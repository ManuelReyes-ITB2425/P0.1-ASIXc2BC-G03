# Mantenimiento del Sistema

Este documento detalla los procedimientos de diagnóstico, resolución de problemas comunes y tareas de mantenimiento rutinario para el entorno de despliegue.

## 1. Diagnóstico Inicial y Monitoreo de Logs

El primer paso ante cualquier fallo de servicio (ej. Error HTTP 500, tiempos de espera agotados) es la revisión de los logs del orquestador de contenedores.

**Síntoma:** La aplicación devuelve errores internos o no responde.

**Acción:** Consultar los logs en tiempo real de los servicios implicados.

```bash
# Inspeccionar logs de todos los servicios simultáneamente
docker-compose logs -f

# Inspeccionar logs de servicios específicos (ej. backend y base de datos)
docker-compose logs -f s2_app s7_db
```

**Patrones de error a identificar:**
*   **PHP:** `Fatal error` o excepciones no capturadas.
*   **MySQL:** `Access denied for user` o rechazos de conexión.
*   **Nginx:** `Connection refused` o errores de *upstream*.

---

## 2. Problemas de Permisos (Escritura en Volúmenes)

**Síntoma:** Las subidas de archivos fallan. La aplicación muestra el error `Permission denied` o los archivos se guardan con un tamaño de 0 bytes.

**Causa:** El usuario del contenedor encargado de la ejecución (por defecto `www-data` con UID 33) no tiene permisos de escritura sobre el directorio montado en el *host*.

**Solución:**
1. Acceder a la instancia o servidor.
2. Verificar los permisos actuales del directorio afectado:
   ```bash
   ls -l uploads/
   ```
3. Aplicar los permisos adecuados. Se recomienda cambiar el propietario para que coincida con el UID del contenedor:

   ```bash
   # Opción A (Recomendada): Asignar propiedad al usuario www-data (UID 33)
   sudo chown -R 33:33 uploads/

   # Opción B (Fallback): Otorgar permisos globales de lectura/escritura
   sudo chmod -R 777 uploads/
   ```

---

## 3. Errores de Enrutamiento estático (HTTP 404 en Imágenes)

**Síntoma:** El archivo físico existe en el directorio de almacenamiento, pero el navegador devuelve un error `404 Not Found` al intentar renderizarlo.

**Diagnóstico:** Inspeccionar la red mediante las herramientas de desarrollo del navegador. Si la URL es correcta pero el servidor devuelve 404, existe un error en las reglas del proxy inverso.

**Solución:**
1. Revisar la configuración de Nginx (`nginx.conf` o el archivo de *sites-available* correspondiente).
2. Validar los siguientes parámetros:
   *   La regla `location /uploads/` está definida y tiene la prioridad correcta.
   *   La directiva `proxy_pass` apunta al servicio estático correcto (`s6_static`) y al puerto adecuado.
3. Aplicar los cambios recargando el servicio sin interrumpir conexiones activas (*graceful reload*):

   ```bash
   docker exec s1_proxy nginx -s reload
   ```

---

## 4. Conectividad con Base de Datos

**Síntoma:** La aplicación arroja excepciones del tipo `Connection failed: Unknown host` o `Connection refused`.

**Procedimiento de diagnóstico:**
1. Verificar que el contenedor de la base de datos se encuentra en estado `Up`:
   ```bash
   docker ps | grep s7_db
   ```
2. Validar que el *hostname* definido en el archivo de configuración (ej. `db_config.php`) coincida exactamente con el nombre del servicio definido en el `docker-compose.yml` (`s7_db`) o su alias de red.
3. Realizar una prueba de resolución DNS y conectividad desde el contenedor de la aplicación:

   ```bash
   # Acceder a la shell del contenedor backend
   docker exec -it s2_app sh
   
   # Comprobar resolución de red hacia la base de datos
   ping s7_db
   ```

---

## 5. Depuración de Provisionamiento en Entornos Cloud (AWS)

**Síntoma:** La instancia EC2 (o equivalente) inicia correctamente, pero la IP pública no responde al tráfico HTTP/HTTPS.

**Causa probable:** Fallo durante la ejecución del script de inicio (User Data / Cloud-Init), como errores en el gestor de paquetes, fallos de clonación de Git o problemas en la instalación de Docker.

**Acción:** Inspeccionar el log de salida del provisionamiento de la instancia.

```bash
cat /var/log/cloud-init-output.log
```
*Nota: Revisar este archivo permite identificar si el flujo de automatización se interrumpió antes de levantar los contenedores.*

---

## 6. Procedimientos de Respaldo (Backups)

Scripts y comandos estándar para la extracción segura de datos del entorno en producción.

**Respaldo de la Base de Datos (Volcado SQL):**
Se extrae directamente desde el contenedor hacia el *host*.
```bash
docker exec s7_db mysqldump -u root -p[PASSWORD] extagram_db > backup_db_$(date +%F).sql
```
*(Reemplazar `[PASSWORD]` con la contraseña real o utilizar variables de entorno).*

**Respaldo de Archivos Subidos (Assets):**
Compresión del directorio de almacenamiento persistente.
```bash
tar -czvf backup_uploads_$(date +%F).tar.gz ./uploads
```

