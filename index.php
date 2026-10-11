<?php
include 'conexion.php';

// Obtener productos de la base de datos
$stmt = $pdo->query("SELECT id, nombre, stock, precio_unitario, unidad_medida FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de ventas recientes
$stmtHistorial = $pdo->query("
    SELECT v.id as venta_id, v.fecha, v.total 
    FROM ventas v
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
    <script>
        function agregarFilaVenta() {
            const container = document.getElementById('productos-venta-container');
            const primeraFila = container.querySelector('.fila-producto');
            const nuevaFila = primeraFila.cloneNode(true);
            
            // Limpiar valores de la nueva fila clonada
            nuevaFila.querySelector('select').value = "";
            nuevaFila.querySelector('input').value = "";
            
            container.appendChild(nuevaFila);
        }

        function eliminarFilaVenta(btn) {
            const container = document.getElementById('productos-venta-container');
            if (container.querySelectorAll('.fila-producto').length > 1) {
                btn.closest('.fila-producto').remove();
            } else {
                alert("Debe haber al menos un producto en la venta.");
            }
        }
    </script>
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
                            <th>Unidad</th>
                            <th>Stock</th>
                            <th>Precio Unitario</th>
                            <th>Añadir Mercadería</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-inventario">
                        <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($prod['unidad_medida']); ?></td>
                            <td><strong><?php echo htmlspecialchars($prod['stock']); ?></strong></td>
                            <td>$<?php echo number_format($prod['precio_unitario'], 2); ?></td>
                            
                            <td>
                                <form action="actualizar_stock.php" method="POST" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <input type="number" name="cantidad_agregada" placeholder="Cant." min="1" required style="width: 70px; margin-bottom: 0; padding: 5px;">
                                    <button type="submit" style="background-color: #27ae60; padding: 5px 10px; font-size: 0.85rem;">+ Sumar</button>
                                </form>
                            </td>

                            <td class="acciones-producto" style="display: flex; gap: 5px; align-items: center;">
                                <a href="editar_producto.php?id=<?php echo $prod['id']; ?>" style="text-decoration: none; padding: 5px 10px; background-color: #f39c12; color: white; border-radius: 4px; font-size: 0.9rem;">Editar</a>
                                
                                <form action="eliminar_producto.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de que desea eliminar este producto?');">
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" style="background-color: #e74c3c;">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="ventas">
            <h2>Registro de Ventas y Facturación</h2>
            <div class="form-section">
                <h3>Registrar Venta Múltiple</h3>
                <form action="procesar_venta_multiple.php" method="POST">
                    
                    <label><strong>Nombre del Cliente:</strong></label>
                    <input type="text" name="cliente_nombre" placeholder="Nombre del cliente o consumidor final" required style="margin-bottom: 15px;">

                    <label><strong>Productos de la Compra:</strong></label>
                    <div id="productos-venta-container">
                        <div class="fila-producto" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                            <select name="producto_id[]" required style="flex: 2; margin-bottom: 0;">
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($productos as $prod): ?>
                                    <?php if ($prod['stock'] > 0): ?>
                                        <option value="<?php echo $prod['id']; ?>">
                                            <?php echo htmlspecialchars($prod['nombre']); ?> ($<?php echo number_format($prod['precio_unitario'], 2); ?>) - Stock: <?php echo $prod['stock'] . ' ' . $prod['unidad_medida']; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            
                            <input type="number" name="cantidad[]" placeholder="Cantidad" min="1" required style="flex: 1; margin-bottom: 0;">
                            
                            <button type="button" onclick="eliminarFilaVenta(this)" style="background-color: #e74c3c; padding: 10px; margin-bottom: 0;">X</button>
                        </div>
                    </div>

                    <button type="button" onclick="agregarFilaVenta()" style="background-color: #27ae60; margin-top: 10px;">+ Agregar otro producto</button>
                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
                    
                    <button type="submit" style="background-color: #2980b9; font-size: 1.1rem; width: 100%;">Finalizar Venta y Generar Ticket</button>
                </form>
            </div>

            <div class="list-section">
                <h3>Últimas Ventas Registradas</h3>
                <ul id="historial-ventas">
                    <?php if (empty($historial_ventas)): ?>
                        <p>No hay ventas registradas todavía.</p>
                    <?php else: ?>
                        <?php foreach ($historial_ventas as $v): ?>
                            <li>
                                Venta #<?php echo $v['id']; ?> &bull; Fecha: <?php echo $v['fecha']; ?> &bull; <strong>Total: $<?php echo number_format($v['total'], 2); ?></strong>
                                <a href="ver_ticket.php?id=<?php echo $v['id']; ?>" target="_blank" style="margin-left: 15px; text-decoration: none; background: #3498db; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85rem;">Ver Ticket</a>
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