# 1. Objetivo del Despliegue e Infraestructura

## 1.1. Propósito General
Migración y despliegue de la aplicación distribuida "Extagram" desde un entorno local hacia **Amazon Web Services (AWS)**.

La arquitectura de microservicios consta de **7 contenedores Docker** (balanceadores Nginx, procesadores PHP y bases de datos MySQL), configurados para ser accesibles públicamente vía internet, asegurando la persistencia de datos y la comunicación entre servicios.

## 1.2. Estrategia de Implementación: Infraestructura como Código (IaC)
Se implementa una estrategia de IaC utilizando **Terraform** bajo un enfoque de **Aprovisionamiento con Bootstrapping**, evitando la configuración manual:

*   **Orquestación de Infraestructura:** Terraform define y provisiona los recursos en la nube (Instancia EC2, Security Groups, Key Pairs).
*   **Configuración Automática:** Mediante scripts *User Data*, la instancia se autoconfigura al inicio: instalación del motor Docker, clonado del repositorio y ejecución de los servicios definidos en `docker-compose`.

## 1.3. Justificación Técnica
La automatización del despliegue responde a los siguientes criterios de ingeniería:

*   **Reproductibilidad:** Permite destruir y recrear el entorno idénticamente en minutos, mitigando errores humanos.
*   **Documentación como Código:** Los archivos de configuración (`main.tf` y `setup.sh`) actúan como la definición técnica real de la infraestructura.
*   **Preparación para Escalabilidad:** Establece la base de código necesaria para la implementación de seguridad avanzada y alta disponibilidad en la fase **P0.2**.

## 1.4. Alcance del Despliegue
El alcance técnico abarca:

*   **Aprovisionamiento:** Instancia EC2 con Amazon Linux 2023.
*   **Red y Seguridad:** Configuración de Security Groups para tráfico HTTP (Puerto 80) y SSH (Puerto 22).
*   **Aplicación:** Despliegue de la pila de contenedores de la arquitectura Extagram.
*   **Persistencia:** Gestión de volúmenes para la base de datos y almacenamiento de archivos multimedia.




## 2. Análisis del Problema de Aprovisionamiento

### 2.1. Descripción de la Incidencia
Durante el despliegue automatizado, se detectó un fallo en la fase de *bootstrapping*. A pesar de que Terraform confirmó la creación exitosa de la infraestructura y la instancia EC2 figuraba en estado `Running` en la consola de AWS, el servicio Extagram no se encontraba operativo.

La inspección de la instancia mediante SSH reveló el siguiente estado:
*   **Ausencia de dependencias:** Docker y Docker Compose no estaban instalados.
*   **Código fuente:** El repositorio de la aplicación no se había clonado.
*   **Estado del sistema:** El sistema operativo estaba funcional, pero el aprovisionamiento de aplicaciones no se había ejecutado.

### 2.2. Diagnóstico Técnico (Causa Raíz)
La revisión de los logs del sistema, específicamente `/var/log/cloud-init-output.log`, evidenció una incompatibilidad crítica entre la definición de la infraestructura y el script de aprovisionamiento:

*   **Definición en Terraform (`main.tf`):** Instancia configurada para desplegar una AMI basada en **Ubuntu Server 22.04 LTS** (`ami-0c7...`).
*   **Script de usuario (`user_data`):** Contenía comandos de gestión de paquetes pertenecientes a la familia RedHat/Amazon Linux (`yum update`, `yum install`).

**Detalle del conflicto**
Ubuntu utiliza `apt` como gestor de paquetes nativo. Al ejecutarse el script de inicio (`setup.sh`), el intérprete falló al invocar la instrucción `yum`, comando inexistente en distribuciones Debian/Ubuntu. Esto generó un error de tipo "command not found", interrumpiendo la ejecución del script de forma inmediata sin reportar el fallo al proceso padre de Terraform.


### 2.3. Solución Implementada
Para resolver la incompatibilidad y asegurar la ejecución correcta del aprovisionamiento, se procedió a estandarizar el entorno utilizando el ecosistema nativo de AWS.

**Acciones realizadas**

*   **Sustitución de la AMI:** Se modificó la configuración en Terraform para retirar la dependencia de Ubuntu. En su lugar, se implementó un bloque `data "aws_ami"` encargado de localizar y seleccionar dinámicamente la última versión estable de **Amazon Linux 2023**.
*   **Validación de compatibilidad:** Se confirmó la validez del script de `user_data` existente. Al utilizar Amazon Linux 2023, los comandos `yum` (alias del gestor `dnf` en esta versión) se ejecutan nativamente, eliminando el conflicto de dependencias.

**Resultado**
La alineación entre el sistema operativo y la lógica de aprovisionamiento permitió completar el proceso de *bootstrapping*. La aplicación Extagram inicia sus servicios automáticamente tras el despliegue de la instancia, sin requerir intervención manual.

