<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

// special case: an e-mail-account-user can change its own password:
$GLOBALS['ADMIN_ROLE_REQUIRED'] = false;

$GLOBALS['PASSWORDS_FILE'] = PASSWORDS;
$GLOBALS['PASSWORDS_SAVE'] = array('password' => 0);
// sepcial case: loading the old data to find the correct account-entry:
// do not change this:
$GLOBALS['PASSWORDS_LOAD'] = array('password' => 0);

// We don't support get', because auf security aspects!
//function get(array $requestData) { return []; }


/**
 * Function to resolve the status.
 */
function __saved($account, $saved) {
    $result = array_filter($saved, function($ar) use (&$account) {
        return $ar['password'] == $account['password'];
    });
    return count($result);
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData) {
    $insert = false;
    $requestData['#'] = __buildHashCode($requestData);
    if (isset($requestData['oldaccount']) && strlen($requestData['oldaccount']) > 0) {
        delete($requestData);
        unset($requestData['oldaccount']);
        $insert = true;
    } else {
        return false;
    }
    if ($requestData['#']) {
        $result = __save('PASSWORDS', $requestData, $insert);
        $result = (1 == __saved($requestData, $result));
        @copy(PASSWORDS, RELEASE_DIR . '/' . basename(PASSWORDS));
        return $result && @file_exists(RELEASE_DIR . '/' . basename(PASSWORDS));
    }
    return false;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function post(array $requestData) {
    $requestData['#'] = __buildHashCode($requestData);
    if ($requestData['#']) {
        __delete('PASSWORDS', $requestData);
    }
    $result = __save('PASSWORDS', $requestData, true);
    return (1 == __saved($requestData, $result));
}

/**
 * Function to handle HTTP-DELETE-requests.
 * @param Array, $requestData are the requested data
 */
function delete(array $requestData) {
    $requestData['#'] = __buildHashCode($requestData);
    if ($requestData['#']) {
        $result = __delete('PASSWORDS', $requestData);
        return (1 == __saved($requestData, $result));
    }
    return true;
}

function __buildHashCode($requestData) {
    $account = isset($requestData['oldaccount']) && strlen($requestData['oldaccount']) > 0
             ? $requestData['oldaccount']
             : $requestData['account'];
    $items = __load('PASSWORDS');
    foreach ($items as $item) {
        if (isset($item['password']) && strpos($item['password'], $account) === 0) {
            return $item['#'];
        }
    }

    return NULL;
}

?>
