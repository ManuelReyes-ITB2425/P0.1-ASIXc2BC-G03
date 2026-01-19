# P0.1-ASIXc2BC-G03

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
