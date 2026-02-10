# P0.1-ASIXc2BC-G03
***
# Sobre el proyecto
Extagram es una web donde podemos subir imagenes como si fuera una red social, esta diseñada bajo una arquitectura de microservicios orquestados. El objetivo principal de este proyecto es demostrar la implementación de una infraestructura escalable, robusta y automatizada, separando responsabilidades en contenedores independientes.

A diferencia de un despliegue monolítico tradicional, Extagram distribuye su carga en 7 servicios distintos:

Frontend y Enrutamiento: Un proxy inverso (Nginx) que gestiona el tráfico y balancea la carga.

Backend Escalable: Múltiples nodos PHP para procesar la lógica de la aplicación y garantizar alta disponibilidad.

Gestión de Datos: Separación estricta entre almacenamiento de archivos (imágenes) y base de datos relacional (MySQL/MariaDB).


