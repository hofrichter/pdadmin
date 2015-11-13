<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$GLOBALS['DOMAINS_FILE'] = DOMAINS;
$GLOBALS['DOMAINS_SAVE'] = array('domain' => 0, 'state' => 1);
$GLOBALS['DOMAINS_LOAD'] = array('domain' => 0, 'state' => 1);

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    return __load('DOMAINS');
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    return __save('DOMAINS', $requestData, false);
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    return __save('DOMAINS', $requestData, true);
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    return __delete('DOMAINS', $requestData);
}
?>
