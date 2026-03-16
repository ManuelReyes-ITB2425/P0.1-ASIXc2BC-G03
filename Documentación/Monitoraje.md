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

![Configuración Grafana](images/img_1_1.png)

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

![Configuración cAdvisor](images/img_2_1.png)

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

![Configuración Node Exporter](images/img_3_1.png)

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

![Configuración Prometheus](images/img_4_1.png)

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

![Configuración prometheus.yml](images/img_4_2.png)

---

# Conexión de Grafana con Prometheus

También es necesario configurar desde la **interfaz gráfica de Grafana** la conexión con Prometheus.

Prometheus será configurado como **fuente de datos (Data Source)** para que Grafana pueda utilizar sus métricas.

![Configuración datasource Prometheus](images/img_5_1.png)

---

# Resultado final

Después de completar todas las configuraciones, se obtiene un panel de monitorización con las métricas de los servicios del servidor.

![Dashboard Grafana](images/img_6_1.png)

Las gráficas muestran información como:

- Uso de CPU
- Uso de memoria
- Actividad de contenedores
- Recursos del sistema

---

# Visualización final de métricas

![Gráficas finales](images/img_7_1.png)
