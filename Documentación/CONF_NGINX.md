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
Aquí tienes el contenido formateado en Markdown, limpio y estructurado con bloques de código, tablas y negritas para mejorar la legibilidad en tu `README.md` o documentación.

***

# Hardening del Proxy Inverso Nginx (s1-proxy)

## Objetivo

El proxy inverso **s1-proxy** es el único punto de entrada a toda la infraestructura desde Internet. Su configuración de seguridad tiene tres objetivos principales:

1.  **Cifrar todo el tráfico** mediante HTTPS/TLS, eliminando la posibilidad de interceptar comunicaciones (ataques Man-in-the-Middle).
2.  **Mitigar ataques volumétricos** (DDoS, fuerza bruta, Slowloris) antes de que lleguen al backend PHP.
3.  **Ocultar la identidad** de la infraestructura interna para dificultar el reconocimiento del atacante.

---

## nginx.conf — Análisis por secciones

### Zona de Memoria (Rate Limiting)

```nginx
limit_req_zone  $binary_remote_addr  zone=mylimit:10m    rate=10r/s;
limit_conn_zone $binary_remote_addr  zone=conn_limit:10m;
```

Se declaran dos zonas de memoria compartida a nivel http (antes de cualquier bloque server) para rastrear el comportamiento por IP:

| Directiva | Función |
| :--- | :--- |
| `limit_req_zone` | Crea un registro de peticiones por IP. Limita a **10 peticiones/segundo** por cliente. Se aplica individualmente en cada location. |
| `limit_conn_zone` | Crea un registro de conexiones simultáneas por IP. Permite bloquear a clientes que abren demasiadas conexiones en paralelo. |
| `$binary_remote_addr` | Se usa la IP en formato binario (4 bytes) en lugar de texto para minimizar el espacio de memoria consumido por la zona. |
| `:10m` | Cada zona reserva **10 MB** de memoria RAM compartida entre los workers de Nginx, suficiente para ~160.000 IPs simultáneas. |

> **Por qué:** Sin estas zonas, un atacante podría saturar el servidor con miles de peticiones por segundo o abrir miles de conexiones a la vez, agotando los recursos del servidor (ataque DoS).

### Upstream (Balanceo de Carga)

```nginx
upstream backend_pool {
    server s2_app:9000;
    server s3_app:9000;
}
```

Define el grupo de servidores PHP-FPM que procesarán las peticiones de la aplicación principal. Nginx distribuye el tráfico entre `s2_app` y `s3_app` en modo **Round Robin** (por defecto), alternando petición a petición.

> **Por qué:** Distribuir la carga entre dos contenedores PHP mejora la disponibilidad (si uno falla, el otro sigue sirviendo) y permite escalar horizontalmente añadiendo más servidores al pool sin cambiar el código de la aplicación.

### Servidor Trampa (Catch-All)

```nginx
server {
    listen 80  default_server;
    listen 443 ssl default_server;
    server_name _;
    return 444;
}
```

Este bloque actúa como **"honeypot de conexión"**: intercepta cualquier petición que llegue a la IP del servidor con un Host desconocido (escáneres automáticos, bots, ataques de enumeración).

> **Por qué:** El código de respuesta **444** es específico de Nginx y no existe en el estándar HTTP. Hace que Nginx cierre la conexión TCP sin enviar ninguna respuesta. Esto confunde a los escáneres automáticos, que no reciben ni siquiera un error 400 o 403 para confirmar que hay un servidor activo. La directiva `server_name _` actúa como comodín que captura cualquier nombre de host no definido en otros bloques server.

### Redirección HTTP → HTTPS

```nginx
server {
    listen 80;
    server_name localhost;
    return 301 https://$host$request_uri;
}
```

Redirige permanentemente todo el tráfico no cifrado del puerto 80 al puerto 443.

> **Por qué:** El código **301 Moved Permanently** indica al navegador y a los motores de búsqueda que el sitio ha migrado definitivamente a HTTPS. Así se garantiza que ningún usuario acceda accidentalmente por HTTP en texto plano. La variable `$request_uri` preserva la ruta y los parámetros de la URL original durante el redireccionamiento.

---

## Servidor HTTPS Principal

### SSL/TLS

```nginx
ssl_protocols             TLSv1.2 TLSv1.3;
ssl_prefer_server_ciphers on;
ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:...';
ssl_session_cache   shared:SSL:10m;
ssl_session_timeout 10m;
ssl_session_tickets off;
```

