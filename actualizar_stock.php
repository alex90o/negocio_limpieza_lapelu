<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = intval($_POST['id']);
    $cantidad_agregada = intval($_POST['cantidad_agregada']);

    if ($producto_id > 0 && $cantidad_agregada > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id");
            $stmt->execute([
                ':cantidad' => $cantidad_agregada,
                ':id' => $producto_id
            ]);
        } catch (PDOException $e) {
            // Manejar error si es necesario
        }
    }
}
header("Location: index.php");
exit();
?>