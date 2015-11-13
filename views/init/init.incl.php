<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(0); }

$GLOBALS['ADMIN_ROLE_REQUIRED'] = false;

/**
 * This module is a special one, because it initializes the application by
 * checking the session. It returns http-status 200, if the session exists
 * and contains a valid 'user:id' and is not timedout.
 */
include_once(INST_DIR . '/res/backend/lib/apache-log4php-2.3.0/Logger.php');

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $logger = Logger::getLogger(basename(__FILE__));
    if (validateSession()) {
        $logger->info("Session is valid.");
        header(HTTP_VERSION . ' ' . HTTP_200);
        return;
    } else {
        $headers = getallheaders();
        if (isset($headers['sid'])) {
            session_write_close();
            session_id($headers['sid']);
            session_start();
            if (validateSession()) {
                header(HTTP_VERSION . ' ' . HTTP_200);
                return;
            }
        }
        $logger->info("Session is invalid.");
        header(HTTP_VERSION . ' ' . HTTP_401);
    }
}
?>