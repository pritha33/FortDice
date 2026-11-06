<?php
function encryptRailFence($text, $key) {
    $rails = max(2, intval($key));
    $fence = array_fill(0, $rails, '');
    $rail = 0;
    $dir = 1;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $fence[$rail] .= $text[$i];
        $rail += $dir;
        
        if ($rail == 0 || $rail == $rails - 1) {
            $dir *= -1;
        }
    }
    
    return implode('', $fence);
}

function decryptRailFence($text, $key) {
    $rails = max(2, intval($key));
    $pattern = array();
    $rail = 0;
    $dir = 1;
    
    for ($i = 0; $i < strlen($text); $i++) {
        $pattern[] = $rail;
        $rail += $dir;
        
        if ($rail == 0 || $rail == $rails - 1) {
            $dir *= -1;
        }
    }
    
    $counts = array_fill(0, $rails, 0);
    foreach ($pattern as $r) {
        $counts[$r]++;
    }
    
    $grid = array_fill(0, $rails, '');
    $index = 0;
    for ($r = 0; $r < $rails; $r++) {
        $grid[$r] = substr($text, $index, $counts[$r]);
        $index += $counts[$r];
    }
    
    $pos = array_fill(0, $rails, 0);
    $result = '';
    foreach ($pattern as $r) {
        $result .= $grid[$r][$pos[$r]];
        $pos[$r]++;
    }
    
    return $result;
}
?>