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

<img width="867" height="698" alt="image" src="https://github.com/user-attachments/assets/5c8fb2da-dae9-4568-ab0c-1493b2071d66" />

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
