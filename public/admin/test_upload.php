<?php
echo "<h2>TEST DE SUBIDA EN RENDER</h2>";

echo "<h3>POST:</h3>";
var_dump($_POST);

echo "<h3>FILES:</h3>";
var_dump($_FILES);

// Ruta destino EXACTA
$destino = $_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/test_" . time() . ".jpg";

echo "<h3>Ruta destino:</h3>";
echo $destino . "<br>";

if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/uploads/comercios/")) {
    echo "<p style='color:red;'>❌ La carpeta NO existe para escribir</p>";
} else {
    echo "<p style='color:green;'>✔ La carpeta existe</p>";
}

// Intentar mover archivo
if (!empty($_FILES['logo']['tmp_name'])) {

    echo "<h3>Intentando mover archivo...</h3>";

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $destino)) {
        echo "<p style='color:green;font-size:20px;'>✔ ARCHIVO SUBIDO CORRECTAMENTE</p>";
        echo "<p>Prueba a abrir la URL:</p>";
        echo "<a href='/uploads/comercios/" . basename($destino) . "'>" . basename($destino) . "</a>";
    } else {
        echo "<p style='color:red;font-size:20px;'>❌ ERROR moviendo archivo</p>";
        echo "<p>¿PHP permitió la subida temporal? Revisando permisos…</p>";
        echo "<p>Temp file: ".$_FILES['logo']['tmp_name']."</p>";
    }

} else {
    echo "<p style='color:red;'>❌ No llegó ningún archivo al servidor</p>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="logo">
    <button type="submit">Probar Subida</button>
</form>
