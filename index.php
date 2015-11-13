<?php
////////////////////////////////////////////////////////////////////////////////
@define('APP_CHECK', 1);

// This script implements a limitation of HTTP_GET-requests, as soon as the
// result is an array. Add the request parameter 'limit' request-url. The syntax
// is simple:
// - identified by the length:    http://.../?limit=%start%:%length%
// - identified by the positions: http://.../?limit=%start%-%end%
//     %start% is the start position in the array
//     %length% is the array size of / item count in the the expexted result
//     %end% is the end position 


////////////////////////////////////////////////////////////////////////////////
@ini_set('session.use_cookies', 0);
@ini_set('session.use_only_cookies', 0);
@ini_set('session.use_trans_sid', 1);
@ini_set('session.cache_delimiter', "");
//@ini_set('session.save_path', __DIR__ . "/sessions");
@ini_set('session.save_handler', "files");
@session_name("sid");
if (isset($_REQUEST['sid'])) {
    @session_id($_REQUEST['sid']);
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['sid'])) {
        @session_id($headers['sid']);
    }
} elseif (function_exists('apache_response_headers')) {
    $headers = apache_response_headers();
    if (isset($headers['sid'])) {
        @session_id($headers['sid']);
    }
}
@session_start();

////////////////////////////////////////////////////////////////////////////////
@define('INST_DIR', __DIR__);

$ini = @parse_ini_file(INST_DIR . '/config/pdadmin.cfg');
define('RELEASE_DIR',     isset($ini['RELEASE_DIR'])     ? $ini['RELEASE_DIR']     : realpath(__DIR__ . "/config/release"));
define('BACKUP_DIR',      isset($ini['BACKUP_DIR'])      ? $ini['BACKUP_DIR']      : realpath(__DIR__ . "/config/backup"));
define('WORK_DIR',        isset($ini['WORK_DIR'])        ? $ini['WORK_DIR']        : realpath(__DIR__ . "/config/work"));
define('POSTMAP_BIN',     isset($ini['POSTMAP_BIN'])     ? $ini['POSTMAP_BIN']     : 'postmap');
define('DEPLOY_INTERVAL', isset($ini['DEPLOY_INTERVAL']) ? $ini['DEPLOY_INTERVAL'] : 15);
define('DEPLOY_NEXT_RUN', isset($ini['DEPLOY_NEXT_RUN']) ? $ini['DEPLOY_NEXT_RUN'] : '');

if (!defined('ADMINS'))      { define('ADMINS',       WORK_DIR . "/administrators");  }
if (!defined('ACCOUNTS'))    { define('ACCOUNTS',     WORK_DIR . "/accounts");  }
if (!defined('DOMAINS'))     { define('DOMAINS',      WORK_DIR . "/domains");   }
if (!defined('ADDRESSES'))   { define('ADDRESSES',    WORK_DIR . "/addresses"); }
if (!defined('PASSWORDS'))   { define('PASSWORDS',    WORK_DIR . "/passwords");  }
if (!defined('TESTS'))       { define('TESTS',        WORK_DIR . "/tests");  }



////////////////////////////////////////////////////////////////////////////////

@require_once(INST_DIR . '/res/backend/lib/utilities.incl.php');
@require_once(INST_DIR . '/res/backend/lib/sessionDestroy.incl.php');
@require_once(INST_DIR . '/res/backend/lib/HttpCodes.incl.php');

include_once(INST_DIR . '/res/backend/lib/apache-log4php-2.3.0/Logger.php');
// Passing the configurator as string
LoggerMDC::put('sessionid', session_id());
Logger::configure(array(
    'rootLogger' => array(
        'appenders' => array('default')
    ),
    'appenders' => array(
        'default' => array(
            'class' => 'LoggerAppenderDailyFile',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
                'params' => array(
                    'conversionPattern' => '%date{Y-m-d H:i:s,u} [%server{REMOTE_ADDR}] %p %c %x - %m%n'
                )
            ),
            'params' => array(
                'datePattern' => 'Y-m-d',
                'file' => __DIR__ . '/logs/pdadmin_%s.log',
            ),
        ),
    )
));

