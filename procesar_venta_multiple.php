<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'conexion.php';
?>
<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? 'Cliente General');
    $productos_ids = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if (empty($cliente_nombre)) {
        $cliente_nombre = 'Cliente General';
    }

    if (!empty($productos_ids)) {
        try {
            $pdo->beginTransaction();

            // 1. Obtener o registrar el cliente
            $stmtCliente = $pdo->prepare("SELECT id FROM clientes WHERE LOWER(nombre) = LOWER(:nombre)");
            $stmtCliente->execute([':nombre' => $cliente_nombre]);
            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $cliente_id = $cliente['id'];
            } else {
                $stmtInsertCliente = $pdo->prepare("INSERT INTO clientes (nombre) VALUES (:nombre)");
                $stmtInsertCliente->execute([':nombre' => $cliente_nombre]);
                $cliente_id = $pdo->lastInsertId();
            }

            $total_venta_general = 0;
            $items_venta = [];

            // 2. Validar stock, costos y precios del momento
            for ($i = 0; $i < count($productos_ids); $i++) {
                $prod_id = intval($productos_ids[$i]);
                $cant = floatval($cantidades[$i]);

                if ($prod_id > 0 && $cant > 0) {
                    $stmt = $pdo->prepare("SELECT nombre, precio_costo, precio_unitario, stock, unidad_medida FROM productos WHERE id = :id FOR UPDATE");
                    $stmt->execute([':id' => $prod_id]);
                    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$prod || $prod['stock'] < $cant) {
                        throw new Exception("Stock insuficiente para el producto: " . ($prod['nombre'] ?? 'Desconocido'));
                    }

                    $precio_costo = floatval($prod['precio_costo']);
                    $precio_venta = floatval($prod['precio_unitario']);
                    $subtotal = $cant * $precio_venta;
                    $total_venta_general += $subtotal;

                    $items_venta[] = [
                        'id' => $prod_id,
                        'nombre' => $prod['nombre'],
                        'unidad_medida' => $prod['unidad_medida'],
                        'costo' => $precio_costo,
                        'precio' => $precio_venta,
                        'cantidad' => $cant,
                        'subtotal' => $subtotal
                    ];
                }
            }

            if (empty($items_venta)) {
                throw new Exception("No hay productos válidos en la venta.");
            }

            // 3. Registrar la venta
            $stmtVenta = $pdo->prepare("INSERT INTO ventas (cliente_id, total) VALUES (:cliente_id, :total)");
            $stmtVenta->execute([
                ':cliente_id' => $cliente_id,
                ':total' => $total_venta_general
            ]);
            $venta_id = $pdo->lastInsertId();

            // 4. Registrar detalle guardando precio unitario y costo
            foreach ($items_venta as $item) {
                $stmtDetalle = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, precio_costo, subtotal) VALUES (:v_id, :p_id, :cant, :precio, :costo, :sub)");
                $stmtDetalle->execute([
                    ':v_id' => $venta_id,
                    ':p_id' => $item['id'],
                    ':cant' => $item['cantidad'],
                    ':precio' => $item['precio'],
                    ':costo' => $item['costo'],
                    ':sub' => $item['subtotal']
                ]);

                $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cant WHERE id = :id");
                $stmtStock->execute([
                    ':cant' => $item['cantidad'],
                    ':id' => $item['id']
                ]);
            }

            $pdo->commit();
            header("Location: ver_ticket.php?id=" . $venta_id);
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