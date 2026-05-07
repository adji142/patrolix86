<?php

namespace App\Helpers;

/**
 * Decrypts passwords encrypted by CodeIgniter 3's Encryption library (OpenSSL driver).
 *
 * CI3 format: base64( HMAC-SHA512(64 bytes) + IV(16 bytes) + Ciphertext )
 * Cipher    : AES-128-CBC
 * HMAC key  : derived from the base key via HKDF(key, sha512, null, 64, 'sha512')
 */
class CIDecryption
{
    public static function decrypt(string $encrypted, string $key): string|false
    {
        $data = base64_decode($encrypted, true);
        if ($data === false) {
            return false;
        }

        $hmacLen  = 64; // SHA-512 output = 64 bytes
        $ivLen    = 16; // AES-128-CBC IV = 16 bytes

        if (strlen($data) <= $hmacLen + $ivLen) {
            return false;
        }

        $hmacStored    = substr($data, 0, $hmacLen);
        $ivAndCipher   = substr($data, $hmacLen);

        // Derive the HMAC key exactly as CI3 does
        $hmacKey = self::hkdf($key, 'sha512', null, $hmacLen, 'sha512');

        $expectedHmac = hash_hmac('sha512', $ivAndCipher, $hmacKey, true);
        if (!hash_equals($hmacStored, $expectedHmac)) {
            return false;
        }

        $iv         = substr($ivAndCipher, 0, $ivLen);
        $ciphertext = substr($ivAndCipher, $ivLen);

        // PHP's openssl silently uses the first 16 bytes of the key for AES-128-CBC
        return openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    private static function hkdf(string $key, string $digest, ?string $salt, int $length, string $info): string
    {
        $digestLen = strlen(hash($digest, '', true));

        if ($salt === null) {
            $salt = str_repeat("\0", $digestLen);
        }

        $prk    = hash_hmac($digest, $key, $salt, true);
        $output = '';
        $block  = '';

        for ($i = 1; strlen($output) < $length; $i++) {
            $block   = hash_hmac($digest, $block . $info . chr($i), $prk, true);
            $output .= $block;
        }

        return substr($output, 0, $length);
    }
}
