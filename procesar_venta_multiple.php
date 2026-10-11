<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente = trim($_POST['cliente_nombre'] ?? 'Cliente General');
    $productos_ids = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if (!empty($productos_ids)) {
        try {
            $pdo->beginTransaction();

            $total_venta_general = 0;
            $items_venta = [];

            for ($i = 0; $i < count($productos_ids); $i++) {
                $prod_id = intval($productos_ids[$i]);
                $cant = floatval($cantidades[$i]); // Permite decimales

                if ($prod_id > 0 && $cant > 0) {
                    $stmt = $pdo->prepare("SELECT nombre, precio_unitario, stock, unidad_medida FROM productos WHERE id = :id FOR UPDATE");
                    $stmt->execute([':id' => $prod_id]);
                    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$prod || $prod['stock'] < $cant) {
                        throw new Exception("Stock insuficiente para el producto: " . ($prod['nombre'] ?? 'Desconocido'));
                    }

                    $subtotal = $cant * $prod['precio_unitario'];
                    $total_venta_general += $subtotal;

                    $items_venta[] = [
                        'id' => $prod_id,
                        'nombre' => $prod['nombre'],
                        'unidad_medida' => $prod['unidad_medida'],
                        'precio' => $prod['precio_unitario'],
                        'cantidad' => $cant,
                        'subtotal' => $subtotal
                    ];
                }
            }

            if (empty($items_venta)) {
                throw new Exception("No hay productos válidos en la venta.");
            }

            $stmtVenta = $pdo->prepare("INSERT INTO ventas (total) VALUES (:total)");
            $stmtVenta->execute([':total' => $total_venta_general]);
            $venta_id = $pdo->lastInsertId();

            foreach ($items_venta as $item) {
                $stmtDetalle = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, subtotal) VALUES (:v_id, :p_id, :cant, :sub)");
                $stmtDetalle->execute([
                    ':v_id' => $venta_id,
                    ':p_id' => $item['id'],
                    ':cant' => $item['cantidad'],
                    ':sub' => $item['subtotal']
                ]);

                $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cant WHERE id = :id");
                $stmtStock->execute([
                    ':cant' => $item['cantidad'],
                    ':id' => $item['id']
                ]);
            }

            $pdo->commit();
            header("Location: ver_ticket.php?id=" . $venta_id . "&cliente=" . urlencode($cliente));
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href='index.php';</script>";
        }
    } else {
        header("Location: index.php");
        exit();
    }
}
?>