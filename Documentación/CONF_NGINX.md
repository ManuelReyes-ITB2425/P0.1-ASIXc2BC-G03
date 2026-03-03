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
Arxiu principal nginx.conf
```bash
# =============================================================
# ZONA DE MEMORIA  (nivel http)
# =============================================================

limit_req_zone  $binary_remote_addr  zone=mylimit:10m    rate=10r/s;
limit_conn_zone $binary_remote_addr  zone=conn_limit:10m;


# =============================================================
# UPSTREAM
# =============================================================

upstream backend_pool {
    server s2_app:9000;
    server s3_app:9000;
}


# =============================================================
# SERVIDOR TRAMPA — cierra conexiones con Host desconocido
# =============================================================

server {
    listen 80  default_server;
    listen 443 ssl default_server;
    server_name _;

    ssl_certificate     /etc/nginx/certs/nginx.crt;
    ssl_certificate_key /etc/nginx/certs/nginx.key;

    return 444;
}


# =============================================================
# REDIRECT HTTP → HTTPS
# =============================================================

server {
    listen 80;
    server_name localhost;
    server_tokens off;
    return 301 https://$host$request_uri;
}


# =============================================================
# SERVIDOR HTTPS PRINCIPAL
# =============================================================

server {
    listen 443 ssl;
    server_name localhost;

    # ── SSL / TLS ────────────────────────────────────────────
    ssl_certificate     /etc/nginx/certs/nginx.crt;
    ssl_certificate_key /etc/nginx/certs/nginx.key;

    ssl_protocols             TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256';

    ssl_session_cache   shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;

    # ── Info del servidor ────────────────────────────────────
    server_tokens off;

    # ── Logs ────────────────────────────────────────────────
    error_log  /var/log/nginx/error.log warn;
    access_log /var/log/nginx/access.log;

    # ── Métodos HTTP permitidos ──────────────────────────────
    if ($request_method !~ ^(GET|HEAD|POST)$) {
        return 405;
    }

    # ── Límites de cliente (anti-DDoS / Slowloris) ───────────
    client_max_body_size        10M;
    client_body_timeout         10s;
    client_header_timeout       10s;
    keepalive_timeout           15s;
    send_timeout                10s;

    # ── Límites de buffers (anti buffer-overflow) ────────────
    client_body_buffer_size      16k;
    client_header_buffer_size     1k;
    large_client_header_buffers  4 8k;

    # ── Conexiones simultáneas por IP ────────────────────────
    limit_conn conn_limit 20;

    # ── Ocultar cabeceras del backend ────────────────────────
    fastcgi_hide_header X-Powered-By;
    proxy_hide_header   X-Powered-By;
    fastcgi_hide_header Server;
    proxy_hide_header   Server;

    # ── Bloquear ficheros sensibles ──────────────────────────
    location ~* \.(ht|git|env|svn|bak|log)$ {
        deny all;
        return 404;
    }

    # ── Estáticos ────────────────────────────────────────────
    location ~* \.(css|svg|jpg|jpeg|png|gif|ico)$ {
        proxy_pass http://s6_static;
        include snippets/security-headers.conf;
    }

    # ── Upload ───────────────────────────────────────────────
    location = /upload.php {
        include fastcgi_params;
        fastcgi_pass      s4_upload:9000;
        fastcgi_param     SCRIPT_FILENAME /var/www/html/upload.php;
        fastcgi_param     HTTPS on;

        limit_req zone=mylimit burst=5 nodelay;

        include snippets/security-headers.conf;
    }

    # ── Directorio de uploads (servir, nunca ejecutar PHP) ───
    location /uploads/ {
        proxy_pass http://s6_static;

        location ~* \.php$ {
            deny all;
            return 403;
        }

        include snippets/security-headers.conf;
    }

    # ── Aplicación principal ─────────────────────────────────
    location / {
        include fastcgi_params;
        fastcgi_pass      backend_pool;
        fastcgi_param     SCRIPT_FILENAME /var/www/html/extagram.php;
        fastcgi_param     HTTPS on;

        limit_req zone=mylimit burst=20 nodelay;

        include snippets/security-headers.conf;
    }
}

```
Generación de claves privadas y publicas. 
<img width="732" height="251" alt="image" src="https://github.com/user-attachments/assets/01b4572e-f43d-4cb6-a49b-771b0456420a" />

```bash
add_header X-Frame-Options           "SAMEORIGIN"                                          always;
add_header X-XSS-Protection          "1; mode=block"                                       always;
add_header X-Content-Type-Options    "nosniff"                                             always;
add_header Referrer-Policy           "no-referrer-when-downgrade"                          always;
add_header Content-Security-Policy   "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline';" always;
add_header Permissions-Policy        "geolocation=(), microphone=(), camera=()"            always;
# Sin 'preload' — peligroso en localhost con self-signed
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains"                 always;

```
