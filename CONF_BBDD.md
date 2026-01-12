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
