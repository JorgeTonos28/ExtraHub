<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Si no hay una sesión de usuario, redirigir a login.php
    function getBaseUrl() {
        // Protocolo (http o https)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        
        // Dominio del servidor
        $domain = $_SERVER['HTTP_HOST'];

        // URL base
        $baseUrl = $protocol . '://' . $domain;

        return $baseUrl;
    }

    $baseUrl = getBaseUrl();

    header('Location: ' . $baseUrl . '/login.php');
    exit;
}
?>