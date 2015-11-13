<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }


$GLOBALS['TESTS_FILE'] = TESTS;
$GLOBALS['TESTS_SAVE'] = array('email' => 0, 'account' => 1);
$GLOBALS['TESTS_LOAD'] = array('email' => 0, 'account' => 1);

/**
 * @see files.incl.php
 * @Override
 */
function __identify($oldData, $newData) {
    return isset($oldData['email'])
        && isset($newData['email'])
        && $oldData['email'] == $newData['email'];
}

function __splitAccounts($rows) {
    if (@is_array($rows)) {
        $result = array();
        for ($i = 0; $i < count($rows); $i++) {
            foreach (explode(',', $rows[$i]['account']) as $account) {
                if (!isset($result[$rows[$i]['email']])) {
                    $result[$rows[$i]['email']] = array(
                        '#' => $rows[$i]['#'],
                        'email' => $rows[$i]['email'],
                        'account' => array()
                    );
                }
                $result[$rows[$i]['email']]['account'][] = $account;
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
function __test($result) {
    $logger = Logger::getLogger(basename(__FILE__));
    $logger->info("start tests...");
    
    if (!file_exists(ADDRESSES)) {
        file_put_contents(ADDRESSES, "");
    }
    if (!file_exists(ACCOUNTS)) {
        file_put_contents(ACCOUNTS, "");
    }

    $cmd = sprintf("%s %s %s", POSTMAP_BIN, ADDRESSES, ACCOUNTS);
    exec($cmd, $postmapResult);
    $logger->info('compiling ' . $cmd . ': ' .count($postmapResult));
    
    foreach ($result as $id => $test) {
        $cmd = sprintf("%s -q %s hash:%s hash:%s", POSTMAP_BIN, $test['email'], ADDRESSES, ACCOUNTS);
        $postmapResult = array();
        @exec($cmd, $postmapResult);
        $logger->info('checking ' . $cmd . ': ' .count($postmapResult));

        if (count($postmapResult) >= 1) {
            $tmpResult = array();
            foreach ($postmapResult as $tmpResultItem) {
                foreach (preg_split('/\s*,\s*/', $tmpResultItem) as $item) {
                    $tmpResult[] = preg_replace('/\/$/', '', $item);
                }
            }
            $postmapResult = array_unique($tmpResult);
        }
        $result[$id]['testresult'] = true;

        if (count($test['account']) > 0 && count($postmapResult) > 0) {
            $result[$id]['testresult'] = count(array_diff($test['account'], $postmapResult)) == 0;
            if (!$result[$id]['testresult']) {
                $result[$id]['testresult'] = array('expected' => implode(',', $test['account']), 'actual' => implode(',', $postmapResult));
            }
            $logger->debug('testing ' . implode(',', $test['account']) . ': ' . ($result[$id]['testresult'] ? 'correct' : 'failed'));
        } elseif (count($test['account']) > 0) {
            $result[$id]['testresult'] = array('expected' => implode(',', $test['account']), 'actual' => '');
        } else {
            $result[$id]['testresult'] = true;
        }
        $logger->debug("Testresult: ". json_encode($result[$id]['testresult']));
    }
    return $result;
}


/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $result = __splitAccounts(__load('TESTS'));
    return __test($result);
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    $row = __joinAccounts($requestData);
    __save('TESTS', $row, $isPost);
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
    __delete('TESTS', $requestData);
    return get($requestData);
}
?>
