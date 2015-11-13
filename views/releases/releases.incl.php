<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $logger = Logger::getLogger(basename(__FILE__));
    $isDeployed = file_exists(RELEASE_DIR . '/' . basename(DOMAINS))
               || file_exists(RELEASE_DIR . '/' . basename(ACCOUNTS))
               || file_exists(RELEASE_DIR . '/' . basename(ADDRESSES))
               || file_exists(RELEASE_DIR . '/' . basename(PASSWORDS))
               || file_exists(RELEASE_DIR . '/' . basename(TESTS))
               ;
    $logger->info("There are " . ($isDeployed ? '' : 'no ') . "files in the release directory.");
    return $isDeployed;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    $logger = Logger::getLogger(basename(__FILE__));
    $logger->debug('Deploying files to ' . RELEASE_DIR);
    $logger->debug('Deploying ' . DOMAINS . ' to ' . RELEASE_DIR . '/' . basename(DOMAINS));
    @copy(DOMAINS,   RELEASE_DIR . '/' . basename(DOMAINS));
    $logger->debug( 'Deploying ' .ACCOUNTS . ' to ' . RELEASE_DIR . '/' . basename(ACCOUNTS));
    @copy(ACCOUNTS,  RELEASE_DIR . '/' . basename(ACCOUNTS));
    $logger->debug( 'Deploying ' .ADDRESSES . ' to ' . RELEASE_DIR . '/' . basename(ADDRESSES));
    @copy(ADDRESSES, RELEASE_DIR . '/' . basename(ADDRESSES));
    $logger->debug( 'Deploying ' .PASSWORDS . ' to ' . RELEASE_DIR . '/' . basename(PASSWORDS));
    @copy(PASSWORDS, RELEASE_DIR . '/' . basename(PASSWORDS));
    $logger->debug( 'Deploying ' .TESTS . ' to ' . RELEASE_DIR . '/' . basename(TESTS));
    @copy(TESTS, RELEASE_DIR . '/' . basename(TESTS));
    return get($requestData);
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    $logger = Logger::getLogger(basename(__FILE__));
    $logger->info("start undeployment...");
    $logger->debug('Undeploying files to ' . RELEASE_DIR);
    $logger->debug('Undeploying ' . RELEASE_DIR . '/' . basename(DOMAINS));
    @unlink(RELEASE_DIR . '/' . basename(DOMAINS));
    $logger->debug('Undeploying ' . RELEASE_DIR . '/' . basename(ACCOUNTS));
    @unlink(RELEASE_DIR . '/' . basename(ACCOUNTS));
    $logger->debug('Undeploying ' . RELEASE_DIR . '/' . basename(ADDRESSES));
    @unlink(RELEASE_DIR . '/' . basename(ADDRESSES));
    $logger->debug('Undeploying ' . RELEASE_DIR . '/' . basename(PASSWORDS));
    @unlink(RELEASE_DIR . '/' . basename(PASSWORDS));
    $logger->debug('Undeploying ' . RELEASE_DIR . '/' . basename(TESTS));
    @unlink(RELEASE_DIR . '/' . basename(TESTS));
    return get($requestData);
}
?>
