<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$GLOBALS['ADDRESSES_FILE'] = ADDRESSES;
$GLOBALS['ADDRESSES_SAVE'] = array('emailpattern' => 0, 'account' => 1);
$GLOBALS['ADDRESSES_LOAD'] = array('emailpattern' => 0, 'account' => 1);

$GLOBALS['ACCOUNTS_FILE'] = ACCOUNTS;
$GLOBALS['ACCOUNTS_LOAD'] = array('email' => 0, 'account' => 1);

/**
 * @see files.incl.php
 * @Override
 */
function __identify($oldData, $newData) {
    return isset($oldData['emailpattern'])
        && isset($newData['emailpattern'])
        && $oldData['emailpattern'] == $newData['emailpattern'];
}

function prepareSave(array $requestData) {
    $result = array();
    if (isset($requestData['account'])) {
        $requestData['account'] = implode(',', $requestData['account']);
        $result = $requestData;
    } elseif (is_array($requestData)) {
        foreach ($requestData as $address) {
            if (isset($address['account'])) {
                $address['account'] = implode(',', $address['account']);
                $result[]= $address;
            }
        }        
    }
    return $result;
}

function prepareLoad(array $requestData) {
    $accounts = __load('ACCOUNTS');
    $result = array();
    if (is_array($requestData)) {
        foreach ($requestData as $address) {
            if (isset($address['account'])) {
                $found = false;
                foreach ($accounts as $account) {
                    // ignore mails, of the accounts itselfs
                    if ($account['email'] == $address['emailpattern']) {
                        continue;
                    }
                    $address['account'] = explode(',', $address['account']);
                    $result[]= $address;
                    break;
                }
            }
        }        
    }
    return $result;
}

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    return prepareLoad(__load('ADDRESSES'));
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    __save('ADDRESSES', prepareSave($requestData), $isPost);
    return get($requestData);
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    return put($requestData, true);
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    __delete('ADDRESSES', prepareSave($requestData));
    return get($requestData);
}
?>
