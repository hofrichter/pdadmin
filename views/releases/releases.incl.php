<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $isDeployed = file_exists(RELEASE_DIR . '/' . basename(DOMAINS))
               || file_exists(RELEASE_DIR . '/' . basename(ACCOUNTS))
               || file_exists(RELEASE_DIR . '/' . basename(ACCOUNT_ADDRESSES))
               || file_exists(RELEASE_DIR . '/' . basename(ADDRESSES))
               || file_exists(RELEASE_DIR . '/' . basename(PASSWORDS))
               || file_exists(RELEASE_DIR . '/' . basename(TESTS))
               ;
    info(__FILE__, __LINE__, "There are " . ($isDeployed ? '' : 'no ') . "files in the release directory.");
    return $isDeployed;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    debug(__FILE__, __LINE__, 'Deploying files to ' . RELEASE_DIR);
    debug(__FILE__, __LINE__, 'Deploying ' . DOMAINS . ' to ' . RELEASE_DIR . '/' . basename(DOMAINS));
    @copy(DOMAINS,   RELEASE_DIR . '/' . basename(DOMAINS));
    debug(__FILE__, __LINE__,  'Deploying ' .ACCOUNTS . ' to ' . RELEASE_DIR . '/' . basename(ACCOUNTS));
    @copy(ACCOUNTS,  RELEASE_DIR . '/' . basename(ACCOUNTS));
    debug(__FILE__, __LINE__,  'Deploying ' .ACCOUNT_ADDRESSES . ' to ' . RELEASE_DIR . '/' . basename(ACCOUNT_ADDRESSES));
    @copy(ACCOUNT_ADDRESSES,  RELEASE_DIR . '/' . basename(ACCOUNT_ADDRESSES));
    debug(__FILE__, __LINE__,  'Deploying ' .ADDRESSES . ' to ' . RELEASE_DIR . '/' . basename(ADDRESSES));
    @copy(ADDRESSES, RELEASE_DIR . '/' . basename(ADDRESSES));
    debug(__FILE__, __LINE__,  'Deploying ' .PASSWORDS . ' to ' . RELEASE_DIR . '/' . basename(PASSWORDS));
    @copy(PASSWORDS, RELEASE_DIR . '/' . basename(PASSWORDS));
    debug(__FILE__, __LINE__,  'Deploying ' .TESTS . ' to ' . RELEASE_DIR . '/' . basename(TESTS));
    @copy(TESTS, RELEASE_DIR . '/' . basename(TESTS));
    return get($requestData);
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    info(__FILE__, __LINE__, "start undeployment...");
    debug(__FILE__, __LINE__, 'Undeploying files to ' . RELEASE_DIR);
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(DOMAINS));
    @unlink(RELEASE_DIR . '/' . basename(DOMAINS));
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(ACCOUNTS));
    @unlink(RELEASE_DIR . '/' . basename(ACCOUNTS));
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(ACCOUNT_ADDRESSES));
    @unlink(RELEASE_DIR . '/' . basename(ACCOUNT_ADDRESSES));
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(ADDRESSES));
    @unlink(RELEASE_DIR . '/' . basename(ADDRESSES));
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(PASSWORDS));
    @unlink(RELEASE_DIR . '/' . basename(PASSWORDS));
    debug(__FILE__, __LINE__, 'Undeploying ' . RELEASE_DIR . '/' . basename(TESTS));
    @unlink(RELEASE_DIR . '/' . basename(TESTS));
    return get($requestData);
}
?>
