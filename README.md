# P0.1-ASIXc2BC-G03
***

# Diagrama de Red

En este diagrama de red se puede observar cómo hemos escalado el proyecto, así como la información relevante de cada uno de los servidores y sus respectivas características.

<img width="608" height="626" alt="image" src="https://github.com/user-attachments/assets/90715c77-47da-4ea4-95d8-46543a8ad92a" />


# Funcionamiento de Extagram

En este archivo veremos cómo funciona Extagram desde el punto de vista de un cliente para que pueda entender cómo funciona y lo pueda utilizar sin ningún problema.

## ¿Cómo subir fotos?

Si el usuario quiere subir fotos a Extagram tendrá que entrar en la página web y escribir algo de texto relacionado con la imagen que subirá. Esto lo podrá realizar escribiendo en el primer recuadro que se muestra en la web:

<img width="597" height="345" alt="image" src="https://github.com/user-attachments/assets/76e4d81a-a9d0-495d-bde8-5978006e3452" />


Una vez hecho esto deberá seleccionar la imagen deseada; para ello tendrá que darle clic al recuadro enorme con un icono de cámara:

<img width="610" height="370" alt="image" src="https://github.com/user-attachments/assets/ce9268b4-45a9-4565-8311-f3a6c43aeaee" />


Una vez realizado esto se abrirá una ventana donde tendrás que elegir la foto que quieras subir de tu dispositivo:

<img width="607" height="230" alt="image" src="https://github.com/user-attachments/assets/e919ff7e-53d9-4a14-abad-6669bc8c79fa" />


Y una vez hecho esto le daremos al botón azul en el que pone **“Publicar”**.

<img width="596" height="351" alt="image" src="https://github.com/user-attachments/assets/afcf4424-3c39-4f5c-9ce6-f1ca56f8878a" />

Una vez realizados todos los pasos se subirá a la página web la imagen junto con el texto escrito.

<img width="605" height="449" alt="image" src="https://github.com/user-attachments/assets/9ea8a3ec-a9cf-40c4-aae8-0a0f6ba5e60a" />

## ¿Cómo subir solo texto?

Si el usuario quiere subir un post con solo texto, tendrá que realizar lo siguiente:

1.  El usuario tendrá que escribir algo en el primer recuadro de la página web:

    <img width="607" height="348" alt="image" src="https://github.com/user-attachments/assets/e6cc040a-b218-4af9-86d1-6e6ffc86ec13" />


2.  Una vez realizado esto tendrá que realizar la publicación dándole al botón azul en el cual pone **“Publicar”**.

3.  Una vez realizado esto el post cargará al final de la página.
   
   <img width="606" height="111" alt="image" src="https://github.com/user-attachments/assets/4890446f-6f0a-461c-8646-42aaa8b3e328" />


## ¿Cómo puedo borrar una publicación?

Si el usuario desea borrar una publicación realizada, tendrá que contactar con el servicio de soporte de Extagram. Para ello contamos con un correo electrónico solo para soporte a usuarios:

> **ayudaextagram@gmail.com**

# Explicación
En este archivo explicaremos qué herramientas hemos seleccionado para el proyecto y por qué. Expondremos para ello los puntos fuertes y débiles de las opciones disponibles.

## Nube host

**Opciones disponibles:**
*   Isard
*   AWS

### Isard
La primera opción que tuvimos para hacer el proyecto fue Isard. Esto debido a que es una herramienta que hemos utilizado bastante en clase para hostear máquinas (tanto clientes como servidores), aparte de que lo utilizamos en toda la construcción del anterior proyecto que hicimos.

*   **Puntos fuertes:** Este servicio en la nube tiene bastantes puntos fuertes, el más notorio es la facilidad para crear máquinas virtuales y poder administrarlas simplemente, aparte de la comodidad que nos brinda por estar acostumbrados a manipularlo.
*   **En su contraparte:** Isard solo permite una conexión simultánea, lo cual es una gran desventaja debido a que el creador de la máquina tendrá que realizar toda la configuración de esta. Aparte, las máquinas creadas con Isard son menos configurables que con otros servicios en la nube.

