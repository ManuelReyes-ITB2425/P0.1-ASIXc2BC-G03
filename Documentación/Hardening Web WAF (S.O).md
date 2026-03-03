# Hardening de Estructura (AWS)

Primero realizaremos el Hardening de la estructura AWS esto debido a que nuestro servidor está corriendo en esta tecnología.

## Snapshots de seguridad:
Antes que nada, haremos una instantánea del servidor antes de los cambios, por si algo fallara o el servidor no respondiera, para poder tener una copia de backup. 

<img width="906" height="590" alt="image" src="https://github.com/user-attachments/assets/39b465fa-7b71-4307-9746-f320c42e6cc4" />

*Imagen de la sección de AWS donde realizaremos la instantánea de la instancia.*

## Configuración de GS (Grupos de seguridad):
Una vez ya tenemos un backup preparado, proseguimos con la configuración de los grupos de seguridad, en la cual dejaremos como regla de entrada los puertos HTTP y HTTPS abiertos para todo el mundo; esto, obviamente, es fundamental porque, si no, nuestro servicio no tendría visibilidad en el exterior.
También, por otro lado, dejamos el puerto SSH abierto, pero solo para el mismo servidor, esto para que todos los miembros del grupo puedan entrar a gestionar el servicio. 

<img width="905" height="295" alt="image" src="https://github.com/user-attachments/assets/e3ff1c46-b66d-41d3-b286-4e3e52d208a6" />

*Imagen de las reglas de entrada comentadas anteriormente.*

## Habilitar SSM (AWS Systems Manager):
En este punto realizaremos la habilitación del SSM, este servicio nativo de AWS nos permite conectarnos a la máquina servidor sin tener que utilizar el servicio SSH. Esto nos permitiría poder bloquear el puerto del servicio anteriormente mencionado y así reducir intrusiones no autorizadas.

### Seleccionar el tipo de servicio que tenemos.
Primero tendremos que seleccionar qué tipo de servicio en nuestro servidor tenemos en AWS, en este caso EC2:

<img width="903" height="688" alt="image" src="https://github.com/user-attachments/assets/055b0aba-e593-4bd7-b6fd-6cb551356131" />

*Imagen de la selección del servicio en AWS*

### Aplicar los permisos para la seguridad óptima del servidor.
En este siguiente apartado tendremos que seleccionar qué tipos de permisos vamos a aplicar en la instancia; en nuestro caso es el siguiente: **“AmazonSSMManagedInstanceCore”**

<img width="904" height="228" alt="image" src="https://github.com/user-attachments/assets/28b756f5-2673-4b2b-9735-a3e625ddc084" />

*Imagen de los permisos seleccionados para el rol*

### Detalles adicionales:
Para terminar, tenemos que detallar un poco de información adicional que nunca está mal introducirla para poder identificar el rol sencillamente. Como se podrá ver en la siguiente captura, hemos puesto el “Nombre del rol” indicando el nombre de nuestro servidor (**Extagram**) y la descripción la dejamos por defecto.

<img width="742" height="214" alt="image" src="https://github.com/user-attachments/assets/0694ef70-581e-4060-9dab-971626165726" />

*Imagen del apartado para añadir detalles adicionales.*

### Error y Solución:
**Error:** Una vez que realizamos todo lo anterior, nos salió el siguiente error en el cual AWS nos decía que no teníamos autorización suficiente para realizar el ROL.

<img width="902" height="94" alt="image" src="https://github.com/user-attachments/assets/85bc523b-f8c7-4185-bd91-7f413effed01" />

*Imagen del error que nos dio tras realizar todo el proceso.*

**La solución:** Al ver que no podíamos crear un rol personalizado, fuimos a por la alternativa, que era seleccionar un rol ya prehecho y aplicarlo en nuestro servidor. Para esto seleccionamos el rol **“LabInstanceProfile”** y, una vez hecho esto, logramos obtener lo que tanto buscamos, poder conectarnos a nuestro servidor sin tener que utilizar el puerto 22 (SSH).

