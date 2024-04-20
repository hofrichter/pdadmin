<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {

    sessionDestroy();
    unset($_SESSION['user:id']);
    unset($_SESSION['user:loggedin']);
    session_write_close();

    header(HTTP_VERSION . ' ' . HTTP_200);
}

?>