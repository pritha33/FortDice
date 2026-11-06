<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin($allowedTypes = []) {
    if (!isLoggedIn()) {
        header('Location: ../index.php?error=Please login first');
        exit;
    }
    if (!empty($allowedTypes) && !in_array(getUserType(), $allowedTypes)) {
        header('Location: ../index.php?error=Access denied');
        exit;
    }
}
?>