<img width="906" height="235" alt="image" src="https://github.com/user-attachments/assets/c1a8974f-92d7-4836-bc71-66acc09b3b9a" />

*Imagen del perfil seleccionado para nuestro servidor.*

## Verificación:
En las siguientes capturas veremos capturas verificativas donde se puede ver que nos podemos conectar mediante SSM (Session Manager) y que todo funciona correctamente.

<img width="904" height="522" alt="image" src="https://github.com/user-attachments/assets/9bbbfb11-4974-4437-b27d-70179fdb9b94" />

*Ventana de conexión SSM habilitada.*

Como se puede ver en esta otra captura, nos podemos conectar a nuestra máquina y ejecutar comandos sin ningún problema.

<img width="906" height="440" alt="image" src="https://github.com/user-attachments/assets/27718f86-88e1-4262-96c4-891aa2d1d0f5" />

*Imagen la nueva terminal por SSM en la cual podemos gestionar el servicio sin problema.*

---

# Hardening del sistema operativo

Una vez que ya tenemos hecho el Hardening de la estructura AWS seguimos con el Hardening del sistema operativo, el más importante y en el que nos focalizaremos primariamente.

## Actualización de paquetes:
Una gran parte de la seguridad de los servicios se basa en tenerlos actualizados a sus últimas versiones; por eso nuestro primer punto en el hardening del sistema será actualizar paquetes.

<img width="900" height="269" alt="image" src="https://github.com/user-attachments/assets/7aedf904-72b6-4499-b5f7-2f782acedf3c" />

*Utilización de los comandos apt update y apt upgrade para actualizar paquetes.*

## Automatización de actualizaciones:
Actualizar los paquetes puede ser un proceso tedioso por el tiempo que este puede consumir; para aligerar esto, instalaremos **unattended-upgrades**, herramienta que nos permitirá que el sistema se actualice pasivamente sin necesidad de intervención humana.

<img width="780" height="217" alt="image" src="https://github.com/user-attachments/assets/06728179-fc62-40e9-93ff-cde9f486361a" />

*Instalación de la herramienta unattended-upgrades*

Una vez instalada la herramienta, tendremos que instalar otra herramienta, la cual tiene el nombre de **dpkg-reconfigure**. Es muy importante instalar las 2 herramientas debido a que esta última se encarga de realizar la automatización de las actualizaciones pasivas de la herramienta anterior.

<img width="621" height="76" alt="image" src="https://github.com/user-attachments/assets/91d550c0-48ec-4015-8389-7733b4930f1c" />

*Instalación de la herramienta dpkg-reconfigure*

## Revisión de servicios instalados y corriendo en el servidor:
El siguiente punto, una vez actualizado el sistema, será revisar qué servicios tenemos en nuestro servidor y desinstalar los servicios que no vamos a utilizar nunca porque no nos interesa o porque no son útiles actualmente.

Primero veremos los servicios que tenemos corriendo en nuestro servidor; como se podrá ver en la imagen adjuntada, tenemos varios servicios activos. Todos estos servicios son necesarios para el correcto funcionamiento del servidor:

| Puerto | Explicación de por qué es importante que esté activo. |
| :--- | :--- |
| **22 (SSH)** | Lo dejaremos abierto debido a que, sin él, solo podría gestionar el servidor el usuario mediante el SSM. |
| **53 (DNS)** | Vital para la resolución de nombres. |
| **68 (network)** | Crucial para que las redes funcionen en nuestra máquina. |
| **80 (HTTP)** | Fundamental para que nuestro servidor sea visible en internet. |
| **323 (chronyd)** | Necesario para que la hora del sistema sea correcta. |
| **337707 (container)** | Donde corren los contenedores que alojan nuestro servicio web. |

<img width="909" height="210" alt="image" src="https://github.com/user-attachments/assets/6a553b24-7bdf-46fd-8d9e-562fefd57986" />

