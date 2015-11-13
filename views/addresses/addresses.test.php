<?php
//require_once(__DIR__ . '/../../_testing_/test.php');
define('APP_CHECK', 1);
define('INST_DIR', realpath(__DIR__ . "/../../"));
define('ADDRESSES',  sys_get_temp_dir() . '/yep.ADDRESSES.test');
$_SESSION['user:id'] = 'unit-test';
include_once(INST_DIR . '/res/backend/lib/apache-log4php-2.3.0/Logger.php');
include_once(INST_DIR . '/res/backend/lib/utilities.incl.php');

require_once(__DIR__ . '/addresses.incl.php');

@require_once(INST_DIR . '/res/backend/lib/files.incl.php');

@unlink(ADDRESSES);

function initData($fakeCount, $start) {
    $fa = fopen(ADDRESSES, 'a');
    for ($i = $start; $i < $fakeCount + $start; $i++) {
        $fakeUser = uniqid();
        fwrite($fa, sprintf("%s@%s\t%s\n", $i, 'unittest.tld', $fakeUser));
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
        print "!!! NOT FOUND " . $item1['address'] . " !!!\n";
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
            $str = is_array($item[$key]) ? implode(',', $item[$key]) : $item[$key];
            print "  - $key " . $str . " ";
            $pos = strpos($content, $str);
            if ($pos !== FALSE && $exists) {
                print "was found at $pos.\n";
            } elseif ($pos === FALSE && !$exists) {
                print "was removed successfully.\n";
            } else {
                print "NOT FOUND!!!\n";
                print "Searched for\n" . $str . "\n\n";
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
    foreach ($data as $a) print "= davor:  " . $a['emailpattern'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "=--------\n";
    $result = array();
    if ($func !== 'get') {
        foreach ($data as $item) {
            $func($item);
        }
    }
    $result = get(array());

    print "=--------\n";
    foreach ($result as $a) print "= danach: " . $a['emailpattern'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "\n";
    return $result;
}

$fields = array('emailpattern', 'account');
////////////////////////////////////////////////////////////////////////////////
// initialize test
$testAddresss = array();
for ($i = 0; $i < 20; $i++) {
    $addressId = hash('md5', uniqid());
    $userId = 'user-' . $addressId;
    $testAddresss[] = array(
        'account' => $userId,
        'emailpattern' => $addressId . '@domain.tld'
    );
}

initData(15, 100);
//$testAddresss = doTest('POST', $testAddresss);
$testAddresss = call('post', $testAddresss);
initData(15, 500);
check(ADDRESSES, $testAddresss, $fields);


//$testAddresss = doTest('PUT', $testAddresss);
$testAddresss = call('put', $testAddresss);
check(ADDRESSES, $testAddresss, $fields);

$loadedTestAddresss = call('get', $testAddresss);
check(ADDRESSES, $loadedTestAddresss, $fields);
find($testAddresss, $loadedTestAddresss);

//$testAddresss = doTest('DELETE', $testAddresss);
call('delete', $testAddresss);
check(ADDRESSES, $testAddresss, $fields, false);

print "\n>>> file: " . ADDRESSES . "\n";
?>
