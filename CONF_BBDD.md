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

![Paso 1]https://drive.google.com/thumbnail?id=1qSPogGrq1WgRo6BuuBxvOGgz_iaKnQzZ&sz=w1000
