<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(0); }

/**
 * Function to handle HTTP-GET-requests.
 * @param String, $sessionId is the id of the session to destroy
 */
function sessionDestroy($sessionId = false) {
    if (!$sessionId) {
        $sessionId = session_id();
    }
    if (session_id()) {
        session_write_close();
    }
    session_start();
    $currSessionId = session_id();
    session_write_close();
    // do the destroy-thing
    session_id($sessionId);
    session_start();
    session_destroy();
    session_write_close();

    if ($currSessionId != $sessionId) {
        session_id($currSessionId);
    } else {
        session_regenerate_id(true);
    }
    session_start();
    session_write_close();
    session_start();
}
?>