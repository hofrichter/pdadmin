<?php
//require_once(__DIR__ . '/../../_testing_/test.php');
define('APP_CHECK', 1);
define('INST_DIR', realpath(__DIR__ . "/../../"));
define('DOMAINS',  sys_get_temp_dir() . '/yep.DOMAINS.test');
$_SESSION['user:id'] = 'unit-test';

require_once(INST_DIR . '/res/backend/lib/LoggerShortcuts.incl.php');
require_once(INST_DIR . '/res/backend/lib/Logger.incl.php');
require_once(INST_DIR . '/config/logging.cfg.php');
Logger::configure($LOGGING);

include_once(INST_DIR . '/res/backend/lib/utilities.incl.php');

require_once(__DIR__ . '/domains.incl.php');

require_once(INST_DIR . '/res/backend/lib/files.incl.php');

unlink(DOMAINS);

function initData($fakeCount) {
    $fa = fopen(DOMAINS, 'a');
    for ($i = 0; $i < $fakeCount; $i++) {
        $fakeUser = uniqid();
        fwrite($fa, sprintf("%s.%s\t%s\n", $fakeUser, 'unittest.tld', 'OK'));
    }
    fclose($fa);
}

function find($subData, $allData) {
    print "=== find =================================\n";
    foreach ($subData as $item1) {
        print "= search for " . $item1['#'] . "\n";
        foreach ($allData as $item2) {
            if ($item1['#'] === $item2['#']) {
                continue 2;
            }
        }
        print "!!! NOT FOUND " . $item1['domain'] . " !!!\n";
        foreach ($allData as $item2) {
            print "!!! checked: " . $item2['#'] . "\n";
        }
        die ("\n\n!!! Test failed !!!\n\n");
    }
    print "===========================================\n";
}

function check($file, array $data, array $fields, $exists = true) {
    print "=== check =================================\n";
    $content = file_get_contents($file);
    for ($i = 0; $i < count($data); $i++) {
        $item = $data[$i];
        print "= check item #$i\n";
        foreach ($fields as $key) {
            print "  - $key " . $item[$key] . " ";
            $pos = strpos($content, $item[$key]);
            if ($pos !== FALSE && $exists) {
                print "was found at $pos.\n";
            } elseif ($pos === FALSE && !$exists) {
                print "was removed successfully.\n";
            } else {
                print "NOT FOUND!!!\n";
                print "Searched for\n" . $item[$key] . "\n\n";
                print "in content\n$content\n\n";
                die ("\n\n!!! Test failed !!!\n\n");
            }
        }
    }
    print "===========================================\n";
}

function call($func, $data) {
    print "=== $func =================================\n";
    //print "====== $func : " . basename(__FILE__) . " @ " . __LINE__ . ":\n";
    foreach ($data as $a) print "= davor:  " . $a['domain'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "=--------\n";
    $result = array();
    if ($func !== 'get') {
        foreach ($data as $item) {
            $func($item);
        }
    }
    $result = get(array());
    print "=--------\n";
    if ($func != 'delete') {
        foreach ($result as $a) {
            print "= danach: " . $a['domain'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
        }
    }
    print "\n";
    return $result;
}

$fields = array('domain', 'state');
////////////////////////////////////////////////////////////////////////////////
// initialize test
$testDomains = array();
for ($i = 0; $i < 20; $i++) {
    $domainId = hash('md5', uniqid());
    $testDomains[] = array(
        'domain' => $domainId,
        'state' => 'OK'
    );
}

initData(15);
$testDomains = call('post', $testDomains);
initData(15);
check(DOMAINS, $testDomains, $fields);


$testDomains = call('put', $testDomains);
check(DOMAINS, $testDomains, $fields);

$loadedTestDomains = call('get', $testDomains);
check(DOMAINS, $loadedTestDomains, $fields);
find($testDomains, $loadedTestDomains);

call('delete', $testDomains);
check(DOMAINS, $testDomains, $fields, false);

print "\n>>> file: " . DOMAINS . "\n";
?>
