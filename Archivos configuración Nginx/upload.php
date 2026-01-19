<?php
if (!empty($_FILES['photo']['name'])) {

    // Genera un ID único para evitar que archivos con el mismo nombre se sobrescriban
    $photoid = uniqid();
    $destino = __DIR__ . '/uploads/' . $photoid;

    // Intenta mover el archivo desde la carpeta temporal al destino final
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destino)) {
        header("Location: /extagram.php");
        exit;
    } else {
        echo "ERROR move_uploaded_file";
    }
} else {
    // Si no se envió ninguna foto, redirige de vuelta
    header("Location: /extagram.php");
    exit;
}
?>
