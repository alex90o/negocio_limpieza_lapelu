<?php
include 'conexion.php';

// Filtro de fechas (por defecto: mes actual)
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Consulta para calcular ingresos, costos y ganancias
$sql = "
    SELECT 
        COUNT(DISTINCT v.id) as total_ventas,
        SUM(dv.subtotal) as total_ingresos,
        SUM(dv.cantidad * CASE WHEN dv.precio_costo > 0 THEN dv.precio_costo ELSE p.precio_costo END) as total_costos
    FROM ventas v
    JOIN detalle_ventas dv ON v.id = dv.venta_id
    JOIN productos p ON dv.producto_id = p.id
    WHERE DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':fecha_inicio' => $fecha_inicio,
    ':fecha_fin' => $fecha_fin
]);
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

$total_ingresos = floatval($resumen['total_ingresos'] ?? 0);
$total_costos = floatval($resumen['total_costos'] ?? 0);
$ganancia_neta = $total_ingresos - $total_costos;
$total_ventas = intval($resumen['total_ventas'] ?? 0);

// Consulta para ver el detalle de productos vendidos en ese período
$sqlDetalle = "
    SELECT 
        p.nombre,
        p.unidad_medida,
        SUM(dv.cantidad) as cantidad_vendida,
        SUM(dv.subtotal) as total_recaudado,
        SUM(dv.cantidad * CASE WHEN dv.precio_costo > 0 THEN dv.precio_costo ELSE p.precio_costo END) as costo_total
    FROM detalle_ventas dv
    JOIN ventas v ON dv.venta_id = v.id
    JOIN productos p ON dv.producto_id = p.id
    WHERE DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY dv.producto_id
    ORDER BY total_recaudado DESC
";
$stmtDetalle = $pdo->prepare($sqlDetalle);
$stmtDetalle->execute([
    ':fecha_inicio' => $fecha_inicio,
    ':fecha_fin' => $fecha_fin
]);
$productos_vendidos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ganancias - La Pelu</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .resumen-cards {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .card {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 8px;
            color: white;
            text-align: center;
        }

        .card.ingresos { background-color: #2980b9; }
        .card.costos { background-color: #e67e22; }
        .card.ganancia { background-color: #27ae60; }
        .card h3 { margin: 0 0 10px 0; font-size: 1rem; text-transform: uppercase; }
        .card p { margin: 0; font-size: 1.8rem; font-weight: bold; }

        .filtro-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filtro-form div { flex: 1; min-width: 150px; }
        .filtro-form label { display: block; margin-bottom: 5px; font-weight: bold; }
    </style>
</head>
<body>

    <header>
       
    </header>
    <br><br><br><br>

    <main class="fondo">
        <section id="reporte-ganancias">
            <h2>Reporte de Ganancias y Ventas</h2>

            <div class="form-section">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #2c3e50;">Filtrar Período</h3>
                    <a href="index.php" style="text-decoration: none; padding: 8px 15px; background: #7f8c8d; color: white; border-radius: 4px; font-weight: bold; font-size: 0.9rem;">Volver al Inicio</a>
                </div>

                <form method="GET" action="reporte_ganancias.php" class="filtro-form">
                    <div>
                        <label>Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
                    </div>
                    <div>
                        <label>Fecha Fin:</label>
                        <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
                    </div>
                    <div>
                        <button type="submit" style="background-color: #2980b9;">Calcular Reporte</button>
                    </div>
                </form>

                <div class="resumen-cards">
                    <div class="card ingresos">
                        <h3>Total Ventas</h3>
                        <p>$<?php echo number_format($total_ingresos, 2); ?></p>
                        <small><?php echo $total_ventas; ?> transacciones</small>
                    </div>
                    <div class="card costos">
                        <h3>Costo de Mercadería</h3>
                        <p>$<?php echo number_format($total_costos, 2); ?></p>
                    </div>
                    <div class="card ganancia">
                        <h3>Ganancia Neta</h3>
                        <p>$<?php echo number_format($ganancia_neta, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="list-section">
                <h3>Desglose de Productos Vendidos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant. Vendida</th>
                            <th>Total Venta ($)</th>
                            <th>Total Costo ($)</th>
                            <th>Ganancia ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos_vendidos)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No hay ventas registradas en el período seleccionado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_vendidos as $p): 
                                $ganancia_prod = $p['total_recaudado'] - $p['costo_total'];
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td>
                                <td><?php echo $p['cantidad_vendida'] . ' ' . $p['unidad_medida']; ?></td>
                                <td>$<?php echo number_format($p['total_recaudado'], 2); ?></td>
                                <td>$<?php echo number_format($p['costo_total'], 2); ?></td>
                                <td style="color: #27ae60; font-weight: bold;">$<?php echo number_format($ganancia_prod, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Control de Negocio de Limpieza La Pelu</p>
    </footer>

</body>
</html>