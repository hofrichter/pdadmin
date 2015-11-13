<?php
/**
 * This function encrypts the data via RSA. It expects these keys:
 * - the private of the current user
 * - the public key of the chat partner
 * - the public key of the server
 * 
 * @param key is the key to use to encrypt the plain data
 * @param data is the plain data stream to encrypt
 * @return the encrypted data
 */
public static function encrypt ($key, $data) {
    trace(__FILE__, __LINE__, __FUNCTION__ . "(<key>, \$data[len=" . strlen($data) . "])");
    $keyObj = openssl_pkey_get_public($key);
    //$keyObj = @openssl_get_publickey($key);
    $result = "";
    $success = @openssl_public_encrypt($data, $result, $keyObj);
    $errStr = @openssl_error_string();
    if ($errStr) {
        trace(__FILE__, __LINE__, __FUNCTION__ . "(<key>, \$data[len=" . strlen($data) . "]) = " . $errStr);
    }
    return $result;
}

/**
 * This function decrypts the data via RSA. It expects these keys:
 * - the private of the current user
 * - the public key of the chat partner
 * - the public key of the server
 * 
 * @param key is the key to use to decrypt the encrypted data
 * @param data is the encrypted data stream to decrypt
 * @return the decrypted data
 */
public static function decrypt ($key, $data) {
    trace(__FILE__, __LINE__, __FUNCTION__ . "(<key>, \$data[len=" . strlen($data) . "])");
    $keyObj = @openssl_pkey_get_private($key);
    $success = @openssl_private_decrypt($data, $result, $keyObj);
    $errStr = @openssl_error_string();
    if ($errStr) {
        trace(__FILE__, __LINE__, __FUNCTION__ . "(<key>, \$data[len=" . strlen($data) . "]) = " . $errStr);
    }
    return $result;
}

/**
 * This function creates a new RSA-key.
 * 
 * @param keySize is the desired rsa-key-size
 * @return an object like this:
 *         {'public': <public-key>, 'private': <private-key>}
 */
public static function key($keySize) {
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => $keySize,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );
    
    $maxExecTime = defined('max_execution_time_for_init') ? max_execution_time_for_init : 60;
    $oldValue = ini_get('max_execution_time');
    if ($oldValue < $maxExecTime) {
        ini_set('max_execution_time', $maxExecTime);
    }
    // create a new keypair
    $opensslRes = openssl_pkey_new($config);
    // get the private key:
    openssl_pkey_export($opensslRes, $privateKey);
    // get the public key
    $publicKey = openssl_pkey_get_details($opensslRes);
    
    ini_set('max_execution_time', $oldValue);
    
    return array(
        'public'  => trim($publicKey["key"]),
        'private' => $privateKey
    );
}
?>