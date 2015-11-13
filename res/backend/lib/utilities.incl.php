<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(0); }

function isEmpty($obj, $key = NULL) {
    if (is_array($obj)) {
        return is_null($key) || !isset($obj[$key]) || strlen($obj[$key]) == 0;
    } else {
        return is_null($obj) || strlen($obj) == 0;
    }
}

function validateSession() {
    //$result = isset($_SESSION['user:id']) && strlen($_SESSION['user:id']) > 0
    //       && isset($_SESSION['user:loggedin']) && $_SESSION['user:loggedin'] > (date('YmdHi') - 30);
    $result = !isEmpty($_SESSION, 'user:id') && !isEmpty($_SESSION, 'user:loggedin') && $_SESSION['user:loggedin'] > (date('YmdHi') - 30);
    return $result;
}
?>