| Directiva | Motivo |
| :--- | :--- |
| `ssl_protocols TLSv1.2 TLSv1.3` | Se deshabilitan versiones antiguas e inseguras: TLS 1.0 y 1.1 tienen vulnerabilidades conocidas (POODLE, BEAST). |
| `ssl_prefer_server_ciphers on` | El servidor impone su lista de cifrados en lugar de aceptar la propuesta del cliente, evitando que se negocie un cifrado débil. |
| `ssl_ciphers '...'` | Solo se permiten cifrados de alta seguridad basados en **ECDHE** (Elliptic Curve Diffie-Hellman Ephemeral), que garantizan *Perfect Forward Secrecy*: aunque se comprometa la clave privada en el futuro, las sesiones pasadas no se pueden descifrar. |
| `ssl_session_cache shared:SSL:10m` | Almacena en memoria parámetros de sesiones TLS anteriores para evitar el costoso handshake completo en reconexiones. |
| `ssl_session_tickets off` | Desactiva los tickets de sesión porque podrían comprometer la confidencialidad futura si la clave maestra se filtra. |

### Ocultación de información del servidor

```nginx
server_tokens off;
```

Elimina la versión de Nginx de las cabeceras HTTP de respuesta y de las páginas de error. Sin esta directiva, el servidor envía `Server: nginx/1.25.3`, dando pistas sobre vulnerabilidades de versiones específicas.

### Restricción de métodos HTTP

```nginx
if ($request_method !~ ^(GET|HEAD|POST)$) {
    return 405;
}
```

Bloquea métodos HTTP no utilizados por la aplicación (DELETE, PUT, PATCH, OPTIONS, TRACE). El método **TRACE** en particular es utilizado en ataques Cross-Site Tracing (XST) para robar cookies de sesión.

### Límites anti-DDoS / Slowloris

```nginx
client_max_body_size    10M;
client_body_timeout     10s;
client_header_timeout   10s;
keepalive_timeout       15s;
send_timeout            10s;
```

Mitigación del ataque **Slowloris**: esta técnica consiste en abrir muchas conexiones al servidor y enviar las cabeceras HTTP muy lentamente (una letra cada pocos segundos) para mantener las conexiones abiertas indefinidamente y agotar el pool de conexiones del servidor.

| Directiva | Función |
| :--- | :--- |
| `client_max_body_size 10M` | Limita el tamaño del cuerpo de las peticiones. Evita que alguien sature el disco subiendo archivos enormes. Coherente con el límite de la aplicación de subida de imágenes. |
| `client_body_timeout 10s` | Si el cliente tarda más de 10 segundos en enviar el cuerpo de la petición, Nginx cierra la conexión. |
| `client_header_timeout 10s` | Mismo principio para las cabeceras HTTP. |
| `keepalive_timeout 15s` | Cierra conexiones inactivas después de 15 segundos para liberar recursos. |
| `send_timeout 10s` | Si el cliente no acepta los datos de respuesta durante 10 segundos, la conexión se cierra. |

### Límites de buffers (anti buffer-overflow)

```nginx
client_body_buffer_size      16k;
client_header_buffer_size     1k;
large_client_header_buffers  4 8k;
```

Controla cuánta memoria puede ocupar cada petición en los buffers de Nginx. Sin estos límites, un atacante podría enviar cabeceras HTTP extraordinariamente grandes para intentar un desbordamiento de buffer, que podría causar una denegación de servicio o, en versiones antiguas de software, ejecución de código arbitrario.

### Conexiones simultáneas por IP

```nginx
limit_conn conn_limit 20;
```

Aplica la zona `conn_limit` definida al inicio para limitar a un máximo de **20 conexiones simultáneas por IP**. Un usuario normal nunca necesita más de 6-8 conexiones en paralelo (navegadores modernos abren 6 como máximo). Valores superiores suelen indicar un ataque automatizado.

### Ocultación de cabeceras del backend

```nginx
fastcgi_hide_header X-Powered-By;
proxy_hide_header   X-Powered-By;
fastcgi_hide_header Server;
proxy_hide_header   Server;
```

Elimina cabeceras que el backend (PHP-FPM) añade automáticamente y que revelan tecnología interna: `X-Powered-By: PHP/8.2.x`. Con esta información, un atacante podría buscar vulnerabilidades conocidas específicas de esa versión de PHP en bases de datos como CVE Details o Exploit-DB.

### Bloqueo de ficheros sensibles

```nginx
location ~* \.(ht|git|env|svn|bak|log)$ {
    deny all;
    return 404;
}
```

Bloquea el acceso a ficheros de configuración o control de versiones que nunca deberían ser accesibles públicamente: `.htaccess`, `.env` (credenciales), `.git` (código fuente), ficheros de backup (`.bak`) y logs (`.log`). Se devuelve un **404** en lugar de 403 deliberadamente para no confirmar al atacante que el fichero existe.

### Bloqueo de PHP en directorio de uploads

```nginx
location /uploads/ {
    location ~* \.php$ {
        deny all;
        return 403;
    }
}
```

Previene el ataque de **Remote Code Execution vía File Upload**: si un atacante consigue subir un archivo llamado `shell.php` disfrazado de imagen, este bloque impide que Nginx lo procese como código PHP, aunque la extensión coincida.

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


***

## snippets/security-headers.conf — Cabeceras de Seguridad HTTP

