<?php
include 'conexion.php';

// Obtener productos de la base de datos (incluyendo la unidad de medida)
$stmt = $pdo->query("SELECT id, nombre, stock, precio_unitario, unidad_medida FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de ventas recientes
$stmtHistorial = $pdo->query("
    SELECT dv.cantidad, dv.subtotal, v.fecha, p.nombre as producto_nombre, p.unidad_medida 
    FROM detalle_ventas dv
    JOIN ventas v ON dv.venta_id = v.id
    JOIN productos p ON dv.producto_id = p.id
    ORDER BY v.fecha DESC LIMIT 10
");
$historial_ventas = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Negocio de Limpieza</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <!--<h1>Control de Negocio de Limpieza La Pelu</h1> -->
    </header>
    <br><br><br><br>
    <main class="fondo">
        
        <section id="productos">
            <h2>Productos</h2>
            <div class="form-section">
                <h3>Añadir Nuevo Producto</h3>
                <form action="guardar_producto.php" method="POST">
                    <input type="text" name="nombre" placeholder="Nombre del Producto (ej. Lavandina)" required>
                    <input type="text" name="unidad_medida" placeholder="Unidad de Medida (ej. Litros, Kilos, Unidades)" required>
                    <input type="number" name="stock" placeholder="Stock Inicial" min="0" required>
                    <input type="number" name="precio_unitario" placeholder="Precio Unitario" min="0" step="0.01" required>
                    <button type="submit">Añadir Producto</button>
                </form>
            </div>
            <div class="list-section">
                <h3>Inventario Actual</h3>
                <table id="tabla-inventario">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Unidad de Medida</th>
                            <th>Stock</th>
                            <th>Precio Unitario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-inventario">
                        <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($prod['unidad_medida']); ?></td>
                            <td><?php echo htmlspecialchars($prod['stock']); ?></td>
                            <td>$<?php echo number_format($prod['precio_unitario'], 2); ?></td>
                            <td class="acciones-producto">
                                <form action="eliminar_producto.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este producto?');">
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="ventas">
            <h2>Registro de Ventas</h2>
            <div class="form-section">
                <h3>Registrar Venta</h3>
                <form action="registrar_venta.php" method="POST">
                    <select name="producto_id" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $prod): ?>
                            <?php if ($prod['stock'] > 0): ?>
                                <option value="<?php echo $prod['id']; ?>">
                                    <?php echo htmlspecialchars($prod['nombre']); ?> ($<?php echo number_format($prod['precio_unitario'], 2); ?>) - Stock: <?php echo $prod['stock'] . ' ' . $prod['unidad_medida']; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="cantidad" placeholder="Cantidad Vendida" min="1" required>
                    <button type="submit">Registrar Venta</button>
                </form>
            </div>
            <div class="list-section">
                <h3>Historial de Ventas</h3>
                <ul id="historial-ventas">
                    <?php if (empty($historial_ventas)): ?>
                        <p>No hay ventas registradas todavía.</p>
                    <?php else: ?>
                        <?php foreach ($historial_ventas as $venta): ?>
                            <li>
                                Vendidos <?php echo $venta['cantidad'] . ' ' . $venta['unidad_medida']; ?> de <?php echo htmlspecialchars($venta['producto_nombre']); ?> a $<?php echo number_format($venta['subtotal'], 2); ?> (<?php echo $venta['fecha']; ?>)
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Control de Negocio de Limpieza La Pelu</p>
    </footer>

</body>
</html>