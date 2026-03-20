# Prueba de estrés con Grafana k6

## ¿Qué es Grafana k6?

Grafana k6 es una herramienta **open-source de pruebas de rendimiento (performance testing)** utilizada para comprobar cómo se comporta una aplicación cuando muchos usuarios la utilizan al mismo tiempo.

---

## ¿Para qué sirve Grafana k6?

Sirve para **simular usuarios virtuales** que realizan acciones en una aplicación, por ejemplo:

- Hacer peticiones a una API  
- Abrir páginas web  
- Enviar formularios  
- Realizar compras simuladas  

Con esto puedes medir:

- Tiempo de respuesta  
- Número de peticiones por segundo  
- Errores del sistema  
- Consumo de recursos  

Esto ayuda a saber si tu sistema **soportará muchos usuarios reales**.

---

## Prueba de estrés realizada
se han realizado tres pruebas de estrés utilizando la herramienta Grafana k6 ejecutada desde un entorno aislado con Kali Linux. Las pruebas se han diseñado con diferentes perfiles de carga (Load Profiles) para simular comportamientos de usuarios reales y ataques de saturación.

Evalua el comportamiento del sistema en operaciones de gran consumo de recursos (subida de imágenes), donde intervienen el proxy S1 (Nginx), el procesamiento PHP y la escritura en disco en el contenedor específico s4_upload,

### Codigo utilizado:

**Configuración del ataque inicial (Perfil Moderado):**
* Peticiones `HTTP GET` continuas simulando lectura de usuarios humanos con un *sleep* dinámico (entre 1 y 3 segundos).
* **Perfil de carga:** Subida progresiva a **50 usuarios concurrentes (VUs)** en 30 segundos, mantenimiento del tráfico durante 2 minutos y bajada final a 0.
* **Criterios de éxito (Thresholds):** Tasa de fallo inferior al 5% y percentil p95 de respuesta por debajo de los 1.5 segundos.

primer codigo:
````bash
import http from 'k6/http';
import { check, sleep } from 'k6';

// 1. Configuración de la carga extrema
export const options = {
  // AHORRO DE MEMORIA: Le dice a k6 que no guarde el HTML en la RAM local, 
  // permitiendo simular muchos más usuarios sin que tu Kali se cuelgue.
  discardResponseBodies: true,
  
  stages: [
    { duration: '30s', target: 100 }, // Rampa rápida a 100 usuarios
    { duration: '1m', target: 200 },  // Rampa a 200 usuarios (Aquí Nginx empezará a sufrir)
    { duration: '1m', target: 400 },  // El latigazo final: 400 usuarios concurrentes
    { duration: '30s', target: 0 },   // Bajada rápida para ver cómo recupera el servidor
  ],
  
  thresholds: {
    http_req_failed: ['rate<0.05'],     // Menos del 5% de error
    http_req_duration: ['p(95)<1500'],  // El 95% en menos de 1.5s
  },
};

// 2. Comportamiento del Usuario Virtual
export default function () {
  const res = http.get('http://35.169.177.227/extagram.php');

  check(res, {
    'Web responde 200 OK': (r) => r.status === 200,
    'Carga en menos de 2 seg': (r) => r.timings.duration < 2000,
  });

  // Hemos reducido el tiempo de "lectura" para que ataquen más rápido.
  // Ahora recargan la página casi instantáneamente (entre 0.5s y 1s).
  sleep(Math.random() * 0.5 + 0.5); 
}
````

**Configuración del ataque (Perfil Spike Testing):**
* Petición `HTTP GET` agresiva hacia la aplicación PHP con un tiempo de espera muy reducido (entre 0.5 y 1 segundo).
* Uso de `discardResponseBodies: true` para optimizar el consumo de memoria en la máquina atacante.
* **Carga extrema:** Escalado progresivo hasta alcanzar un máximo de **400 usuarios virtuales concurrentes (VUs)**.

segundo codigo:

````bash
mport http from 'k6/http';
import { check, sleep } from 'k6';

// 1. Cargamos el archivo en la memoria de k6 (fuera de la función default)
// El parámetro 'b' indica que es un archivo binario.
const imgFile = open('./imagen_test.jpg', 'b');

export const options = {
  stages: [
    { duration: '30s', target: 20 }, // Subimos a 20 usuarios (subir archivos consume mucho, empezamos suave)
    { duration: '1m', target: 20 },  // Mantenemos 20 usuarios
    { duration: '30s', target: 0 },  // Bajamos a 0
  ],
  thresholds: {
    http_req_failed: ['rate<0.10'], // Permitimos hasta un 10% de error (subir fotos satura rápido)
  },
};

export default function () {
  // 2. Preparamos el formulario (Payload)
  // IMPORTANTE: Los nombres de los campos deben coincidir con lo que espera tu 'upload.php'
  // Según el código de extagram, espera un input text llamado 'post' y un input file llamado 'photo'
  const formData = {
    post: 'Esta es una foto de prueba subida por k6',
    photo: http.file(imgFile, 'imagen_test.jpg', 'image/jpeg'),
  };

  // 3. Hacemos el POST al script que procesa la subida (s4_upload)
  const res = http.post('http://TU_IP_PUBLICA_AWS/upload.php', formData);

  // 4. Comprobamos que el servidor haya respondido bien
  check(res, {
    'Subida correcta (Redirección o 200)': (r) => r.status === 200 || r.status === 302,
  });

  // Esperamos 2 segundos entre subida y subida para no colapsar la red de AWS al instante
  sleep(2); 
}
````


