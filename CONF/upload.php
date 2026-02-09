<?php
// Conexión usando el nombre del servicio Docker 's7-db'
$db = new mysqli("s7-db", "extagram_admin", "pass123", "extagram_db");

if (!empty($_POST["post"]) || !empty($_FILES['photo']['name'])) {
    
    $photoid = "";
    $blobData = null; // Variable para el binario

    // 1. GESTIÓN DE LA IMAGEN
    if (!empty($_FILES['photo']['name'])){
        $photoid = uniqid();
        $rutaTemporal = $_FILES['photo']['tmp_name'];
        $rutaDestino = 'uploads/' . $photoid;

        // A) Guardar en carpeta (Para S5)
        move_uploaded_file($rutaTemporal, $rutaDestino);

        // B) Leer el contenido binario (Para S7 - Requisito Sprint 2)
        // Leemos el fichero que acabamos de subir
        $blobData = file_get_contents($rutaDestino);
    }

    // 2. GUARDAR EN BBDD
    // Preparamos la consulta con 3 interrogantes: Texto, ID, BLOB
    $stmt = $db->prepare("INSERT INTO posts (post, photourl, image_blob) VALUES (?, ?, ?)");
    
    // "ssBs" es un truco, pero "s" (string) suele funcionar para blobs pequeños/medianos en PHP moderno.
    // Lo correcto es usar send_long_data para archivos grandes, pero para esta práctica:
    // null en el bind_param y luego send_long_data es lo más seguro, 
    // pero para simplificar probaremos pasándolo directo como string si no es muy grande.
    
    $null = NULL;
    $stmt->bind_param("ssb", $_POST["post"], $photoid, $null);
    
    // Enviamos el paquete binario
    if ($blobData !== null) {
        $stmt->send_long_data(2, $blobData);
    }
    
    $stmt->execute();
    $stmt->close();
    $db->close();
}

header("Location: /");
?>
ubuntu
