# P0.1-ASIXc2BC-G03
***
# Sobre el proyecto
Extagram es una web donde podemos subir imagenes como si fuera una red social, esta diseñada bajo una arquitectura de microservicios orquestados. El objetivo principal de este proyecto es demostrar la implementación de una infraestructura escalable, robusta y automatizada, separando responsabilidades en contenedores independientes.

A diferencia de un despliegue monolítico tradicional, Extagram distribuye su carga en 7 servicios distintos:

Frontend y Enrutamiento: Un proxy inverso (Nginx) que gestiona el tráfico y balancea la carga.

Backend Escalable: Múltiples nodos PHP para procesar la lógica de la aplicación y garantizar alta disponibilidad.

Gestión de Datos: Separación estricta entre almacenamiento de archivos (imágenes) y base de datos relacional (MySQL/MariaDB).


# Índice del Proyecto

## 📁 CONF
Archivos de configuración, despliegue y código fuente de la aplicación.
* [docker-compose.yml](./CONF/docker-compose.yml)
* [extagram.php](./CONF/extagram.php)
* [main.tf](./CONF/main.tf)
* [nginx.conf](./CONF/nginx.conf)
* [preview.vsg](./CONF/preview.vsg)
* [setup.sh](./CONF/setup.sh)
* [style.css](./CONF/style.css)
* [upload.php](./CONF/upload.php)

## 📚 Documentación
Manuales, guías y detalles técnicos de la infraestructura.
* [CONF_BBDD.md](./Documentación/CONF_BBDD.md)
* [CONF_NGINX.md](./Documentación/CONF_NGINX.md)
* [Hardening Web WAF (S.O).md](./Documentación/Hardening%20Web%20WAF%20(S.O).md)
* [Monitoraje.md](./Documentación/Monitoraje.md)
* [Terraform.md](./Documentación/Terraform.md)
* [arquitectura.md](./Documentación/arquitectura.md)
* [docker.md](./Documentación/docker.md)
* [guia_usuario.md](./Documentación/guia_usuario.md)
* [mantenimiento.md](./Documentación/mantenimiento.md)
* [monitorización.md](./Documentación/monitorización.md)
* [pruebasestres.md](./Documentación/pruebasestres.md)
* [servidores.md](./Documentación/servidores.md)

## 📌 Archivos Principales
* [README.md](./README.md)
* [Sprint Planning.md](./Sprint%20Planning.md)
* [Sprint Review.md](./Sprint%20Review.md)
* [labsuser.pem](./labsuser.pem)
