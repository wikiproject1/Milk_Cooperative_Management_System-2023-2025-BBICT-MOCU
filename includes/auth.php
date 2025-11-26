<?php
/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is a farmer
 */
function isFarmer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'farmer';
}

/**
 * Check if user is an industry
 */
function isIndustry() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'industry';
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

/**
 * Require farmer role
 */
function requireFarmer() {
    requireLogin();
    if (!isFarmer()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

/**
 * Require industry role
 */
function requireIndustry() {
    requireLogin();
    if (!isIndustry()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
} 