<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$color_msg = "";

// ====================================================
// VALIDACIÓN MANUAL POR CÓDIGO
// ====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["codigo_manual"])) {

    $codigo = trim($_POST["codigo_manual"]);

    if ($codigo !== "") {

        // Obtener datos del cupón
        $sql = $conn->prepare("
            SELECT c.*, 
                   u.nombre AS usuario_nombre, 
                   u.telefono AS usuario_telefono,
                   com.nombre AS comercio_nombre
            FROM cupones c
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            LEFT JOIN comercios com ON com.id = c.comercio_id
            WHERE c.codigo = ?
        ");
        $sql->bind_param("s", $codigo);
        $sql->execute();
        $cup = $sql->get_result()->fetch_assoc();

        if (!$cup) {
            $mensaje = "❌ Cupón no encontrado.";
            $color_msg = "#c0392b";

        } else {

            // 1️⃣ Validar caducidad
            if (!empty($cup["fecha_caducidad"]) && strtotime($cup["fecha_caducidad"]) < time()) {
                $mensaje = "⛔ Este cupón está CADUCADO.";
                $color_msg = "#c0392b";

            } else {

                $cup_id = $cup["id"];

                // 2️⃣ Contar casillas usadas
                $q1 = $conn->prepare("
                    SELECT COUNT(*) AS usadas
                    FROM cupon_casillas
                    WHERE cupon_id = ? AND estado = 1
                ");
                $q1->bind_param("i", $cup_id);
                $q1->execute();
                $usadas = $q1->get_result()->fetch_assoc()["usadas"];

                // Cupón completo → no validar más
                if ($usadas >= $cup["total_casillas"]) {
                    $mensaje = "🏆 Este cupón YA ESTÁ COMPLETADO.";
                    $color_msg = "#27ae60";

                    // asegurar marcado final
                    $conn->query("UPDATE cupones SET estado='usado' WHERE id=$cup_id");

                } else {

                    // 3️⃣ Buscar primera casilla libre
                    $q2 = $conn->prepare("
                        SELECT id, numero_casilla
                        FROM cupon_casillas
                        WHERE cupon_id = ? AND estado = 0
                        ORDER BY numero_casilla ASC
                        LIMIT 1
                    ");
                    $q2->bind_param("i", $cup_id);
                    $q2->execute();
                    $cas = $q2->get_result()->fetch_assoc();

                    if (!$cas) {
                        $mensaje = "🏆 Cupón completado.";
                        $color_msg = "#27ae60";

                        $conn->query("UPDATE cupones SET estado='usado' WHERE id=$cup_id");

                    } else {

                        // 4️⃣ Marcar casilla
                        $now = date("Y-m-d H:i:s");
                        $upd = $conn->prepare("
                            UPDATE cupon_casillas
                            SET estado = 1, fecha_marcado = ?
                            WHERE id = ?
                        ");
                        $upd->bind_param("si", $now, $cas["id"]);
                        $upd->execute();

                        // 5️⃣ Registrar validación
                        $reg = $conn->prepare("
                            INSERT INTO validaciones (cupon_id, comercio_id, fecha_validacion, metodo)
                            VALUES (?, ?, ?, 'ADMIN')
                        ");
                        $reg->bind_param("iis", $cup_id, $cup["comercio_id"], $now);
                        $reg->execute();

                        // Estado tras marcar
                        $nuevasUsadas = $usadas + 1;
                        $faltan = $cup["total_casillas"] - $nuevasUsadas;

                        // 6️⃣ Si se completó
                        if ($faltan == 0) {
                            $conn->query("UPDATE cupones SET estado='usado' WHERE id=$cup_id");

                            $mensaje = "🏆 Casilla {$cas['numero_casilla']} marcada. ¡Cupón COMPLETADO! 🎉";
                            $color_msg = "#27ae60";

                        } else {
                            $mensaje = "✔️ Casilla {$cas['numero_casilla']} marcada con éxito. Faltan $faltan casillas.";
                            $color_msg = "#27ae60";
                        }
                    }
                }
            }
        }
    }
}

include "_header.php";
?>

<h1>Validación de Cupones</h1>

<div class="card">

<?php if ($mensaje): ?>
    <div class="msg-box" style="background: <?= $color_msg ?>; 
        padding:15px; color:white; border-radius:10px; font-size:18px; margin-bottom:20px; text-align:center;">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<h3>Validación Manual</h3>

<form method="POST">
    <input type="text" name="codigo_manual" placeholder="Introduce el código del cupón" required>
    <button class="btn btn-success" type="submit">
        Validar Cupón
    </button>
</form>

</div>

<?php include "_footer.php"; ?>
