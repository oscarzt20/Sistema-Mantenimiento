<?php
        session_start();
        // conexión
        $host = "localhost";
        $user = "root";
        $password = "";
        $database = "mantenimientobd";

        $connection = new mysqli($host, $user, $password, $database);

        if ($connection->connect_error)
            die("Error en la conexión: " . $connection->connect_error);

        // verifica si los datos fueron enviados por POST
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user"]) && isset($_POST["password"])) {
            $usuario = $_POST["user"];
            $contrasena = $_POST["password"];

            // consulta preparada para evitar inyección SQL
            $query = $connection->prepare("SELECT id_usuario, correo, contraseña, nombreUsuario FROM usuario WHERE correo = ?");
            $query->bind_param("s", $usuario);
            $query->execute();
            $resultado = $query->get_result();

            if ($resultado->num_rows > 0) {
                $datosUsuario = $resultado->fetch_assoc();

                // verifica contraseña
                if ($contrasena === $datosUsuario["contraseña"]) {
                    $_SESSION["id_usuario"] = $datosUsuario["id_usuario"];
                    $_SESSION["correo"] = $datosUsuario["correo"];
                    $_SESSION["nombreUsuario"] = $datosUsuario["nombreUsuario"];
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    mostrarError("Contraseña incorrecta");
                }
            } else {
                mostrarError("Usuario no encontrado");
            }

            $query->close();

        }

        function mostrarError($mensaje) {
            
            echo "<dialog open style='color:red; padding: 1em; border: 1px solid red;'>$mensaje</dialog>";
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/login.css">
    <title>login</title>
</head>
<body>
    <div class="mainContainer">
        <div class="loginContainer">
            <div class="title">
                <img src="../img/fastbank.jpg" alt="" id="img_fastbank">
            </div>
            <form method="POST" class="navLogin">
                <p class="p_login">Usuario</p>
                <input type="text" name="user" placeholder="Usuario">
                <p class="p_login">Constraseña</p>
                <input type="password" name="password" placeholder="Contraseña"> 
                <br><br><input type="submit" value="Iniciar Sesión" id="btt_iniciar_sesion"><br><br>
            </form>
        </div>
        <div class="registerContainer">
            <nav>
                <p>¿Aún no estás registrado?</p><a href="../pages/Registro.php">Registrate </a>
                <a href="../pages/dashboard.php"></a>
            </nav>
        </div>
    </div>

    <script>
        const dialog = document.querySelector("dialog");
        if (dialog) {
        setTimeout(() => {
            dialog.close();
            dialog.remove();
        }, 2000);
    }
    </script>
</body>
</html>