### AWS (Amazon Web Service)
Es la segunda opción que se nos presentó para realizar el proyecto. Este servicio en la nube es uno de los más utilizados mundialmente debido a su gran personalización de características en una máquina y su flexibilidad.

*   **Puntos fuertes:** AWS tiene un gran abanico de posibilidades, aparte de que nos permite poder conectarnos todos a una misma máquina con la llave pública que este nos proporciona, lo cual nos viene bastante bien para poder trabajar todos en una misma máquina.
*   **En su contraparte:** Podemos resaltar su gran complejidad, la cual puede dificultar su utilización.

> **Elección:** Al final tomamos la elección de seleccionar **AWS**, todo debido al gran factor de poder trabajar todos los miembros del grupo juntos sin tener ningún problema con el servidor y por el acceso desde fuera de la red a nuestro servidor, lo cual nos facilitará la muestra y verificación del funcionamiento del servidor cuando lo terminemos.

---

## Servidor Web

**Opciones disponibles:**
*   Apache
*   Nginx

### Apache
La primera opción que tuvimos como servicio web fue Apache, el cual hemos utilizado bastante y es bastante similar en cuanto a configuración a su rival Nginx. Apache es muy bueno en tareas de personalización extensas, en mostrar contenido dinámico, en segmentar entornos para hostear y mucho más.

*   **Puntos negativos:** Tenemos que resaltar que no es muy óptimo para mostrar contenido estático en una página web (contenido que vamos a mostrar en nuestra página web) y consume bastantes recursos.

### Nginx
Como segunda opción tenemos a Nginx, servicio que también hemos tocado bastante y es muy similar en términos de configuración básica a Apache. A diferencia de su rival, consume menos recursos del sistema, se puede utilizar como *proxy server* inverso, es más óptimo para mostrar contenido estático y es más fácil de configurar para usuarios sin mucha experiencia.

*   **En su contraparte:** Este servicio tiene soporte limitado a Windows (cosa que no nos importa debido a que nuestra máquina será Linux sin GUI), no tiene soporte dinámico para el contenido y al utilizar módulos de terceros puede utilizar recursos de más.

> **Elección:** Hemos seleccionado **Nginx** como servicio Web debido a que nos parece más óptimo para el tipo de proyecto que tenemos que crear. Esto debido a las funcionalidades que nos aporta que Apache no hace; un ejemplo de esto sería la facilidad para el contenido estático o la posibilidad de configurar el servicio como balanceador de carga, el cual más tarde tendremos que realizar.

---

## Gestor de Base de datos

### MySQL
MySQL es uno de los gestores de base de datos más usados y famosos en el mundo de la informática.
Este es muy parecido a MariaDB y solo tienen diferencias muy concretas, como que MySQL tiene licencia abierta y cerrada, mientras que MariaDB solo tiene licencia abierta, o que MySQL es un poco más lento para procesar determinadas aplicaciones. En general son diferencias que no nos van a afectar en el proyecto.

### MariaDB
Por otro lado, tenemos MariaDB, el cual es muy parecido a MySQL. Este es un poco menos utilizado, pero sigue siendo una fantástica opción.
Las diferencias entre MariaDB y MySQL son contadas como hemos comentado en el apartado de MySQL; lo único destacable es que puede soportar más motores de Bases de datos y su rapidez con ciertas aplicaciones.

> **Elección:** En este caso hemos seleccionado **MariaDB** porque ya estamos bastante habituados a utilizarla.
> Por otro lado, MySQL no era mala opción y realmente elegir uno u otro no cambiaría mucho nuestro resultado final del proyecto debido a que las diferencias, como hemos expuesto, son mínimas.
