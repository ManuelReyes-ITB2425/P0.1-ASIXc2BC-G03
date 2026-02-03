# Orquestació amb Docker

## Pasos previos
Hacemos la instalación necesaria para poner en funcionamiento Docker. 
```bash
# Actualizar repositorios
sudo apt update

# Instalar Docker
sudo apt install docker.io docker-compose -y

# Activar Docker y dar permisos al usuario
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
```

La estructura que seguiremos y crearemos sera la siguiente:
```bash
extagram-project
│
├── proxy
│
├── src
│   └── html
│       ├── extagram.php
│       └── upload.php
│
├── db
│   └── init
│       └── (scripts SQL)
│
├── static
│   ├── css
│   │   └── style.css
│   └── images
│       └── preview.svg
│
└── uploads
```
## Configuración docker-compose.yml

Ahora, creamos el archivo de configuración de Docker. 
```bash
sudo nano docker-compose.yml
```
***

Este archivo define la orquestación completa de la infraestructura de microservicios. Utiliza la versión **3.8** de la especificación Compose, un estándar moderno compatible con la mayoría de los motores Docker actuales.

## 1. Servicio Proxy Inverso (S1)

```yaml
  s1-proxy:
    image: nginx:alpine           # Imagen ligera basada en Alpine Linux (<10MB)
    container_name: s1_proxy
    ports:
      - "80:80"                   # Expone el puerto 80 del host al 80 del contenedor
    volumes:
      - ./proxy/nginx.conf:/etc/nginx/conf.d/default.conf # Inyecta configuración de balanceo
    depends_on:
      - s2-app                    # Asegura que el backend inicie antes que el proxy
      - s3-app
    networks:
      - extagram-net
```

### Justificación
*   **API Gateway:** Usamos Nginx como Proxy Inverso. Su función no es procesar PHP, sino recibir todo el tráfico de internet y decidir a qué contenedor interno enviarlo (balanceo de carga o contenido estático).
*   **Seguridad y Rendimiento:** El uso de la versión `alpine` reduce drásticamente la superficie de ataque y el consumo de recursos.

---

## 2. Backend de Aplicación (S2, S3, S4)

### Nodos de Lectura (S2 y S3)
```yaml
  s2-app: &php_base              # Usamos una base común para evitar repetir código
    image: php:fpm-alpine        # PHP-FPM es más rápido para alto tráfico
    container_name: s2_app
    volumes:
      - ./src/html:/var/www/html # Montaje 'bind': código en vivo (hot-reload)
    networks:
      - extagram-net
    # Instala el driver MySQLi al arrancar
    command: /bin/sh -c "docker-php-ext-install mysqli && php-fpm"
```

**Justificación:**
*   S2 y S3 son réplicas idénticas destinadas a **lectura** (ej. listar fotos).
*   Esta redundancia habilita el **balanceo de carga** y la **alta disponibilidad** (si S2 cae, S3 sigue respondiendo).
*   Se utiliza `php:fpm-alpine` por rendimiento superior frente al módulo tradicional de Apache.

### Nodo de Escritura/Subidas (S4)
```yaml
  s4-upload:
    <<: *php_base                # Hereda configuración base
    container_name: s4_upload
    volumes:
      - ./uploads:/var/www/html/uploads # Acceso de ESCRITURA al disco físico
```

**Justificación:**
*   Este contenedor está especializado en la subida de archivos.
*   Se monta el volumen `./uploads` para persistir las imágenes en el host.
*   Esta separación permite aplicar políticas de seguridad más estrictas a S2/S3 (que podrían configurarse como *read-only*).

---

## 3. Capa de Datos (S7)
```yaml
  s7-db:
    image: mariadb:10.6          # Versión LTS
    container_name: s7_db
    environment:                 # Credenciales inyectadas
      MARIADB_ROOT_PASSWORD: rootpassword
      MARIADB_DATABASE: extagram_db
    volumes:
      - ./db/init:/docker-entrypoint-initdb.d # Script SQL de inicio 
    networks:
      extagram-net:
        aliases:
          - db.extagram.itb      # Alias DNS interno
```

### Justificación
*   **Motor:** Se elige MariaDB por ser un fork *open-source* totalmente compatible con MySQL.
*   **Networking:** El uso de **aliases** permite que el código PHP conecte a `db.extagram.itb` en lugar de a una IP fija, desacoplando la configuración de red del código fuente.

---

## 4. Servidores de Contenido Estático (S5, S6)
Estos servicios descargan de trabajo a los servidores PHP sirviendo archivos directamente.

```yaml
  s5-images:
    image: nginx:alpine
    volumes:
      - ./uploads:/usr/share/nginx/html/uploads # Acceso de LECTURA a fotos subidas
```

### Justificación
Nginx es mucho más eficiente que PHP sirviendo archivos estáticos (.jpg, .png, .css).
*   **S5 (Images):** Sirve exclusivamente el contenido generado por los usuarios. Comparte el volumen con **S4**, por lo que visualiza al instante lo que S4 sube.
*   **S6 (Static):** Sirve activos fijos del frontend (CSS, JS, Logos) desde `./static`.

---

## 5. Redes

```yaml
networks:
  extagram-net:
    driver: bridge               # Red aislada interna
```

Todos los contenedores se comunican en una red privada `bridge`. Solo el puerto 80 del proxy (S1) está expuesto al exterior. 

---
El codigo completo esta en: [docke-compose.yml](../CONF/docke-compose.yml)