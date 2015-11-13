<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    if (isset($requestData['timestamp']) && is_dir(BACKUP_DIR . '/' . $requestData['timestamp'])) {
        $files = glob(BACKUP_DIR . '/' . $requestData['timestamp'] . '/*.log');
        if (count($files) == 1) {
            return file_get_contents($files[0]);
        } else {
            return '';
        }
    }
    $result = preg_grep('/^\d+_\d+(_ok|_pw_only)*$/', scandir(BACKUP_DIR, SCANDIR_SORT_DESCENDING));
    return $result;
}

/**
 * Function to handle HTTP-POST-requests.
 * @param Array, $requestData are the requested data
 */
function put(array $requestData, $isPost = false) {
    if (isset($requestData['timestamp']) && is_dir(BACKUP_DIR . '/' . $requestData['timestamp'])) {
        @copy(BACKUP_DIR . '/' . $requestData['timestamp'] . '/' . basename(DOMAINS), DOMAINS);
        @copy(BACKUP_DIR . '/' . $requestData['timestamp'] . '/' . basename(ACCOUNTS), ACCOUNTS);
        @copy(BACKUP_DIR . '/' . $requestData['timestamp'] . '/' . basename(ADDRESSES), ADDRESSES);
        @copy(BACKUP_DIR . '/' . $requestData['timestamp'] . '/' . basename(PASSWORDS), PASSWORDS);
    }
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
    if (isset($requestData['dir'])) {
        $dir = BACKUP_DIR . "/" . $requestData['dir'];
        foreach (glob($dir) as $file) {
            unlink($file);
        }
        rmdir($dir);
    }
    return get($requestData);
}
?>