$logger = Logger::getLogger(basename(__FILE__));

$params = @explode("/", @substr(@$_SERVER['PATH_INFO'], 1));
$isDefault = false;
if ($params && count($params) > 0 && strlen($params[0]) > 0) {
    $filename = $params[0];
} else {
    $filename = 'index';
    $isDefault = true;
}

$incl = sprintf("%s/res/backend/%s.incl.php", __DIR__, $filename);
$httpMethod = @constant("HTTP_" . @strtoupper(@$_SERVER['REQUEST_METHOD']));

if (!file_exists($incl)) {
    $incl = sprintf("%s/views/%s/%s.incl.php", __DIR__, $filename, $filename);
    if (!file_exists($incl)) {
        header(HTTP_VERSION . ' ' . HTTP_404);
        exit (0);
    }
}
$logger->debug("Loading request-handling module-file '$incl'.");
if (!validateSession()) {
    if ($httpMethod != 'post' && $filename != 'login' && !$isDefault) {
        if (php_sapi_name() != 'cli') {
            $logger->info("Invalid request. User is not logged in or session timed out.");
            header(HTTP_VERSION . ' ' . HTTP_401);
            exit(0);        
        }
    }
}
ob_start();
include_once($incl);
if (!function_exists($httpMethod)) {
    ob_end_clean();
    $logger->info("Unsupported http-operation '$httpMethod' (by module '$filename').");
    header(HTTP_VERSION . ' ' . HTTP_405);
    exit (0);
}
if (!isset($_SESSION['user:isAdmin']) || !$_SESSION['user:isAdmin']) {
    if (!isset($GLOBALS['ADMIN_ROLE_REQUIRED']) || $GLOBALS['ADMIN_ROLE_REQUIRED']) {
        ob_end_clean();
        $logger->info("Unauthorized http-operation '$httpMethod' (by module '$filename'). The user is not an admin!");
        header(HTTP_VERSION . ' ' . HTTP_401);
        exit (0);
    }
}

@require_once(INST_DIR . '/res/backend/lib/files.incl.php');
ob_end_flush();
$data = array();
switch ($httpMethod) {
    case 'get':
        $data = @$_GET;
        break;
    case 'post':
        $data = @$_POST;
        break;
    default:
        $data = @$_REQUEST;
        break;
}
if (!$data || (is_array($data) && count($data) == 0)) {
    $rawData = '';
    $handle = fopen("php://input","r");
    while (!feof($handle)) {
      $rawData .= fread($handle, 8192);
    }
    fclose($handle);
    if (strpos($rawData, '{') === 0 && strrpos($rawData, '}') === strlen($rawData) - 1) {
        $data = @json_decode($rawData, true);
    }
}
$logger->info("Received data for $httpMethod(...) in module '$filename'.");
$result = array();
@session_start();
try {
    $result = $httpMethod($data);
    if (!is_null($result)) {
        if (is_bool($result)) {
            $result = $result ? 'true' : 'false';
        } elseif (is_array($result) && $httpMethod === 'get') {
            if (isset($data['limit'])) {
                $start = FALSE;
                $length = FALSE;
                if (strpos($data['limit'], ':') !== FALSE) {
                    list($start, $length) = explode(':', $data['limit']);
                } elseif (strpos($data['limit'], '-') !== FALSE) {
                    list($start, $end) = explode('-', $data['limit']);
                    $length = $end > $start ? $end - $start : 0;
                }
                if ($start !== FALSE && $length !== FALSE) {
                    $length = is_numeric($length) ? $length : count($result);
                    $start = is_numeric($start) ? $start : 0;
                    $result = array_splice($result, $start, $length);
                }
            }
        }
        if (is_array($result)) {
            $result = json_encode($result, JSON_PRETTY_PRINT);
        }
        print $result;
    }
} catch (Exception $e) {
    @header(HTTP_VERSION . ' ' . HTTP_409);
    print $e->getMessage() . "\n";
}
$logger->info("Response written to brwoser, created by $httpMethod(...) in module '$filename'.");
?>