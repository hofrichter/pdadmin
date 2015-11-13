<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(0); }

if (!function_exists('__identify')) {
    function __identify($oldData, $newData) {
        return $oldData['#'] == $newData['#'];
    }
}

function __returnFilter($dataType, array $result) {
    $fields = $GLOBALS[$dataType . '_LOAD'];
    $fields['#'] = 'the-hash-code';
    $filtered = array();
    foreach ($result as $key => $value) {
        if (is_numeric($key) && is_array($value)) {
            $filtered[] = __returnFilter($dataType, $value);
        } elseif (isset($fields[$key])) {
            $filtered[$key] = $value;
        }
    }
    return $filtered;
}

function __splitRow(array $fields, $line) {
    $splitted = preg_split('/\s+/', $line);
    if (count($splitted) < count($fields)) {
        return NULL;
    }
    $item = array();
    foreach ($fields as $key => $column) {
        if (count($splitted) <= $column) {
            throw new Exception("entity.invalid.datastructure");
        }
        $item[$key] = $splitted[$column];
    }
    $item['#'] = md5($line);
    return $item;
}

/**
 * private function.
 */
if (!function_exists('__load')) {
    function __load($dataType) {
        $file = $GLOBALS[$dataType . '_FILE'];
        $fields = $GLOBALS[$dataType . '_LOAD'];
        $result = array();
        $lines = @file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], "#") === 0) {
                    continue;
                }
                $item = __splitRow($fields, $lines[$i]);
                if (is_array($item)) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }
}

/**
 * private function.
 */
if (!function_exists('__save')) {
    function __save($dataType, array $item, $insert = false) {
        $file = $GLOBALS[$dataType . '_FILE'];
        $fields = $GLOBALS[$dataType . '_SAVE'];
        $logger = Logger::getLogger(basename(__FILE__));

        if (!is_array($item) || count($item) == 0) {
            return __load($dataType);
        }
        $itemRow = array();
        foreach ($fields as $key => $column) {
            if (!isset($item[$key])) {
                $logger->error("entity.incomplete.datastructure: missing '" . $key . "' in " . var_export($item, true));
                throw new Exception("entity.incomplete.datastructure");
            }
            $itemRow[$column] = $item[$key];
        }
        $itemStr = implode("    ", $itemRow);

        $lines = @file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        $lines = @is_array($lines) ? array_unique($lines) : [];

        if (isset($item['#']) && $lines) {
            for ($i = 0; $i < count($lines); $i++) {
                $splitted = __splitRow($fields, $lines[$i]);
                if (__identify($splitted, $item)) {
                    $item['#'] = md5($itemStr);
                    $lines[$i] = $itemStr;
                    file_put_contents($file, implode("\n", $lines) . "\n");
                    return __load($dataType);
                }
            }
        }
        if ($insert) {
            $item['#'] = md5($itemStr);
            $lines[] = $itemStr;
            $lines = array_unique($lines);
            file_put_contents($file, implode("\n", $lines) . "\n");
            return __load($dataType);
        } else {
            throw new Exception("entity.not.found");
        }
    }
}

/**
 * private function.
 */
if (!function_exists('__delete')) {
    function __delete($dataType, array $item) {
        if (!isset($item['#'])) {
            throw new Exception("entity.incomplete.request");
        }
        $file = $GLOBALS[$dataType . '_FILE'];
        $fields = $GLOBALS[$dataType . '_LOAD'];
        
        $logger = Logger::getLogger(basename(__FILE__));
        if (!is_array($item) || count($item) == 0) {
            return __load($dataType);
        }
        $hashCode = $item['#']; 
        $lines = @file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            for ($i = 0; $i < count($lines); $i++) {
                if (strpos($lines[$i], "#") === 0) {
                    continue;
                }
                if (md5($lines[$i]) == $hashCode) {
                    unset($lines[$i]);
                    file_put_contents($file, implode("\n", $lines) . "\n");
                    return __load($dataType);
                }
            }
        }
    }
}

if (!function_exists('__find')) {
    function __find($item) {
        $loadedItems = __load();
        foreach ($loadedItems as $loadedItem) {
            if (__identify($lines[$i], $item)) {
                return $item;
            }
        }
        return NULL;
    }
}
?>