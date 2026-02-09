#!/bin/bash

# --- LOGGING ---
# Redirigir toda la salida a un archivo log para poder depurar si algo falla
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

echo "Iniciando instalación de Extagram..."

# 1. Actualizar sistema e instalar dependencias
dnf update -y
dnf install -y docker git

# 2. Iniciar y habilitar Docker (Para que arranque si reinicias la maquina)
systemctl start docker
systemctl enable docker

# Añadir al usuario por defecto al grupo docker
usermod -a -G docker ec2-user

# 3. Instalar Docker Compose (Versión Plugin para Amazon Linux)
# Amazon Linux 2023 a veces ya incluye el plugin, pero aseguramos la instalación manual si falla
mkdir -p /usr/local/lib/docker/cli-plugins/
curl -SL https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64 -o /usr/local/lib/docker/cli-plugins/docker-compose
chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

# 4. Clonar el Repositorio
cd /home/ec2-user
# Borramos si existe para evitar error de 'already exists'
rm -rf extagram-project 
git clone https://github.com/TU_USUARIO/extagram-project.git

# Ajustar permisos para que el usuario pueda editar archivos si entras por SSH
chown -R ec2-user:ec2-user extagram-project

# 5. Levantar el proyecto
cd extagram-project
# Usamos el comando completo del plugin
docker compose up -d

echo "¡Despliegue de Extagram finalizado!"
