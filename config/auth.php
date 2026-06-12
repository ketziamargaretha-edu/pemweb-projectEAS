<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSupplier() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'supplier';
}

function isPembeli() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'pembeli';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireSupplier() {
    requireLogin();
    if (!isSupplier()) {
        header("Location: dashboard.php");
        exit();
    }
}

function requirePembeli() {
    requireLogin();
    if (!isPembeli()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>