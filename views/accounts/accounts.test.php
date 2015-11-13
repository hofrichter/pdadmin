<?php
//require_once(__DIR__ . '/../../_testing_/test.php');
define('APP_CHECK', 1);
define('INST_DIR', realpath(__DIR__ . "/../../"));
define('ACCOUNTS',  sys_get_temp_dir() . '/yep.ACCOUNTS.test');
$_SESSION['user:id'] = 'unit-test';
include_once(INST_DIR . '/res/backend/lib/apache-log4php-2.3.0/Logger.php');
include_once(INST_DIR . '/res/backend/lib/utilities.incl.php');

require_once(__DIR__ . '/accounts.incl.php');

@require_once(INST_DIR . '/res/backend/lib/files.incl.php');

@unlink(ACCOUNTS);

function initData($fakeCount) {
    $fa = fopen(ACCOUNTS, 'a');
    for ($i = 0; $i < $fakeCount; $i++) {
        $fakeUser = uniqid();
        fwrite($fa, sprintf("%s\t%s@%s\t%s\t%s\n", $fakeUser, $i, 'unittest.tld', 'unit-test', date('Y-m-d H:i:s')));
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
        print "!!! NOT FOUND " . $item1['account'] . " !!!\n";
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
    foreach ($data as $a) print "= [$func] davor:  " . $a['account'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "=--------\n";
    $result = array();
    if ($func !== 'get') {
        foreach ($data as $item) {
            try {
                $func($item);
            } catch (Exception $e) {
                die ("! NOT FOUND: " . $item['account'] . " : " . $item['#'] . "!\n");
            }
        }
    }
    $result = get(array());

    print "=--------\n";
    foreach ($result as $a) print "= [$func] danach: " . $a['account'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "\n";
    return $result;
}

$fields = array('account', 'email');
////////////////////////////////////////////////////////////////////////////////
// initialize test
$testAccounts = array();
for ($i = 0; $i < 20; $i++) {
    $accountId = hash('md5', uniqid());
    $userId = 'user-' . $accountId;
    $testAccounts[] = array(
        'account' => $userId,
        'email' => $accountId . '@domain.tld',
        'password' => sprintf('user-%s:{SHA512}NEWPASSWORD%s', $accountId, uniqid())
    );
}

initData(5);
//$testAccounts = doTest('POST', $testAccounts);
$testAccounts = call('post', $testAccounts);
initData(5);
check(ACCOUNTS, $testAccounts, $fields);


//$testAccounts = doTest('PUT', $testAccounts);
$testAccounts = call('put', $testAccounts);
check(ACCOUNTS, $testAccounts, $fields);

$loadedTestAccounts = call('get', $testAccounts);
check(ACCOUNTS, $loadedTestAccounts, $fields);
find($testAccounts, $loadedTestAccounts);

//$testAccounts = doTest('DELETE', $testAccounts);
call('delete', $testAccounts);
check(ACCOUNTS, $testAccounts, $fields, false);

print "\n>>> file: " . ACCOUNTS . "\n";
?>
