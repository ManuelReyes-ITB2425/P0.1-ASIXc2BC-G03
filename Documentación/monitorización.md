## 5. Implementación de Infraestructura de Monitorización

Para garantizar la observabilidad del sistema, se ha implementado un stack de monitorización basado en el estándar de la industria para entornos contenerizados: **Prometheus**, **cAdvisor** y **Grafana**. 

El objetivo de esta fase es la recolección pasiva de métricas de uso (CPU, RAM, Red) sin afectar al rendimiento del servicio principal (`s1-proxy` y `backend`).

### 5.1 Arquitectura de Recolección de Datos

Se han integrado tres nuevos servicios en la red privada `extagram-net`, diseñados para interactuar entre sí:

1. **cAdvisor (Container Advisor):** Actúa como un agente a nivel de host. Se monta directamente sobre el demonio de Docker (`/var/lib/docker/`) para extraer métricas precisas del consumo de hardware de cada contenedor individual.
2. **Prometheus:** Es el motor de base de datos de series temporales (Time Series Database). Se ha configurado para realizar técnicas de "scraping" (extracción) contra cAdvisor cada 5 segundos.
3. **Grafana:** Servicio de visualización que consulta la base de datos de Prometheus para generar los *dashboards* (paneles de control).

### 5.2 Configuración del Archivo Maestro (`docker-compose.yml`)

Se ha modificado el archivo orquestador para incluir el nuevo bloque de monitorización. Es destacable el uso de volúmenes de solo lectura (`:ro`) en cAdvisor para garantizar la inmutabilidad del sistema anfitrión (host).

```yaml
  # --- BLOQUE DE MONITORIZACIÓN ---
  cadvisor:
    image: gcr.io/cadvisor/cadvisor:v0.47.0
    container_name: cadvisor
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:rw
      - /sys:/sys:ro
      - /var/lib/docker/:/var/lib/docker:ro
    networks:
      - extagram-net

  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    ports:
      - "9090:9090"
    volumes:
      - ./prometheus/prometheus.yml:/etc/prometheus/prometheus.yml:ro
    depends_on:
      - cadvisor
    networks:
      - extagram-net

  grafana:
    image: grafana/grafana:latest
    container_name: grafana
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
    depends_on:
      - prometheus
    networks:
      - extagram-net

Aquí tienes la sección de configuración de Prometheus y validación, formateada para mantener la coherencia con el resto de tu documentación:

***

## 5.3 Configuración del Motor de Extracción (prometheus.yml)

Para que Prometheus identifique su objetivo de extracción (**cAdvisor**), se ha aprovisionado el siguiente archivo de configuración estático.

Se estableció un `scrape_interval` agresivo de **5 segundos**. Al tratarse de un entorno de pruebas de estrés, una recolección estándar (ej. 1 minuto) no permitiría visualizar los picos de carga momentáneos en los contenedores PHP.

**Archivo:** `./prometheus/prometheus.yml`

```yaml
global:
  scrape_interval: 5s # Captura de alta frecuencia para detectar picos de carga

scrape_configs:
  - job_name: 'cadvisor'
    static_configs:
      # Uso del alias DNS de Docker en lugar de una IP estática
      - targets: ['cadvisor:8080'] 
```


***

## 5.3 Configuración del Motor de Extracción (prometheus.yml)

Para que Prometheus identifique su objetivo de extracción (**cAdvisor**), se ha aprovisionado el siguiente archivo de configuración estático.

Se estableció un `scrape_interval` agresivo de **5 segundos**. Al tratarse de un entorno de pruebas de estrés, una recolección estándar (ej. 1 minuto) no permitiría visualizar los picos de carga momentáneos en los contenedores PHP.

**Archivo:** `./prometheus/prometheus.yml`

```yaml
global:
  scrape_interval: 5s # Captura de alta frecuencia para detectar picos de carga

scrape_configs:
  - job_name: 'cadvisor'
    static_configs:
      # Uso del alias DNS de Docker en lugar de una IP estática
      - targets: ['cadvisor:8080'] 
```

## 5.4 Aprovisionamiento de Puertos (Seguridad Perimetral)

Dado que la infraestructura está alojada en **AWS EC2**, fue necesario modificar la seguridad perimetral para permitir el acceso a los paneles de administración desde el exterior.

Se abrieron los siguientes puertos en el *Security Group* de AWS (y en su defecto, en el firewall UFW del host):

*   **TCP 9090:** Para la validación del estado interno de Prometheus.
*   **TCP 3000:** Para el acceso a la interfaz web de Grafana.

## 5.5 Validación del Despliegue

Tras ejecutar `docker compose up -d`, la infraestructura interna de telemetría quedó activa. Se validó la correcta conexión entre componentes accediendo a:

`http://[IP_PUBLICA]:9090/targets`

Confirmando que el endpoint de cAdvisor reportaba un estado **UP (1/1)**.

Esta configuración deja el entorno completamente preparado para que la **Persona B** vincule los *Data Sources* de Grafana y proceda a ejecutar y registrar las pruebas de estrés mediante *Apache Benchmark*.
