<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    $sql = $conn->prepare("SELECT id, password FROM admin WHERE usuario = ?");
    $sql->bind_param("s", $usuario);
    $sql->execute();
    $res = $sql->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();

        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id"];
            header("Location: dashboard.php");
            exit;
        } else $msg = "Contraseña incorrecta";
        
    } else $msg = "Usuario no encontrado";
}
?>
