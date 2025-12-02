<?php
// Helper functions

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function base_url($path = '') {
    $base = rtrim(BASE_URL, '/');
    
    if ($path === '') {
        return $base;
    }
    
    return $base . '/' . ltrim($path, '/');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}
?>

