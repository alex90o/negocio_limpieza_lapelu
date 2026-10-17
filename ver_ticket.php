<?php
include 'conexion.php';

$venta_id = intval($_GET['id'] ?? 0);

if ($venta_id <= 0) {
    header("Location: index.php");
    exit();
}

// Obtener datos de la venta y cliente
$stmtVenta = $pdo->prepare("
    SELECT v.*, COALESCE(c.nombre, 'Cliente General') as cliente_nombre 
    FROM ventas v
    LEFT JOIN clientes c ON v.cliente_id = c.id
    WHERE v.id = :id
");
$stmtVenta->execute([':id' => $venta_id]);
$venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    echo "<script>alert('Venta no encontrada.'); window.location.href='index.php';</script>";
    exit();
}

// Obtener los detalles usando el precio unitario guardado en la venta
$stmtDetalles = $pdo->prepare("
    SELECT dv.cantidad, dv.subtotal, 
           CASE 
               WHEN dv.precio_unitario > 0 THEN dv.precio_unitario 
               ELSE (dv.subtotal / dv.cantidad) 
           END as precio_unitario,
           p.nombre, p.unidad_medida 
    FROM detalle_ventas dv
    JOIN productos p ON dv.producto_id = p.id
    WHERE dv.venta_id = :id
");
$stmtDetalles->execute([':id' => $venta_id]);
$detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta #<?php echo $venta['id']; ?> - La Pelu</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f4f7f6;
            margin: 0;
            padding: 20px;
        }
        .ticket {
            max-width: 400px;
            background: #fff;
            margin: 0 auto;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3, p {
            text-align: center;
            margin: 5px 0;
        }
        .info {
            margin: 15px 0;
            font-size: 0.9rem;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        th, td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .total {
            text-align: right;
            font-size: 1.1rem;
            font-weight: bold;
            margin-top: 15px;
        }
        .acciones {
            text-align: center;
            margin-top: 20px;
        }
        .acciones button, .acciones a {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 1rem;
            margin: 0 5px;
        }
        .acciones button:hover, .acciones a:hover {
            background: #2980b9;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .ticket {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .acciones {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="ticket">
        <h2>🐶 Control de Negocio 🐶</h2>
        <h3>Limpieza "La Pelu"</h3>
        <p>Ticket de Venta #<?php echo $venta['id']; ?></p>
        
        <div class="info">
            <p><strong>Fecha:</strong> <?php echo $venta['fecha']; ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Cant. / Prod.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det): ?>
                <tr>
                    <td>
                        <?php echo $det['cantidad'] . ' ' . $det['unidad_medida'] . ' - ' . htmlspecialchars($det['nombre']); ?><br>
                        <small style="color: #666;">($<?php echo number_format($det['precio_unitario'], 2); ?> c/u)</small>
                    </td>
                    <td>$<?php echo number_format($det['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Total: $<?php echo number_format($venta['total'], 2); ?>
        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 0.85rem; color: #666;">¡Gracias por su compra!</p>

        <div class="acciones">
            <button onclick="window.print()">Imprimir Ticket</button>
            <a href="index.php">Volver al Inicio</a>
        </div>
    </div>

</body>
</html>