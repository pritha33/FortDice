<?php
/**
 * Hill Cipher Implementation
 * Uses 2x2 matrix encryption
 */

class HillCipher {
    private $keyMatrix;
    private $keySize = 2;
    
    public function __construct($key) {
        $this->keyMatrix = $this->generateKeyMatrix($key);
    }
    
    /**
     * Generate 2x2 key matrix from key string
     */
    private function generateKeyMatrix($key) {
        // Clean key - take first 4 letters and convert to numbers
        $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
        $key = str_pad($key, 4, 'A', STR_PAD_RIGHT);
        $key = substr($key, 0, 4);
        
        $matrix = [];
        $index = 0;
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 2; $j++) {
                $matrix[$i][$j] = ord($key[$index++]) - 65;
            }
        }
        
        // Check if matrix is invertible
        $det = ($matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0]) % 26;
        if ($det % 2 == 0 || $det % 13 == 0) {
            // Not invertible, use default matrix
            $matrix = [
                [3, 10],
                [20, 11]
            ];
        }
        
        return $matrix;
    }
    
    /**
     * Get modular inverse of a number
     */
    private function modInverse($a, $m = 26) {
        $a = $a % $m;
        for ($x = 1; $x < $m; $x++) {
            if (($a * $x) % $m == 1) {
                return $x;
            }
        }
        return 1;
    }
    
    /**
     * Get inverse key matrix for decryption
     */
    private function getInverseMatrix() {
        $matrix = $this->keyMatrix;
        $det = ($matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0]) % 26;
        if ($det < 0) $det += 26;
        
        $invDet = $this->modInverse($det);
        
        $invMatrix = [
            [($matrix[1][1] * $invDet) % 26, (-$matrix[0][1] * $invDet) % 26],
            [(-$matrix[1][0] * $invDet) % 26, ($matrix[0][0] * $invDet) % 26]
        ];
        
        // Fix negative values
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 2; $j++) {
                if ($invMatrix[$i][$j] < 0) {
                    $invMatrix[$i][$j] += 26;
                }
            }
        }
        
        return $invMatrix;
    }
    
    /**
     * Encrypt text using Hill Cipher
     */
    public function encrypt($text) {
        $text = strtoupper(preg_replace('/[^A-Z]/', '', $text));
        if (strlen($text) % 2 != 0) {
            $text .= 'X'; // Padding
        }
        
        $result = '';
        $matrix = $this->keyMatrix;
        
        for ($i = 0; $i < strlen($text); $i += 2) {
            $p1 = ord($text[$i]) - 65;
            $p2 = ord($text[$i + 1]) - 65;
            
            $c1 = ($matrix[0][0] * $p1 + $matrix[0][1] * $p2) % 26;
            $c2 = ($matrix[1][0] * $p1 + $matrix[1][1] * $p2) % 26;
            
            $result .= chr($c1 + 65) . chr($c2 + 65);
        }
        
        return $result;
    }
    
    /**
     * Decrypt text using Hill Cipher
     */
    public function decrypt($text) {
        $text = strtoupper(preg_replace('/[^A-Z]/', '', $text));
        if (strlen($text) % 2 != 0) {
            $text .= 'X';
        }
        
        $result = '';
        $invMatrix = $this->getInverseMatrix();
        
        for ($i = 0; $i < strlen($text); $i += 2) {
            $c1 = ord($text[$i]) - 65;
            $c2 = ord($text[$i + 1]) - 65;
            
            $p1 = ($invMatrix[0][0] * $c1 + $invMatrix[0][1] * $c2) % 26;
            $p2 = ($invMatrix[1][0] * $c1 + $invMatrix[1][1] * $c2) % 26;
            
            $result .= chr($p1 + 65) . chr($p2 + 65);
        }
        
        return $result;
    }
}

/**
 * Simple wrapper functions for easy use
 */
function encryptHill($text, $key) {
    $hill = new HillCipher($key);
    return $hill->encrypt($text);
}

function decryptHill($text, $key) {
    $hill = new HillCipher($key);
    return $hill->decrypt($text);
}
?>