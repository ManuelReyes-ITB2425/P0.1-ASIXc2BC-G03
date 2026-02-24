# Guía de Instalación: MariaDB y PHP en AWS

Esta guía detalla los pasos a paso, los comandos y comprobaciones realizadas para una instalacion de los servicios de base de datos MariaDB y el lenguaje de programación PHP en una instancia de AWS.

---

## 1. Instalación de MariaDB

Para instalar MariaDB en las versiones más recientes de AWS tenemos que tener en cuenta que usan "dnf" de tal manera que ejecutamos el siguiente comando. 

> **Nota:** En AWS, el paquete específico para realizar la instalacion correctamente es "mariadb105".

```bash
# Instalamos el servidor MariaDB y el cliente
sudo dnf install mariadb105-server -y

# Iniciamos el servicio
sudo systemctl start mariadb

# Habilitamos el servicio para que inicie automáticamente con el sistema
sudo systemctl enable mariadb

```
<img width="975" height="770" alt="Captura de pantalla de 2025-12-16 16-04-42" src="https://github.com/user-attachments/assets/8a7a5574-183e-4c3f-a177-09db228f1b16" />
<img width="855" height="204" alt="Captura de pantalla de 2025-12-16 16-06-13" src="https://github.com/user-attachments/assets/3cd28576-9ff4-4d45-9501-54c6443ffba3" />

 En la imagen adjuntada podemos ver como hacemos la comprobación de que el servicio este activo, de esta manera nos podemos asegurar de que se ha hecho la instalación correctamente.


# Instalación y Verificación de PHP en AWS

Este apartado detalla el proceso de instalación de PHP y las pruebas necesarias para confirmar que el entorno está listo.

---

## 2. Instalación de PHP
Para instalar PHP junto con los módulos comunes para trabajar con servidores web y bases de datos (como MariaDB), ejecuta:

```bash
# Instalamos PHP y extensiones necesarias
sudo dnf install php php-common php-mysqli php-fpm php-cli -y
```
<img width="961" height="764" alt="Captura de pantalla de 2025-12-16 16-21-35" src="https://github.com/user-attachments/assets/ce4ef3f7-d640-4fff-82bb-4ac49685980e" />

Adjuntamos comprobacion del servicio activo e instalado correctamente de tal manera que ambos serivicos estan preparados para usar.

<img width="978" height="509" alt="Captura de pantalla de 2025-12-16 16-22-09" src="https://github.com/user-attachments/assets/84b52aae-6a36-42b4-9e91-28aa716b0493" />

# Implementacion de BBDD con servidor web NGINX

## 1. Modificación de archivos PHP

<img width="1102" height="826" alt="Captura de pantalla de 2026-01-13 16-35-37" src="https://github.com/user-attachments/assets/7accaad7-a369-4ae0-90ce-711030dcf038" />


Hemos editado el archivo extragram.php, como se muestra en la imagen adjunta, de tal manera que hemos podido modificar la página web para que resulte visualmente atractiva y con una interfaz más cómoda.

<img width="1111" height="815" alt="Captura de pantalla de 2026-01-13 16-35-58" src="https://github.com/user-attachments/assets/4be4f794-0c18-4da1-8705-8401cb623e32" />


Por otra parte, también hemos modificado el archivo style.css para hacer la página web mucho más atractiva visualmente y más dinámica. Como se puede apreciar en la imagen adjunta, se pueden ver diferentes emoticonos que mejoran notablemente el aspecto visual de la web.

Adjuntamos la evolucion de la pagina web gracias a la modifiaccion de archivos:
## FASE 1
<img width="1364" height="299" alt="Captura de pantalla de 2026-01-13 15-29-47" src="https://github.com/user-attachments/assets/33673cfc-7410-4d82-9ccc-e486553714bc" />

## FASE 2

<img width="1724" height="901" alt="Captura de pantalla de 2026-01-13 15-48-14" src="https://github.com/user-attachments/assets/679ae78f-bcbc-462c-9f90-50f30a9537e4" />

## FASE 3

<img width="837" height="453" alt="image" src="https://github.com/user-attachments/assets/08a0c082-22c8-436a-8539-bcbc5f754e06" />

