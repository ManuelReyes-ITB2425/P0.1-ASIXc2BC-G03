# Servidor S4 - Documentación de Servicio

En esta documentación se detallan los archivos y configuraciones realizados para que el **Servicio 4 (S4)** funcione correctamente.

## 🚀 Función
El servidor S4 implementa el servicio **PHP-FPM** para ejecutar el script `upload.php`. En este script se gestionan las imágenes insertadas por los usuarios de **Extagram**. 

> **Nota importante:** Todos los archivos se almacenan dentro del directorio `uploads`.

---

## 📂 Archivos Vitales

### 1. docker-compose.yml
Es el archivo de orquestación global. Define los parámetros necesarios para que el contenedor se despliegue con los recursos y redes adecuados.

<img width="596" height="191" alt="image" src="https://github.com/user-attachments/assets/fdfb1395-4cfd-474f-8282-54c43c226de7" />

### 2. upload.php
Este script es el núcleo de la gestión de archivos. Realiza dos funciones críticas:
* **Gestión de archivos:** Procesa la subida de imágenes.
* **Sincronización:** Vincula los datos con la **Base de Datos**. Al guardar la información en el directorio `uploads` y en la BD simultáneamente, se garantiza un sistema protegido ante caídas.

[Ver el archivo upload.php](upload.php)
---

## 🛠️ Explicación de Parámetros (Docker)

| Directiva | Descripción |
| :--- | :--- |
| **s4-upload** | Nombre de la directiva del servicio en el archivo Docker Compose. |
| **image** | Imagen basada en `php-alpine`. PHP es indispensable para el script y Alpine garantiza ligereza y eficiencia. |
| **container_name** | Define el nombre del contenedor en ejecución como `s4_upload`. |
| **volumes** | Configura dos rutas: una para los archivos HTML y otra específica para el almacenamiento de imágenes en `uploads`. |
| **networks** | Conecta el servicio a la red `extagram-net` para permitir la comunicación con el resto de contenedores. |
| **command** | Comando encargado de instalar las dependencias y herramientas de PHP necesarias. |

---

## ⚠️ Otras Modificaciones

### Permisos del Directorio
Es necesario configurar correctamente los **permisos en el directorio `uploads`**. 
<img width="573" height="51" alt="image" src="https://github.com/user-attachments/assets/db721f3e-92d5-4bec-80a0-17faeaa0cddc" />
<img width="548" height="224" alt="image" src="https://github.com/user-attachments/assets/05cbc60a-a817-48dd-865e-74696f318f5f" />

Si no se otorgan los permisos de lectura y escritura adecuados al usuario que ejecuta PHP, el sistema no podrá gestionar las imágenes, resultando en errores de ejecución en la plataforma.

---
*Documentación generada para el despliegue del proyecto Extagram.*
