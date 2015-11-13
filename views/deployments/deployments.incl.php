<?php if(!defined('APP_CHECK')) { header('HTTP/1.0 400 Bad Request'); exit(1); }

/**
 * Function to handle HTTP-GET-requests.
 * @param Array, $requestData are the requested data
 */
function get(array $requestData) {
    $result = false;
    $tmpResult = trim(@file_get_contents(DEPLOY_NEXT_RUN));
    if (strlen($tmpResult) > 0) {
        // format is: %{CONTDOWN_IN_MIN} %Y-%m-%d %H:%M:%S
        // where %{CONTDOWN_IN_MIN} is the time in [minutes] until the next deployment
        $tmp = explode(' ', $tmpResult);
        if (count($tmp) == 3) {
            $fmt = isset($requestData['fmt']) ? $requestData['fmt'] : 'd.m.Y H:i:s';
            $dat = DateTime::createFromFormat('Y-m-d H:i:s', $tmp[1] . " " . $tmp[2]);
            $result = array('countdown' => $tmp[0]
                           ,'timestamp' => $dat->format($fmt)
                           );
        }
    }
    return $result;
}
?>
