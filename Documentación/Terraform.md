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
