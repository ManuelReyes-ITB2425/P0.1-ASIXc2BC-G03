# Hardening de Estructura (AWS)

[cite_start]Primero realizaremos el Hardening de la estructura AWS esto debido a que nuestro servidor está corriendo en esta tecnología[cite: 53, 54].

## Snapshots de seguridad
[cite_start]Antes que nada, haremos una instantánea del servidor antes de los cambios, por si algo fallara o el servidor no respondiera, para poder tener una copia de backup[cite: 55, 56].

![Imagen de la sección de AWS donde realizaremos la instantánea de la instancia](ruta/a/tu/imagen1.png)
[cite_start]*Imagen de la sección de AWS donde realizaremos la instantánea de la instancia[cite: 57].*

## Configuración de GS (Grupos de seguridad)
[cite_start]Una vez ya tenemos un backup preparado, proseguimos con la configuración de los grupos de seguridad, en la cual dejaremos como regla de entrada los puertos HTTP y HTTPS abiertos para todo el mundo; esto, obviamente, es fundamental porque, si no, nuestro servicio no tendría visibilidad en el exterior[cite: 58, 59, 60]. 

[cite_start]También, por otro lado, dejamos el puerto SSH abierto, pero solo para el mismo servidor, esto para que todos los miembros del grupo puedan entrar a gestionar el servicio[cite: 61].

![Reglas de entrada](ruta/a/tu/imagen2.png)
[cite_start]*Imagen de las reglas de entrada comentadas anteriormente[cite: 62].*

## Habilitar SSM (AWS Systems Manager)
[cite_start]En este punto realizaremos la habilitación del SSM, este servicio nativo de AWS nos permite conectarnos a la máquina servidor sin tener que utilizar el servicio SSH[cite: 63, 64]. [cite_start]Esto nos permitiría poder bloquear el puerto del servicio anteriormente mencionado y así reducir intrusiones no autorizadas[cite: 65].

### Seleccionar el tipo de servicio
[cite_start]Primero tendremos que seleccionar qué tipo de servicio en nuestro servidor tenemos en AWS, en este caso EC2[cite: 66, 67]:

![Selección del servicio](ruta/a/tu/imagen3.png)
[cite_start]*Imagen de la selección del servicio en AWS[cite: 68].*

### Aplicar permisos de seguridad
[cite_start]En este siguiente apartado tendremos que seleccionar qué tipos de permisos vamos a aplicar en la instancia; en nuestro caso es el siguiente: **“AmazonSSMManagedInstanceCore”**[cite: 69, 70, 71].

![Permisos seleccionados](ruta/a/tu/imagen4.png)
[cite_start]*Imagen de los permisos seleccionados para el rol[cite: 72].*

### Detalles adicionales
Para terminar, detallamos información adicional para identificar el rol sencillamente. [cite_start]Hemos puesto el “Nombre del rol” indicando el nombre de nuestro servidor (**Extagram**) y la descripción la dejamos por defecto[cite: 73, 74, 75].

![Detalles adicionales](ruta/a/tu/imagen5.png)
[cite_start]*Imagen del apartado para añadir detalles adicionales[cite: 76].*

### Resolución de Errores
* [cite_start]**Error:** Tras realizar lo anterior, apareció un error indicando que no teníamos autorización suficiente para realizar el ROL[cite: 77, 78].
* **Solución:** Seleccionamos un rol prehecho llamado **“LabInstanceProfile”**. [cite_start]Con esto, logramos conectarnos al servidor sin utilizar el puerto 22 (SSH)[cite: 79, 80, 81].

## Verificación
[cite_start]A continuación, se muestra la conexión mediante SSM (Session Manager) funcionando correctamente[cite: 82, 83].

![Terminal SSM](ruta/a/tu/imagen6.png)
[cite_start]*Imagen de la nueva terminal por SSM en la cual podemos gestionar el servicio[cite: 84, 85].*

---

# Hardening del Sistema Operativo

[cite_start]Tras la estructura AWS, nos focalizaremos en el Hardening del sistema operativo[cite: 86].

## Actualización y Automatización
1. [cite_start]**Actualización de paquetes:** Usamos `apt update` y `apt upgrade`[cite: 87, 88, 89, 90].
2. [cite_start]**Automatización:** Instalamos `unattended-upgrades` y `dpkg-reconfigure` para actualizaciones pasivas sin intervención humana[cite: 91, 92, 93, 94, 95, 96, 97].

## Revisión de Servicios e Inventario de Puertos
[cite_start]Revisamos los servicios activos y desinstalamos aquellos innecesarios (como **Telnet**, sustituyéndolo por SSH)[cite: 98, 99, 104, 105, 106].

| Puerto | Explicación de importancia |
| :--- | :--- |
| **22 (SSH)** | [cite_start]Necesario para gestión alternativa al SSM[cite: 101]. |
| **53 (DNS)** | [cite_start]Vital para la resolución de nombres[cite: 101]. |
| **68 (network)** | [cite_start]Crucial para el funcionamiento de las redes[cite: 101]. |
| **80 (HTTP)** | [cite_start]Fundamental para la visibilidad del servicio web[cite: 101]. |
| **323 (chronyd)** | [cite_start]Necesario para la sincronización de la hora del sistema[cite: 101]. |
| **337707 (container)**| [cite_start]Puerto donde corren los contenedores del servicio web[cite: 101]. |

![Servicios activos](ruta/a/tu/imagen7.png)
[cite_start]*Imagen de los servicios que están corriendo[cite: 103].*

## Fortalecimiento del Kernel
[cite_start]Creamos el archivo `99-hardening.conf` para denegar ataques **ICMP, SYN Flood e IP Forwarding**[cite: 107, 108, 109, 110]. [cite_start]Aplicamos los cambios con el comando `sysctl –system`[cite: 39, 40].

## Configuración de Permisos Críticos
[cite_start]Cambiamos permisos en archivos vitales para la seguridad[cite: 41]:
* [cite_start]**Shadow y Gshadow:** Permisos `600`[cite: 42].
* [cite_start]**Root:** Permisos `700`[cite: 42].

## Hardening del Servidor Web y Usuario
[cite_start]Modificamos el usuario `www-data` para que no pueda acceder al sistema, previniendo que atacantes usen este usuario maliciosamente[cite: 43, 44, 45, 46].

---

# Revisión Final y Herramientas de Auditoría

[cite_start]Instalamos **Lynis** para obtener una puntuación de Hardening[cite: 47].
* [cite_start]**Resultado inicial:** 62/100[cite: 48, 49].

### Mejoras aplicadas post-auditoría:
* [cite_start]**Crontab y Grub:** Cambio de permisos para evitar cambios en el orden de arranque o persistencia de malware[cite: 49, 50].
* [cite_start]**SSH:** Ajustes en `/etc/ssh/sshd_config` según recomendaciones de Lynis[cite: 50].
* [cite_start]**Malware Scanner:** Instalación de **RKHunter**, un escáner que solo consume recursos durante escaneos activos[cite: 51, 52].
