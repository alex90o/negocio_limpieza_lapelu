<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad']);

    if ($producto_id > 0 && $cantidad > 0) {
        try {
            $pdo->beginTransaction();

            // Verificar stock y precio actual
            $stmtProd = $pdo->prepare("SELECT stock, precio_unitario FROM productos WHERE id = :id FOR UPDATE");
            $stmtProd->execute([':id' => $producto_id]);
            $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if ($producto && $producto['stock'] >= $cantidad) {
                $precio_unitario = $producto['precio_unitario'];
                $total_venta = $cantidad * $precio_unitario;

                // 1. Insertar venta principal
                $stmtVenta = $pdo->prepare("INSERT INTO ventas (total) VALUES (:total)");
                $stmtVenta->execute([':total' => $total_venta]);
                $venta_id = $pdo->lastInsertId();

                // 2. Insertar detalle de venta
                $stmtDetalle = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, subtotal) VALUES (:venta_id, :producto_id, :cantidad, :subtotal)");
                $stmtDetalle->execute([
                    ':venta_id' => $venta_id,
                    ':producto_id' => $producto_id,
                    ':cantidad' => $cantidad,
                    ':subtotal' => $total_venta
                ]);

                // 3. Descontar stock
                $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id");
                $stmtStock->execute([
                    ':cantidad' => $cantidad,
                    ':producto_id' => $producto_id
                ]);

                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}
header("Location: index.php");
exit();
?>