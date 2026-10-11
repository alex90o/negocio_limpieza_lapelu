<?php
include 'conexion.php';

// Si envían el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $stock = intval($_POST['stock']);
    $precio = floatval($_POST['precio_unitario']);

    if ($id > 0 && !empty($nombre) && !empty($unidad_medida) && $stock >= 0 && $precio >= 0) {
        try {
            $sql = "UPDATE productos SET nombre = :nombre, unidad_medida = :unidad_medida, stock = :stock, precio_unitario = :precio WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':unidad_medida' => $unidad_medida,
                ':stock' => $stock,
                ':precio' => $precio,
                ':id' => $id
            ]);
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, complete todos los campos correctamente.";
    }
}

// Si cargan la página con un ID por GET, buscamos los datos actuales
$producto = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$producto) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Producto - Control de Negocio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Modificar Producto</h1>
    </header>
    <main class="fondo" style="max-width: 600px; margin: 40px auto; padding: 20px;">
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="editar_producto.php" method="POST" class="form-section">
            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
            
            <label>Nombre del Producto:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
            
            <label>Unidad de Medida:</label>
            <input type="text" name="unidad_medida" value="<?php echo htmlspecialchars($producto['unidad_medida']); ?>" required>
            
            <label>Stock Actual:</label>
            <input type="number" name="stock" value="<?php echo $producto['stock']; ?>" min="0" required>
            
            <label>Precio Unitario ($):</label>
            <input type="number" name="precio_unitario" value="<?php echo $producto['precio_unitario']; ?>" min="0" step="0.01" required>
            
            <div style="margin-top: 15px;">
                <button type="submit" style="background-color: #27ae60;">Guardar Cambios</button>
                <a href="index.php" style="text-decoration: none; padding: 10px 15px; background: #7f8c8d; color: white; border-radius: 4px; margin-left: 10px;">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>