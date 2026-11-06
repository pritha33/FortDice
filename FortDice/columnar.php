<?php
function encryptColumnar($text, $key) {
    $key = preg_replace('/[^A-Za-z]/', '', $key);
    $cols = strlen($key);
    $rows = ceil(strlen($text) / $cols);
    $grid = array_fill(0, $cols, '');
    
    for ($i = 0; $i < strlen($text); $i++) {
        $grid[$i % $cols] .= $text[$i];
    }
    
    $result = '';
    for ($c = 0; $c < $cols; $c++) {
        $result .= $grid[$c];
    }
    
    return $result;
}

function decryptColumnar($text, $key) {
    $key = preg_replace('/[^A-Za-z]/', '', $key);
    $cols = strlen($key);
    $rows = ceil(strlen($text) / $cols);
    $grid = array_fill(0, $cols, '');
    
    $index = 0;
    for ($c = 0; $c < $cols; $c++) {
        for ($r = 0; $r < $rows && $index < strlen($text); $r++) {
            $grid[$c] .= $text[$index++];
        }
    }
    
    $result = '';
    for ($r = 0; $r < $rows; $r++) {
        for ($c = 0; $c < $cols; $c++) {
            if (isset($grid[$c][$r])) {
                $result .= $grid[$c][$r];
            }
        }
    }
    
    return $result;
}
?>