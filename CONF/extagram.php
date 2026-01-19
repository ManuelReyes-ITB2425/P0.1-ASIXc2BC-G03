<!DOCTYPE html>
<html>
<head>
    <title>Extagram</title>
    <!-- Ruta relativa para Sprint 1 -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="POST" enctype="multipart/form-data" action="upload.php">
        <input type="text" name="post" placeholder="Write something...">
        <input id="file" type="file" name="photo" 
        onchange="document.getElementById('preview').src=window.URL.createObjectURL(event.target.files[0])">
        <label for="file">
            <!-- Ruta relativa para Sprint 1 -->
            <img id="preview" src="preview.svg">
        </label>
        <input type="submit" value="Publish">
    </form>

    <?php
    // IMPORTANTE: Para Sprint 1 usamos localhost
    $db = new mysqli("localhost", "extagram_admin", "pass123", "extagram_db");

    // Verificar conexión
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    foreach ($db->query("SELECT * FROM posts") as $fila) {
        echo "<div class='post'>";
        echo "<p>".$fila['post']."</p>";
        if (!empty($fila['photourl'])) {
            // Ruta relativa a la carpeta uploads
            echo "<img src='uploads/".$fila['photourl']."'>";
        }
        echo "</div>";
    }
    ?>
</body>
</html>
