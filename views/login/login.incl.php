<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

// special case: an e-mail-account-user can change its own password, so login:
$GLOBALS['ADMIN_ROLE_REQUIRED'] = false;

$GLOBALS['ADMINS_FILE'] = ADMINS;
$GLOBALS['ADMINS_LOAD'] = array('password' => 0);

$GLOBALS['PASSWORDS_FILE'] = PASSWORDS;
$GLOBALS['PASSWORDS_LOAD'] = array('password' => 0);

function __checkPassword($fileId, array $requestData) {
    $passwords = __load($fileId);
    foreach($passwords as $pw) {
        if (isset($pw['password']) && $pw['password'] === $requestData['password']) {
            return true;
        }
    }
    return false;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    $logger = Logger::getLogger(basename(__FILE__));
    if (!isEmpty($requestData, 'username') && !isEmpty($requestData, 'password')) {
        sessionDestroy();
        if (__checkPassword('ADMINS', $requestData)) {
            $_SESSION['user:isAdmin'] = true;
        } elseif (__checkPassword('PASSWORDS', $requestData)) {
            $_SESSION['user:isAdmin'] = false;
        } else {
            header(HTTP_VERSION . ' ' . HTTP_401);
            return;
        }
        $_SESSION['user:id'] = $requestData['username'];
        $_SESSION['user:loggedin'] = date('YmdHi');
        session_write_close();

        $responseData = array(
            'session_name' => session_name(),
            'session_id' => session_id(),
            'username' => $requestData['username']
        );
        if ($_SESSION['user:isAdmin']) {
            $responseData['isAdmin'] = true;
        }
        $logger->info("Login was successful for  " . $_SESSION['user:id'] . ". Current session: " . session_id());
        header(HTTP_VERSION . ' ' . HTTP_200);
        return $responseData;
    }
    header(HTTP_VERSION . ' ' . HTTP_401);
}

?>