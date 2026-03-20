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
se han realizado dos pruebas de estrés utilizando la herramienta Grafana k6 ejecutada desde un entorno aislado con Kali Linux. Las pruebas se han diseñado con diferentes perfiles de carga (Load Profiles) para simular comportamientos de usuarios reales y ataques de saturación.

Evalua el comportamiento del sistema en operaciones de gran consumo de recursos (subida de imágenes), donde intervienen el proxy S1 (Nginx), el procesamiento PHP y la escritura en disco en el contenedor específico s4_upload,

### Codigo utilizado:

primer codi:
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


segon codi:

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

### 1º ataque

<img width="850" height="822" alt="image" src="https://github.com/user-attachments/assets/e66eae08-2a1d-4000-b0ca-64d97fc80bf7" />

---

### 2º ataque

<img width="854" height="649" alt="image" src="https://github.com/user-attachments/assets/8ac8e290-f0a7-4696-8308-9c0276019db6" />

---

### 3º ataque (+400 usuarios)

<img width="867" height="698" alt="image" src="https://github.com/user-attachments/assets/078e3c3a-349d-4b03-80f9-ddee35d859ce" />
