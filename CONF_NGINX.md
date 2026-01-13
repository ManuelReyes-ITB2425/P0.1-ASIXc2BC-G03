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

```bash
server {
        listen       80;
        listen       [::]:80;
        server_name  _;

        # 1. CAMBIO DE RUTA: Apunta a tu carpeta creada
        root         /var/www/extagram;

        # 2. INDICE: Añade extagram.php al principio
        index extagram.php index.html index.htm;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        location / {
            try_files $uri $uri/ =404;
        }

        # 3. CONFIGURACIÓN PHP (Necesaria para AWS/Amazon Linux)
        location ~ \.php$ {
            # Esta línea pasa el script a PHP-FPM
            # EN AWS/AMAZON LINUX, suele ser este socket:
            fastcgi_pass unix:/run/php-fpm/www.sock;

            # Si el de arriba falla, prueba: fastcgi_pass 127.0.0.1:9000;

            fastcgi_index extagram.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        error_page 404 /404.html;
        location = /404.html {
        }

	error_page 500 502 503 504 /50x.html;
        location = /50x.html {
        }
    }
```

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

