<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

$GLOBALS['ADDRESSES_FILE'] = ADDRESSES;
$GLOBALS['ADDRESSES_SAVE'] = array('emailpattern' => 0, 'account' => 1);
$GLOBALS['ADDRESSES_LOAD'] = array('emailpattern' => 0, 'account' => 1);

/**
 * @see files.incl.php
 * @Override
 */
function __identify($oldData, $newData) {
    return isset($oldData['emailpattern'])
        && isset($newData['emailpattern'])
        && $oldData['emailpattern'] == $newData['emailpattern'];
}

function __splitAccounts($rows) {
    if (@is_array($rows)) {
        $result = array();
        for ($i = 0; $i < count($rows); $i++) {
            foreach (explode(',', $rows[$i]['account']) as $account) {
                if (!isset($result[$rows[$i]['emailpattern']])) {
                    $result[$rows[$i]['emailpattern']] = array(
                        '#' => $rows[$i]['#'],
                        'emailpattern' => $rows[$i]['emailpattern'],
                        'account' => array()
                    );
                }
                $result[$rows[$i]['emailpattern']]['account'][] = $account;
            }
        }
        $rows = array_values($result);
    }
    return $rows;
}

function __joinAccounts($row) {
    if (isset($row['account']) && is_array($row['account'])) {
        $row['account'] = implode(',', $row['account']);
    }
    return $row;
}

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    return __splitAccounts(__load('ADDRESSES'));
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    $row = __joinAccounts($requestData);
    __save('ADDRESSES', $row, $isPost);
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
    __delete('ADDRESSES', $requestData);
    return get($requestData);
}
?>
