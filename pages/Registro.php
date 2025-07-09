<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/registro.css">
    <title>Registro</title>
</head>
<body>
    <div class="container">
        <div class="title">
            <img src="../img/fastbank.jpg" alt="" id="img_fastbank">
        </div>
        <form class="register" method="POST">
            <h1 id="lbl_nuevo_registro">Nuevo Registro</h1><br>
            <p>Nombre</p>
            <input type="text" name="newUsername" placeholder="Nombre de Usuario" required><br>
            <p>Primer Apellido</p>
            <input type="text" name="newApellidoP" placeholder="Primero Apellido" required><br>
            <p>Segundo Apellido</p>
            <input type="text" name="newApellidoM" placeholder="Segundo Apellido" required><br>
            <p>Contrasena</p>
            <input type="password" name="Contrasena" placeholder="Contrasena" required minlength="8"><br>
            <p>Correo electrónico</p>
            <input type="email" name="newEmail" placeholder="Correo Electrónico" required><br><br><br>
            <button type="submit" id="registrarse">Enviar</button><br><br>
            <a href="../pages/Login.php">Pantalla de Login</a>
        </form>
    </div>

    <?php
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mantenimientobd";

    $connection = new mysqli($host, $user, $password, $database);

    if ($connection->connect_error)
    {
        die("Connection failed".$connection->connect_error);
    }

        if ($_SERVER["REQUEST_METHOD"] == "POST" &&
            isset($_POST["newUsername"]) &&
            isset($_POST["newApellidoP"]) &&
            isset($_POST["newApellidoM"]) &&
            isset($_POST["Contrasena"]) &&
            isset($_POST["newEmail"])) {

            $nombreUsuario = $_POST["newUsername"];
            $apellidoP = $_POST["newApellidoP"];
            $apellidoM = $_POST["newApellidoM"];
            $correo = $_POST["newEmail"];
            $password = $_POST["Contrasena"];

            // validaciones básicas
            if (correoExiste($connection, $correo)) {
                echo "<dialog open id='dialog'><div class='mensajeError'>El correo electrónico ya está registrado</div></dialog>";
            } else {

                // insertar usuario
                $sql = "INSERT INTO usuario (nombreUsuario, apellidoP, apellidoM, correo, contraseña, activoEstado, id_rol) 
                        VALUES (?, ?, ?, ?, ?, 1, 2)";

                $stmt = $connection->prepare($sql);
                $stmt->bind_param("sssss", $nombreUsuario, $apellidoP, $apellidoM, $correo, $password);

                if ($stmt->execute()) {
                    echo "<dialog open id='dialog'><div class='mensajeExito'>Usuario registrado exitosamente</div></dialog>";
                } else {
                    echo "<dialog open id='dialog'><div class='mensajeError'>Error al registrar usuario</div></dialog>";
                }

                $stmt->close();
            }
        }

        // función para verificar si el correo ya existe
        function correoExiste($connection, $correo) {
            $query = $connection->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
            $query->bind_param("s", $correo);
            $query->execute();
            $query->store_result();
            $existe = $query->num_rows > 0;
            $query->close();
            return $existe;
        }
    ?>

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