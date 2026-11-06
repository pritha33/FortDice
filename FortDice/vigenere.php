<?php
function encryptVigenere($text, $key) {
    $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
    $result = '';
    $j = 0;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        if (!ctype_alpha($char)) {
            $result .= $char;
            continue;
        }
        
        $base = ctype_upper($char) ? 65 : 97;
        $p = ord($char) - $base;
        $k = ord($key[$j % strlen($key)]) - 65;
        $result .= chr((($p + $k) % 26) + $base);
        $j++;
    }
    
    return $result;
}

function decryptVigenere($text, $key) {
    $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
    $result = '';
    $j = 0;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        if (!ctype_alpha($char)) {
            $result .= $char;
            continue;
        }
        
        $base = ctype_upper($char) ? 65 : 97;
        $p = ord($char) - $base;
        $k = ord($key[$j % strlen($key)]) - 65;
        $result .= chr((($p - $k + 26) % 26) + $base);
        $j++;
    }
    
    return $result;
}
?>