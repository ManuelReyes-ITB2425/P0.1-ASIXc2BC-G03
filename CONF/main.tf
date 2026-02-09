provider "aws" {
  region = "us-east-1"
}

# 1. Búsqueda automática de la AMI de Amazon Linux 2023 
data "aws_ami" "amazon_linux_2023" {
  most_recent = true
  owners      = ["amazon"]

  filter {
    name   = "name"
    values = ["al2023-ami-2023.*-x86_64"]
  }
}

# 2. Grupo de Seguridad (Firewall)
resource "aws_security_group" "extagram_sg" {
  name        = "extagram_security_group"
  description = "Permitir HTTP y SSH"

  # HTTP para ver la web
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # SSH para administración
  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Salida a internet (para descargar docker, git, etc.)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# 3. Instancia EC2
resource "aws_instance" "app_server" {
  # Usamos la AMI encontrada automáticamente arriba
  ami           = data.aws_ami.amazon_linux_2023.id 
  instance_type = "t3.medium" # Recomendado: t2.micro se queda corta para 7 dockers
  key_name      = "tu-clave-ssh-aws" # <--- CAMBIA ESTO POR TU CLAVE REAL

  security_groups = [aws_security_group.extagram_sg.name]

  # Script de inicio
  user_data = file("setup.sh")

  tags = {
    Name = "Extagram-Production"
  }
}

output "public_ip" {
  description = "IP Publica para acceder a Extagram"
  value       = aws_instance.app_server.public_ip
}