Este archivo se incluye (`include`) en cada bloque `location` del servidor HTTPS. Esto es necesario porque en Nginx, si se usa `add_header` dentro de un bloque `location`, este **anula automáticamente** todas las cabeceras definidas en el bloque `server` padre. Centralizar las cabeceras en un snippet y llamarlo en cada location evita este comportamiento no deseado.

| Cabecera | Valor | Protege contra |
| :--- | :--- | :--- |
| `X-Frame-Options` | `SAMEORIGIN` | **Clickjacking**: impide que la web sea incrustada en un `<iframe>` de otro dominio para engañar al usuario para que haga clic en elementos ocultos. |
| `X-XSS-Protection` | `1; mode=block` | **Cross-Site Scripting (XSS)**: activa el filtro XSS del navegador. Si detecta un ataque, bloquea la página en lugar de intentar "limpiar" el código malicioso. |
| `X-Content-Type-Options` | `nosniff` | **MIME-type sniffing**: impide que el navegador adivine el tipo de fichero ignorando el Content-Type. Evita que un `.jpg` malicioso sea ejecutado como JavaScript. |
| `Referrer-Policy` | `no-referrer-when-downgrade` | **Filtración de URLs**: controla qué información de la URL actual se envía al servidor destino al hacer clic en un enlace externo. |
| `Content-Security-Policy` | `default-src 'self'; ...` | **XSS e inyección de contenido**: declara qué orígenes son de confianza para cargar recursos (scripts, estilos, imágenes). Cualquier recurso no listado es bloqueado por el navegador. |
| `Permissions-Policy` | `geolocation=(), ...` | **Abuso de APIs del navegador**: deshabilita explícitamente el acceso a APIs sensibles del dispositivo (cámara, micrófono, geolocalización). Aunque la aplicación no las use, impide que código malicioso inyectado pueda activarlas. |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | **Downgrade attacks**: el navegador recuerda durante 1 año que este sitio solo debe ser accedido por HTTPS, evitando redirecciones a versiones HTTP inseguras. Se omite el flag `preload` por ser un certificado autofirmado. |


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
#Firewall a S1

## 1. Objectiu
Dins dels requeriments de securització per a l'entorn Extagram, s'ha implementat un tallafocs (firewall) a nivell de sistema operatiu host (màquina virtual d'AWS) que s'anteposa al proxy invers principal (`S1`). Afegint una capa de filtratge de xarxa per sota dels Security Groups del Cloud i per sobre de la lògica de contenidors.

## 2. Anàlisi del problema: Docker vs UFW
Es va identificar una vulnerabilitat arquitectònica comuna en entorns conteneritzats: **El bypass d'UFW per part de Docker**.

Per defecte, quan Docker exposa un port (ex: `80:80` a `s1_proxy`), el dimoni de Docker insereix regles de configuració directament a la cadena `PREROUTING` d'`iptables`. Això provoca que el trànsit d'entrada s'enruti cap al contenidor *abans* que el tallafocs del sistema operatiu (UFW) pugui avaluar les seves regles de bloqueig, deixant els ports completament oberts a Internet independentment de l'estat d'UFW.

## 3. Solució Implementada (`ufw-docker`)
Per resoldre aquesta incidència i forçar el compliment de les polítiques de seguretat del sistema operatiu, s'ha implementat la utilitat `ufw-docker`. Aquest script s'encarrega d'interceptar el trànsit modificant la cadena `DOCKER-USER` d'`iptables`, obligant a que qualsevol petició externa sigui inspeccionada primer per les regles explícites d'UFW abans d'arribar als contenidors.

### 3.1 Neteja i Hardening de regles locals
Prèviament a la implementació, s'han eliminat les regles innecessàries del host local i s'ha aplicat una política estricta de denegació per defecte (Deny All).

```bash
# S'assegura l'accés d'administració abans d'aplicar polítiques estrictes
sudo ufw allow 22/tcp

# Es defineix la política per defecte a Drop
sudo ufw default deny incoming
sudo ufw default allow outgoing

# S'eliminen regles redundants del host que puguin entrar en conflicte amb Docker
sudo ufw delete allow 80/tcp
sudo ufw delete allow 443/tcp
sudo ufw delete allow 3000/tcp
sudo ufw delete allow 9090/tcp
```


### 3.2. Aplicació de regles específiques per a contenidors

Un cop sanejada la integració entre el tallafocs i el dimoni de Docker, es van habilitar específicament les rutes cap als contenidors de producció mitjançant la xarxa virtual (`terraformdocker_extagram-net`).

Es va permetre:

* Trànsit HTTP/HTTPS cap al proxy S1 (`s1_proxy`).
* Accessos als panells de monitoratge (`grafana` i `prometheus`) per donar resposta als requeriments de l'Sprint 5.

```bash
# Regles per al trànsit web de l'aplicació Extagram (S1)
sudo ufw-docker allow s1_proxy 80/tcp
sudo ufw-docker allow s1_proxy 443/tcp

# Regles per al monitoratge de la infraestructura
sudo ufw-docker allow grafana 3000/tcp
sudo ufw-docker allow prometheus 9090/tcp
```
