<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Error si tiene ventas asociadas u otra restricción
        }
    }
}
header("Location: index.php");
exit();
?>