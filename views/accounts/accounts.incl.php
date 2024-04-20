<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$GLOBALS['ACCOUNTS_FILE'] = ACCOUNTS;
$GLOBALS['ACCOUNTS_SAVE'] = array('email' => 0, 'account' => 1);
$GLOBALS['ACCOUNTS_LOAD'] = array('email' => 0, 'account' => 1);

//$GLOBALS['ACCOUNT_ADDRESSES_FILE'] = ACCOUNT_ADDRESSES;

$GLOBALS['PASSWORDS_FILE'] = PASSWORDS;
$GLOBALS['PASSWORDS_LOAD'] = array('password' => 0);

function prepareSave(array $requestData) {
    if (isset($requestData['account'])) {
        $requestData['account'] .= '/';
    }
    return $requestData;
}

function savePseudoMapping ($accounts) {
    $lines = '';
    foreach ($accounts as $account) {
        $lines .= $account['email'] . "  " . $account['email'] . "\n";
    }
    file_put_contents(ACCOUNT_ADDRESSES, $lines);
}

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $accounts  = __load('ACCOUNTS');
    $passwords = __load('PASSWORDS');
    foreach ($accounts as $i => $account) {
        foreach ($passwords as $password) {
            $account['account'] = preg_replace('/\/$/', '', $account['account']);
            $accounts[$i]['account'] = $account['account'];
            if (isset($account['account'])
            && isset($password['password'])
            && strpos($password['password'], $account['account']) === 0) {
                $accounts[$i]['hasPassword'] = true;
            }
        }
    }
    return $accounts;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    __save('ACCOUNTS', prepareSave($requestData), false);
    $result = get($requestData);
    savePseudoMapping($result);
    return $result;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    __save('ACCOUNTS', prepareSave($requestData), true);
    $result = get($requestData);
    savePseudoMapping($result);
    return $result;
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    __delete('ACCOUNTS', prepareSave($requestData));
    $result = get($requestData);
    savePseudoMapping($result);
    return $result;
}
?>