Esta última imagen muestra cómo ha quedado finalmente la web. El funcionamiento es el siguiente:

1. Hacemos clic en la imagen de la cámara para adjuntar la imagen que queremos publicar.
<img width="837" height="453" alt="Captura de pantalla de 2026-01-19 15-26-49" src="https://github.com/user-attachments/assets/e673ee50-830a-4d14-84bc-74bee04d3667" />

2. Seleccionamos la imagen y añadimos un comentario si queremos incluir un pie de foto.
<img width="887" height="469" alt="Captura de pantalla de 2026-01-19 15-15-29" src="https://github.com/user-attachments/assets/ccaa2550-8155-47a3-af47-085d354a7285" />

3. Pulsamos el botón de Publicar, de modo que la imagen, junto con el pie de foto escrito, se publica en la página web y queda almacenada en la base de datos.
<img width="790" height="901" alt="Captura de pantalla de 2026-01-19 15-15-39" src="https://github.com/user-attachments/assets/93a99d65-c535-45bd-b348-3236319f83bd" />

## Almacenamiento en la Base de datos

Todos los datos publicados en la pagina web, tanto las imagenes como los pies de pagina escritos se guardan de manera automatica en la Base de datos del servidor AWS de la siguiente manera:

<img width="745" height="362" alt="image" src="https://github.com/user-attachments/assets/c50518c0-011c-4996-adbc-3a598ffb6584" />

Podemos apreciar como todos los datos se almacenan de manera segura en la base de datos interna.

En la imagen podemos ver como se ha gestionado el orden de la base de datos y el almacenamiento de las imagenes 

<img width="727" height="621" alt="Captura de pantalla de 2026-01-26 16-26-34" src="https://github.com/user-attachments/assets/63f71959-51fa-4b26-a48f-ab2a56e2ded9" />

## Base de datos final

En esta imagen podemos ver como ha quedado finalmente la base de datos y los campos que almaceamos de cada una de las imagenes que se suben a nuestra web.

<img width="736" height="486" alt="image" src="https://github.com/user-attachments/assets/8cecf63d-f490-4068-b800-acc3d9d77ae9" />

#  Hardening y securitzación Base de Dades

## Aislamiento de Red
En esta imagen podemos ver como una de las cosas más importante que hemos realizado para mejorar el hardening y la securizacón de la base de datos ha sido bloquear todo tipo de pings y puertos inecesarios para aislar la base de datos. En la siguiente imagen vemos como si intentamos hacer ping nos da conexión rechazada.
<img width="760" height="239" alt="Captura de pantalla de 2026-02-23 15-39-06" src="https://github.com/user-attachments/assets/caf58a56-c0e8-4ebd-b970-edb6fb42d2e9" />

## Principio de Mínimo Privilegio
Hemos eliminamos el acceso total (ALL PRIVILEGES) del usuario de la web. Ahora, si un hacker entra, no puede borrar tablas (DROP) ni vaciarlas (DELETE). Solo tiene permiso para SELECT e INSERT.
Al ejecutar el comando nos dice que no tenemos los permisos requeridos para esa acción. 
<img width="1082" height="674" alt="Captura de pantalla de 2026-02-23 16-08-44" src="https://github.com/user-attachments/assets/c5356604-e23c-4ece-a1a5-1b9a1bc632a5" />

## Contraseñas Robustas
Otra medida que hemos implementado para la securización de la base de datos ha sido cambiar las contraseñas por defecto (pass123) por credenciales complejas gestionadas mediante variables de entorno.
<img width="778" height="552" alt="Captura de pantalla de 2026-02-23 15-29-48" src="https://github.com/user-attachments/assets/edd94693-a4d5-46b1-a6fa-cd4b50fcf709" />


## Añadir limite de MB para subir fotos
Hemos añadido un limite de 2MB en las fotos para subir archivos a nuestra base de datos. Es decir, los archivos que superen el tamaño de 2MB dará error si el usuario los quiere subir a extragram.
<img width="1308" height="226" alt="Captura de pantalla de 2026-02-23 17-23-36" src="https://github.com/user-attachments/assets/88ff3ffc-b77e-43f2-85fe-13160d53c654" />







