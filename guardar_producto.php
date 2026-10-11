<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $stock = floatval($_POST['stock']); // Usamos floatval para permitir decimales
    $costo = floatval($_POST['precio_costo']);
    $precio = floatval($_POST['precio_unitario']);

    if (!empty($nombre) && !empty($unidad_medida) && $stock >= 0 && $costo >= 0 && $precio >= 0) {
        try {
            $sql = "INSERT INTO productos (nombre, unidad_medida, stock, precio_costo, precio_unitario) VALUES (:nombre, :unidad_medida, :stock, :costo, :precio)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':unidad_medida' => $unidad_medida,
                ':stock' => $stock,
                ':costo' => $costo,
                ':precio' => $precio
            ]);
        } catch (PDOException $e) {
            // Manejar error si es necesario
        }
    }
}
header("Location: index.php");
exit();
?>