---

## Ataques simulados
**Objetivo:**
Establecer una línea base de rendimiento y evaluar el comportamiento inicial del sistema frente a un nivel de tráfico moderado-alto, simulando 50 usuarios recurrentes interactuando directamente con el contenido dinámico de la aplicación (`extagram.php`).

**Configuración del ataque inicial (Perfil Moderado):**
* Peticiones `HTTP GET` continuas simulando lectura de usuarios humanos con un *sleep* dinámico (entre 1 y 3 segundos).
* **Perfil de carga:** Subida progresiva a **50 usuarios concurrentes (VUs)** en 30 segundos, mantenimiento del tráfico durante 2 minutos y bajada final a 0.
* **Criterios de éxito (Thresholds):** Tasa de fallo inferior al 5% y percentil p95 de respuesta por debajo de los 1.5 segundos.
  
### 1º ataque

<img width="850" height="822" alt="image" src="https://github.com/user-attachments/assets/e66eae08-2a1d-4000-b0ca-64d97fc80bf7" />

---

### 2º ataque

<img width="854" height="649" alt="image" src="https://github.com/user-attachments/assets/8ac8e290-f0a7-4696-8308-9c0276019db6" />

---

**Análisis de los Resultados Iniciales (Primeros y Segundos intentos):**
Se ejecutó la misma prueba en dos iteraciones consecutivas, obteniendo resultados idénticos que demostraron una limitación temprana en la capacidad del servidor:

1. **Rendimiento de Red:** La infraestructura de AWS demostró una excelente capacidad de enrutamiento. Durante los 3 minutos que duró cada iteración, se procesaron más de **6.700 peticiones** (una media de 37 peticiones dinámicas por segundo). El tiempo de respuesta para las conexiones exitosas fue extremadamente bajo: **122.73 ms** (p95), confirmando que el código PHP es muy ligero y la comunicación con MariaDB es rápida.
2. **Saturación del Balanceador:** A pesar de los buenos tiempos de respuesta, las iteraciones arrojaron un **24.91%** y **25.02%** de peticiones fallidas (`http_req_failed`), correspondientes a errores *HTTP 503*.
3. **Conclusión Preliminar:** Estos primeros resultados revelaron de forma temprana que 50 usuarios concurrentes constantes superan el límite de hilos preconfigurado en el *pool* de `php-fpm` (contenedores `s2_app` y `s3_app`). Al enrutar el tráfico mediante el proxy (S1), este se ve obligado a cortar las conexiones cuando los nodos traseros no dan abasto, confirmando la necesidad de ampliar las réplicas antes de abrir el servicio a un público masivo.

### 3º ataque (+400 usuarios)

Simular un escenario de tráfico masivo (equivalente a un ataque DoS de Capa 7 o un pico extremo de viralidad) solicitando de manera agresiva y concurrente el contenido dinámico base (extagram.php). El objetivo era llevar la infraestructura al límite para encontrar el punto exacto de ruptura  de los nodos backend (s2_app y s3_app) y evaluar el comportamiento de la red y el proxy.

<img width="867" height="698" alt="image" src="https://github.com/user-attachments/assets/078e3c3a-349d-4b03-80f9-ddee35d859ce" />


**Análisis de los Resultados obtenidos:**
El informe final generado por *Grafana k6* tras los 3 minutos de ejecución demuestra que se alcanzó y superó drásticamente la capacidad de procesamiento de la arquitectura actual:

1. **Volumen de Tráfico Absorbido:** La red de la instancia en AWS logró enrutar y gestionar un volumen masivo de **70.876 peticiones** (`http_reqs`), manteniendo un promedio sostenido de **392 peticiones por segundo** bajo condiciones de estrés máximo.
2. **Punto de Ruptura:** La prueba cruzó los umbrales de seguridad operativos. Al sostener la carga de 400 usuarios, la infraestructura arrojó una tasa de fallos del **47.45%** (`http_req_failed`), superando ampliamente el límite de tolerancia configurado del 5%. Consecuentemente, solo el **52.54%** de los chequeos confirmaron una respuesta "200 OK". 
3. **Balanceador (Tiempos de Respuesta):** A pesar de que la mitad de las peticiones fallaron (debido al colapso de los *workers* de PHP y la saturación de conexiones en MariaDB), el proxy inverso (S1) no se bloqueó, manteniendo un tiempo de respuesta de **121.77 ms** en el percentil 95 (p95). Esto significa que Nginx continuó funcionando de manera óptima, cortando eficientemente las conexiones sin respuesta (devolviendo errores HTTP 503/504) en lugar de dejar la red congelada.


# Conclusión final
La caída prematura con solo 50 usuarios se explica por un límite de software y no de hardware. La imagen base de php-fpm de Docker utiliza un valor de pm.max_children muy restrictivo por defecto para ahorrar RAM. El proxy Nginx devolvía errores 503 rápidamente (timeouts) porque no tenía contenedores backend libres, a pesar de que la instancia de AWS estaba lejos de alcanzar el 100% de CPU.
