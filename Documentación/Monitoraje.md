# Configuración Grafana y Prometheus

## Monitoraje:

En este apartado veremos cómo realizar la conexión entre varios servicios (Node Exporter, cAdvisor, Grafana y Prometheus); estos 4 servicios realizarán la función de mostrarnos los recursos en vivo que están siendo utilizados en los contenedores de nuestro servidor.

## Configuración en el archivo docker-compose.yml:

En este primer apartado veremos las configuraciones necesarias para que los servicios encapsulados en contenedores puedan implementarse correctamente con el sistema que ya tenemos realizado.

---

## Grafana:

El servicio Grafana tendrá la función de mostrar toda la información mediante una interfaz; es la parte más gráfica y visiblemente atractiva de todos los servicios de monitorización.

### Configuración realizada para el servicio Grafana

Como se puede observar en la captura, tenemos parámetros ya vistos; igualmente, haremos un desglose rápido sobre qué hace cada uno:

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Indica que la imagen que utilizaremos para el servicio será el Grafana en su última versión. |
| container_name | Indicamos que el nombre del contenedor sea Grafana (fácil para poderlo identificar). |
| ports | Señalamos que el puerto 3000 será el que estará ocupando y mediante este nos podremos conectar al servicio. |
| environment | Establecemos que la contraseña sea admin. |
| networks | Indicamos que estará en la misma red que todos nuestros contenedores. |

---

## cAdvisor:

El servicio cAdvisor tendrá la función de vigilar nuestros otros servicios (s1, s2, etc.), todo esto para poder medir cuántos recursos consume cada uno de estos; toda esta información luego será transportada a las gráficas que veremos en el Grafana.

### Configuración realizada para el servicio cAdvisor

Explicación de cada parámetro que hemos configurado para el correcto funcionamiento de este servicio.

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Seleccionamos la imagen más reciente de cAdvisor. |
| container_name | Indicamos que el nombre del contenedor sea cadvisor; esto nos sirve para poder identificarlo fácilmente. |
| ports | Indicamos el puerto que este servicio utilizará; en este caso, el 8080. |
| volumes | Este parámetro es muy importante debido a que le indicamos varias rutas que tiene que mapear y los permisos que utilizará en estas; varias rutas de las introducidas son para calcular la CPU, espacio de disco y demás. |
| networks | Por último, le indicamos que la red que tendrá que utilizar será la misma que todos los demás contenedores (extagram-net). |

---

## node-exporter:

El servicio node-exporter que hemos implementado en nuestro servidor tendrá la función de revisar las características físicas de la máquina y su tiempo de vida.

### Configuración realizada para el servicio node-exporter.

Explicación de cada parámetro configurado para node-exporter:

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Indicamos la imagen que utilizaremos (última versión). |
| container_name | Identificamos el contenedor con un nombre. |
| restart | Indicamos que no se reinicie a no ser que se pare el contenedor. |
| Volumes | Le indicamos qué rutas podrá analizar y con qué permisos podrá ver estas. |
| Command | Sirve para indicarle al contenedor qué rutas mirar. |
| network | Otorgamos al contenedor la misma red que todos los demás para que así estén todos conectados. |
| ports | Indicamos el puerto que utilizará (9100). |

---

## Prometheus:

Por último, el Prometheus, el cual tendrá la función de recoger todos los datos extraídos de los demás servicios y guardarlos para que luego el Grafana los pueda plasmar en sus gráficas.

### Configuración realizada para el servicio Prometheus.

Explicación de cada parámetro configurado para node-exporter:

| Parámetro | Funcionalidad |
|-----------|--------------|
| image | Establecemos que la imagen que utilizamos para el servicio es la del Prometheus en su última versión. |
| container_name | Configuramos el nombre del contenedor a Prometheus (así tenemos el servicio bien identificado). |
| ports | Indicamos que los puertos que utilizaremos serán el 9090. |
| Volumes | Indicamos qué permisos tendrá que utilizar para la configuración del Prometheus. |
| networks | Establecemos el contenedor dentro de la misma red que los demás servicios. |

---

## Configuraciones adicionales:

En este apartado veremos configuraciones adicionales que hemos tenido que realizar para que todo funcione correctamente, aparte de la configuración inicial hecha en el docker-compose.yml.

### prometheus.yml:

Este segundo archivo para la configuración del servicio Prometheus es vital debido a que le señalaremos a qué servicios les tiene que solicitar los datos y con qué frecuencia.

### Configuración realizada para el servicio Prometheus en el archivo prometheus.yml.

---

## Conexión Grafana con Prometheus:

También hemos tenido que conectar vía interfaz gráfica en Grafana el servicio Prometheus, configurándolo como nuestra base de datos de donde recibiremos los datos para las futuras gráficas.

### Imágenes del Prometheus siendo configurado como base de datos por defecto del Grafana.

---

## Resultado:

Como resultado hemos obtenido un panel con todas las gráficas detalladas con los servicios que utilizamos en nuestro servidor:

### Imagen de las gráficas obtenidas acabada la configuración.
