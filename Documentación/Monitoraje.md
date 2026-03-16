# Configuración de Grafana y Prometheus

## Monitorización del sistema

En este apartado veremos cómo realizar la conexión entre varios servicios:

- Node Exporter
- cAdvisor
- Grafana
- Prometheus

Estos cuatro servicios trabajan conjuntamente para mostrarnos **los recursos en tiempo real que están siendo utilizados por los contenedores de nuestro servidor**.

---

# Configuración en `docker-compose.yml`

En este apartado veremos las configuraciones necesarias para que los servicios encapsulados en contenedores puedan implementarse correctamente dentro del sistema.

---

# Grafana

Grafana es el servicio encargado de **mostrar toda la información mediante una interfaz gráfica**.  
Es la parte más visual de todo el sistema de monitorización.

## Configuración del servicio Grafana

<img width="594" height="267" alt="image" src="https://github.com/user-attachments/assets/7a4fb267-0b4c-44eb-9253-84ef2869b3bd" />

Como se puede observar en la captura, se utilizan varios parámetros de configuración.

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Indica que la imagen utilizada para el servicio será Grafana en su última versión. |
| container_name | Nombre del contenedor (Grafana) para poder identificarlo fácilmente. |
| ports | Se expone el puerto **3000** para acceder al servicio. |
| environment | Se establece la contraseña inicial como **admin**. |
| networks | El contenedor se conecta a la misma red que el resto de servicios. |

---

# cAdvisor

El servicio **cAdvisor** se encarga de vigilar los otros servicios (s1, s2, etc.) para medir el consumo de recursos de cada contenedor.

Toda esta información posteriormente será utilizada por **Grafana para generar las gráficas de monitorización**.

## Configuración del servicio cAdvisor

<img width="838" height="362" alt="image" src="https://github.com/user-attachments/assets/22a2404d-1f5f-4f3d-bf82-b069adab56b5" />

### Explicación de parámetros

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Se utiliza la imagen más reciente de cAdvisor. |
| container_name | Nombre del contenedor: **cadvisor**. |
| ports | Puerto utilizado por el servicio: **8080**. |
| volumes | Se mapean rutas del sistema para poder calcular CPU, disco y otros recursos. |
| networks | Se conecta a la red **extagram-net**, igual que los demás contenedores. |

---

# Node Exporter

El servicio **node-exporter** tiene la función de revisar las características físicas del servidor y métricas del sistema.

Esto incluye:

- Uso de CPU
- Memoria
- Disco
- Tiempo de actividad del sistema

## Configuración del servicio node-exporter

<img width="903" height="389" alt="image" src="https://github.com/user-attachments/assets/6ff3b524-297e-4906-bcf3-052084f4df44" />

### Explicación de parámetros

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Imagen utilizada del servicio en su última versión. |
| container_name | Nombre asignado al contenedor. |
| restart | Evita que el contenedor se reinicie automáticamente salvo que se detenga. |
| volumes | Rutas del sistema que el contenedor podrá analizar. |
| command | Indica qué rutas debe analizar el contenedor. |
| network | Se conecta a la misma red que el resto de servicios. |
| ports | Puerto utilizado: **9100**. |

---

# Prometheus

Prometheus es el servicio encargado de **recoger todos los datos generados por los otros servicios y almacenarlos**.

Posteriormente, Grafana utilizará estos datos para generar las gráficas.

## Configuración del servicio Prometheus

<img width="813" height="265" alt="image" src="https://github.com/user-attachments/assets/87517651-b4d3-4c6a-9ba3-8e7a7548e764" />

### Explicación de parámetros

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Imagen de Prometheus en su última versión. |
| container_name | Nombre del contenedor: **Prometheus**. |
| ports | Puerto expuesto: **9090**. |
| volumes | Permisos y rutas para la configuración del servicio. |
| networks | Se conecta a la misma red que el resto de contenedores. |

---

# Configuraciones adicionales

Además de `docker-compose.yml`, se necesita configurar archivos adicionales para que el sistema funcione correctamente.

---

# Archivo `prometheus.yml`

Este archivo es fundamental para el funcionamiento de Prometheus.

Aquí se especifica:

- Qué servicios debe monitorizar
- Cada cuánto tiempo debe recoger los datos

## Configuración en `prometheus.yml`

<img width="478" height="310" alt="image" src="https://github.com/user-attachments/assets/18adb238-8606-4466-928c-32f79341060b" />

---

# Conexión de Grafana con Prometheus

También es necesario configurar desde la **interfaz gráfica de Grafana** la conexión con Prometheus.

Prometheus será configurado como **fuente de datos (Data Source)** para que Grafana pueda utilizar sus métricas.

<img width="900" height="738" alt="image" src="https://github.com/user-attachments/assets/80d0e39f-9002-4084-b5fa-9b65f69e2e4a" />
<img width="902" height="988" alt="image" src="https://github.com/user-attachments/assets/fbda08ad-a376-445d-90b3-1820495c08d5" />

---

# Resultado final

Después de completar todas las configuraciones, se obtiene un panel de monitorización con las métricas de los servicios del servidor.

<img width="903" height="849" alt="image" src="https://github.com/user-attachments/assets/8478fc2d-6bb4-436a-892e-82ec5362f1a6" />

Las gráficas muestran información como:

- Uso de CPU
- Uso de memoria
- Actividad de contenedores
- Recursos del sistema

![Gráficas finales](images/img_7_1.png)
