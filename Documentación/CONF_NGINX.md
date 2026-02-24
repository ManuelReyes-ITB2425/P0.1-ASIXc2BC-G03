# Configuración NGINX 

> **Resumen:** preparativos y configuraciones para poner en producción el servidor web y extagram. 

## 1. Instalación
Actualizamos repositorios y instalamos nginx.

```bash
#Actualizamos
sudo apt update && sudo apt upgrade -y
#Instalamos
sudo yum install nginx -y
#Activamos y comprobamos que el servicio esta iniciado
sudo systemctl start nginx
sudo systemctl enable nginx 
```
<img width="604" height="265" alt="image" src="https://github.com/user-attachments/assets/8aec2add-618b-4af4-9707-a7ef7730b1b2" />

<img width="604" height="250" alt="image" src="https://github.com/user-attachments/assets/26aa6884-56c4-43c5-9491-1d225eb87826" />

Para poder trabajar con PHP, necesitamos instalarlo y también sus módulos necesarios.

```bash
sudo dnf install php-fpm php-mysqlnd
```
Una vez instalados necesitamos reiniciarlo. 
```bash
sudo systemctl enable --now php-fpm
sudo systemctl restart php-fpm
sudo systemctl restart nginx

```
## 2. Preparar escenario

Creamos la estructura del sitio web, guardaremos toda la configuración en el directorio extagram. Además, las fotos que subamos se guardaran en la carpeta uploads. 

```bash
sudo mkdir -p /var/www/extagram/uploads
```
<img width="496" height="100" alt="image" src="https://github.com/user-attachments/assets/70c0f2db-6e07-4198-b9e0-72788a7aa1d3" />

Crearemos y copiaremos el código en el directorio extagram. extagram.php, upload.php, style.css y preview.svg.

## 3. Configuración de Nginx
Primero, configuraremos nginx.conf, dentro del bloque server, se define el root apuntando a /var/www/extagram. se establece extagram.php como página principal y se configura el bloque location ~ \.php$ para enviar los scripts PHP a PHP-FPM.
```bash
sudo nano  /etc/nginx/nginx.conf
```
Consulta el codigo [nginx.conf](../CONF/nginx.conf)

Ahora, comprobamos sintaxis y recargamos Nginx. 
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 3.1 Permisos 

Ponemos los permisos necesarios para que Nginx no tenga problema para acceder al uploads. 

```bash
cd /var/www/extagram
sudo chown -R nginx:nginx uploads/
sudo chmod -R 775 uploads/
```
<img width="475" height="64" alt="image" src="https://github.com/user-attachments/assets/e7ad8c3a-e2f2-4175-9efd-7b22aec52d94" />


# Configuración balanceo de carga

Previamente configurado los contenedores en Docker, podemos trabajar sin necesidad de instalarlo todo de nuevo, la explicación esta: [Docker](). En el archivo de configuración de Nginx del Proxy (S1), se definieron grupos de servidores (upstream) para permitir la escalabilidad horizontal. Esto permite que si un servidor de aplicación cae, el otro siga respondiendo, y distribuye la carga mediante algoritmo Round-Robin por defecto.

```bash
nano proxy/nginx.conf
```

## Principales Responsabilidades

*   **Balanceo de Carga (Load Balancing):**
    *   Define un grupo (*upstream*) llamado `backend_pool`.
    *   Distribuye las peticiones de la aplicación principal (`extagram.php`) entre dos contenedores réplica: `s2_app` y `s3_app` (puerto `9000`).

*   **Segregación de Servicios:**
    *   **Subidas (Uploads):** Las peticiones a `/upload.php` se enrutan exclusivamente al contenedor `s4_upload`. Esto aísla el proceso de subida de archivos pesados para no bloquear la navegación general.
    *   **Contenido Estático:** Los archivos de recursos (`.css`, `.png`, etc.) y el directorio `/uploads/` son servidos directamente por el contenedor `s6_static`, liberando a los procesadores PHP de esta carga.

*   **Protocolos:**
    *   Utiliza el protocolo **FastCGI** para comunicarse con los contenedores PHP.
    *   Utiliza **HTTP** (`proxy_pass`) para comunicarse con el servidor de estáticos.

```bash
upstream backend_pool {
    server s2_app:9000;
    server s3_app:9000;
}

server {
    listen 80;
    server_name localhost;

    error_log  /var/log/nginx/error.log warn;
    access_log /var/log/nginx/access.log;

    location ~* \.(css|svg|jpg|jpeg|png|gif|ico)$ {
        proxy_pass http://s6_static;
    }

    location = /upload.php {
        include fastcgi_params;
        fastcgi_pass s4_upload:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html/upload.php;
    }

    location / {
        include fastcgi_params;
        fastcgi_pass backend_pool;
        fastcgi_param SCRIPT_FILENAME /var/www/html/extagram.php;
    }

    location /uploads/ {
        proxy_pass http://s6_static;
    }
}

```

Ya podemos levantar el contenedor:
```bash
docker-compose up -d
```
Podemos comprobar, que cada vez que reinciamos los contenedores, hay ocasiones que el servicio se mantiene gracias a que se van pasando el servicio el uno al otro, cumpliendose asi nuestro objetivo.

<img width="512" height="125" alt="image" src="https://github.com/user-attachments/assets/dcdd38e5-9e7c-4457-a885-198cb1bc8b09" />

# Securizando Nginx

```bash
upstream backend_pool {
    server s2_app:9000;
    server s3_app:9000;
}

server {
    listen 80;
    server_name localhost;

    server_tokens off; 
    
    error_log  /var/log/nginx/error.log warn;
    access_log /var/log/nginx/access.log;

    location ~* \.(css|svg|jpg|jpeg|png|gif|ico)$ {
        proxy_pass http://s6_static;
    }

    location = /upload.php {
        include fastcgi_params;
        fastcgi_pass s4_upload:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html/upload.php;
    }

    location / {
        include fastcgi_params;
        fastcgi_pass backend_pool;
        fastcgi_param SCRIPT_FILENAME /var/www/html/extagram.php;
    }

    location /uploads/ {
        proxy_pass http://s6_static;
    }

```

<img width="732" height="251" alt="image" src="https://github.com/user-attachments/assets/01b4572e-f43d-4cb6-a49b-771b0456420a" />

