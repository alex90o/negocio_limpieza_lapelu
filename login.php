<?php
session_start();
include 'conexion.php';

// Si ya inició sesión, redirigir al inicio
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($usuario) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :user");
        $stmt->execute([':user' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Guardar datos en la sesión
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario'] = $user['usuario'];

            header("Location: index.php");
            exit();
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - La Pelu</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Contenedor central que respeta los espacios del header */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
            width: 100%;
        }

        .login-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        /* Titular en barra negra con texto blanco visible */
        .login-card h2 {
            background-color: #000000;
            color: #ffffff !important;
            text-align: center;
            padding: 10px 15px;
            border-radius: 20px;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        /* Formulario con campos apilados verticalmente */
        .login-card form {
            display: flex;
            flex-direction: column;
        }

        .login-card label {
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
            text-align: left;
            display: block;
        }

        .login-card input[type="text"],
        .login-card input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            display: block;
        }

        .login-card button {
            margin-top: 20px;
            padding: 12px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .login-card button:hover {
            background-color: #1f618d;
        }

        .error-msg {
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <header>
        <!-- Mantén la misma cabecera que en el resto del sitio -->
    </header>
    <br><br><br><br>

    <main class="fondo">
        <div class="login-wrapper">
            <div class="login-card">
                <h2>Iniciar Sesión</h2>
                <p style="text-align: center; color: #7f8c8d; margin-top: 0; margin-bottom: 20px; font-size: 0.95rem;">
                    Control de Negocio "La Pelu"
                </p>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required autocomplete="off">

                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>

                    <button type="submit">Ingresar</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Control de Negocio de Limpieza La Pelu</p>
    </footer>

</body>
</html>