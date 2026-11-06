<?php
// test_encryption.php - Test all cipher implementations
require_once 'vigenere.php';
require_once 'columnar.php';
require_once 'railfence.php';
require_once 'hill.php';

$testText = "HELLO WORLD THIS IS A SECRET MESSAGE";
$key = "SECRETKEY";

echo "<h2>Encryption Test Results</h2>";
echo "<pre>";

echo "Original Text: " . $testText . "\n\n";

echo "=== Vigenere Cipher ===\n";
$enc = encryptVigenere($testText, $key);
$dec = decryptVigenere($enc, $key);
echo "Encrypted: " . $enc . "\n";
echo "Decrypted: " . $dec . "\n\n";

echo "=== Columnar Transposition ===\n";
$enc = encryptColumnar($testText, $key);
$dec = decryptColumnar($enc, $key);
echo "Encrypted: " . $enc . "\n";
echo "Decrypted: " . $dec . "\n\n";

echo "=== Rail Fence Cipher ===\n";
$enc = encryptRailFence($testText, $key);
$dec = decryptRailFence($enc, $key);
echo "Encrypted: " . $enc . "\n";
echo "Decrypted: " . $dec . "\n\n";

echo "=== Hill Cipher ===\n";
$enc = encryptHill($testText, $key);
$dec = decryptHill($enc, $key);
echo "Encrypted: " . $enc . "\n";
echo "Decrypted: " . $dec . "\n\n";

echo "</pre>";
?>