*Imagen de los servicios que están corriendo.*

Por otro lado, eliminaremos servicios que no utilizaremos nunca; en este caso, eliminaremos **Telnet** debido a que SSH es una mejor alternativa.

<img width="675" height="270" alt="image" src="https://github.com/user-attachments/assets/7f356878-710c-459d-86a3-282706269d28" />

*Desinstalación del servicio telnet con un purge*

## Fortalecimiento del Kernel:
Para seguir con el Hardening del sistema operativo, realizaremos un fortalecimiento del Kernel. Para ello hemos creado el archivo `99-hardening.conf` y aplicaremos parámetros para denegar ciertos tipos de ataques como **ICMP, SYN Flood e IP Forwarding**.

<img width="905" height="319" alt="image" src="https://github.com/user-attachments/assets/4afaac9d-57ce-4153-b51a-5fd5dd955eb8" />

*Imagen de los parámetros introducidos en el archivo.*

Aplicamos los cambios con el comando: `sysctl –system`

<img width="665" height="276" alt="image" src="https://github.com/user-attachments/assets/6ff3b21a-be9b-40e9-ae91-fe894d3ab695" />

*Verificación de la aplicación de los cambios.*

## Configuración de permisos en archivos vitales:
Cambiamos los permisos en 3 archivos muy importantes: **shadow, gshadow y root**. Aplicaremos permisos **600** en shadow y gshadow, y **700** en el directorio root.

<img width="376" height="119" alt="image" src="https://github.com/user-attachments/assets/e3243f59-58ff-4f15-b79b-0bc5455cbbdf" />

*Cambio de permisos en los archivos mencionados.*

## Hardening para el servidor web:
Realizamos cambios para que el usuario que corre la página web (**www-data**) no pueda acceder al sistema, previniendo que atacantes utilicen este usuario de forma maliciosa.

<img width="552" height="73" alt="image" src="https://github.com/user-attachments/assets/8c562ec9-e9b4-4618-b2ec-5a4ce5a0db74" />

*Comando utilizado para denegar el acceso del usuario web.*

---

# Revisión del hardening

Instalamos la herramienta **Lynis** para obtener una puntuación de Hardening y detectar posibles mejoras.

<img width="635" height="159" alt="image" src="https://github.com/user-attachments/assets/a386c968-08d3-4109-a935-fe95bdedb9bc" />


**Resultado:** El primer resultado fue un **62 sobre 100**, una valoración sólida que decidimos mejorar.

<img width="823" height="558" alt="image" src="https://github.com/user-attachments/assets/a172c38b-1d77-4784-8596-979542d9e4b8" />

*Imagen del resultado obtenido al ejecutar Lynis.*

## Aplicando mejoras de Hardening:
Basándonos en las recomendaciones de Lynis, realizamos los siguientes cambios:

### Cambio de permisos en Crontab y Grub:
Cambiamos los permisos de archivos de arranque y automatización para evitar cambios en el orden de arranque o persistencia de malware.

<img width="909" height="101" alt="image" src="https://github.com/user-attachments/assets/84c924d6-9ccd-4219-b5ac-d7c7079c431e" />

*Imagen de los cambios de permisos realizados.*

### Cambios de parámetros del servicio SSH:
Modificamos varios puntos recomendados en el archivo `/etc/ssh/sshd_config`.

<img width="649" height="688" alt="image" src="https://github.com/user-attachments/assets/b6f2920c-1638-451d-ba5d-1a2eb454684b" />

*Imagen de los cambios recomendados por Lynis.*

### Instalación de un Malware scanner:
Instalamos **RKHunter** como escáner de malware óptimo, ya que solo consume recursos cuando realizamos un escaneo activo.

<img width="906" height="420" alt="image" src="https://github.com/user-attachments/assets/5c30174d-dc2f-4098-847d-fef602b86adc" />

*Imagen verificativa de la instalación de RKHunter.*
