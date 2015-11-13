<?php
//require_once(__DIR__ . '/../../_testing_/test.php');
define('APP_CHECK', 1);
define('INST_DIR', realpath(__DIR__ . "/../../"));
define('PASSWORDS', sys_get_temp_dir() . '/yep.PASSWORDS.test');
$_SESSION['user:id'] = 'unit-test';
include_once(INST_DIR . '/res/backend/lib/apache-log4php-2.3.0/Logger.php');
include_once(INST_DIR . '/res/backend/lib/utilities.incl.php');

require_once(__DIR__ . '/passwords.incl.php');

@require_once(INST_DIR . '/res/backend/lib/files.incl.php');

@unlink(PASSWORDS);

function initData($fakeCount) {
    $fp = fopen(PASSWORDS, 'a');
    for ($i = 0; $i < $fakeCount; $i++) {
        $fakeUser = uniqid();
        fwrite($fp, sprintf("%s:{SHA512}%s\n", $fakeUser, uniqid()));
    }
    fclose($fp);
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
                print "was removed successfuly.\n";
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
    if (!function_exists($func)) {
        print "= $func is not supported by this module\n";
        print "=--------\n";
        return $data;
    }


    //print "====== $func : " . basename(__FILE__) . " @ " . __LINE__ . ":\n";
    foreach ($data as $a) print "= davor:  " . $a['password'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "=--------\n";
    $result = array();
    if ($func === 'get') {
        $result = get(array());
    } else {
        foreach ($data as $item) {
            $func($item);
            $result[] = $item;
        }
    }
    //print "=--------\n";
    //foreach ($result as $a) print "= danach: " . $a['password'] . " : " . (isset($a['#']) ? $a['#'] : '####') . "\n";
    print "\n";
    return $result;
}

////////////////////////////////////////////////////////////////////////////////
// initialize test
$testPasswords = array();
for ($i = 0; $i < 20; $i++) {
    $accountId = hash('md5', uniqid());
    $userId = 'user-' . $accountId;
    $testPasswords[] = array(
        'account' => $userId,
        'email' => $accountId . '@domain.tld',
        'password' => sprintf('%s:{SHA512}NEWPASSWORD%s', $userId, uniqid())
    );
}

$fields = array('password');

initData(15);
$testPasswords = call('post', $testPasswords);
initData(15);
check(PASSWORDS, $testPasswords, $fields);


$testPasswords = call('put', $testPasswords);
check(PASSWORDS, $testPasswords, $fields);


$newTestPasswords = array();
foreach ($testPasswords as $key => $value) {
    $userId = 'renamed-user-' . $accountId;
    $newTestPasswords[] = array(
        'oldaccount' => $value['account'],
        'email' => $value['email'],
        'account' => $userId,
        'password' => sprintf('%s:{SHA512}NEWPASSWORD%s', $userId, uniqid())
    );
}

$newTestPasswords = call('put', $newTestPasswords);
check(PASSWORDS, $newTestPasswords, $fields);
check(PASSWORDS, $testPasswords, $fields, false);

call('delete', $testPasswords);
check(PASSWORDS, $testPasswords, $fields, false);

print "\n>>> file: " . PASSWORDS . "\n";
?>
