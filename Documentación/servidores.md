# Servidor S4

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

[Ver el archivo upload.php](../CONF/upload.php)
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

# Servidor S5

En esta documentación se detallan los archivos y configuraciones necesarios para lograr que el **Servicio 5 (S5)** funcione correctamente.

---

## 🖼️ Función
La función principal del servidor S5 es **servir las imágenes** que han sido cargadas previamente por el Servicio 4 en el directorio compartido `uploads`. Actúa como el servidor de entrega de contenido estático (fotos) de Extagram.

---

## 🐳 Docker-compose.yml
El archivo más importante para el funcionamiento de este servicio es el `docker-compose.yml` (archivo principal de Docker). 

Aunque este archivo contiene la configuración de toda la infraestructura, para el S5 nos centramos en las siguientes directivas:

<img width="527" height="216" alt="image" src="https://github.com/user-attachments/assets/4c1de136-91c5-4c8b-9d14-f13c9a424072" />

### Explicación de parámetros:

| Parámetro | Descripción |
| :--- | :--- |
| **s5-images** | Establece el nombre de la directiva del servicio. Todo lo definido bajo este parámetro se aplica exclusivamente a este contenedor. |
| **image** | Utiliza `nginx:alpine`. Se usa **Nginx** para servir contenido estático y **Alpine** por ser una imagen extremadamente ligera y óptima para esta tarea. |
| **container_name** | Define el nombre del contenedor en ejecución como `s5_images`. |
| **volumes** | Mapea y sirve la carpeta `uploads`. Aquí es donde se localizan todas las fotos subidas a la plataforma. |
| **networks** | Conecta el contenedor a la red compartida `extagram-net`. |

---


