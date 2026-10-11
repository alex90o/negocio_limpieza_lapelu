<?php
$host = 'localhost';
$dbname = 'negocio_limpieza_lapelu';
$username = 'root'; // Cambia según tu configuración de MySQL
$password = '';     // Cambia según tu configuración de MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>