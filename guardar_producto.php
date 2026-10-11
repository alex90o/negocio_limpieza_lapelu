<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $stock = intval($_POST['stock']);
    $precio = floatval($_POST['precio_unitario']);
    $unidad_medida = trim($_POST['unidad_medida']); // <-- Nuevo campo

    if (!empty($nombre) && $stock >= 0 && $precio >= 0 && !empty($unidad_medida)) {
        try {
            $sql = "INSERT INTO productos (nombre, stock, precio_unitario, unidad_medida) VALUES (:nombre, :stock, :precio, :unidad_medida)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':stock' => $stock,
                ':precio' => $precio,
                ':unidad_medida' => $unidad_medida
            ]);
        } catch (PDOException $e) {
            // Manejar error si es necesario
        }
    }
}
header("Location: index.php");
exit();
?>