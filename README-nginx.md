# Guía de instalación del Etiquetador OSL
> [!Warning]  
> Esta guía se ha hecho sobre un sistema basado en debian (ubuntu 24.04.2 LTS).  

### Instalación 


#### Paso 1: Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```

#### Paso 2: Instalar nginx:

```bash
sudo apt install nginx -y
```

**Verificar que funciona:**

Abre tu navegador y visita:

```
http://localhost
```

Deberías ver la página de bienvenida de nginx (si no funciona prueba cambiando localhost por la ip de la maquina donde lo has instalado).

#### Paso 3: Instalar MySQL o MariaDB

```bash
sudo apt install mariadb-server -y
```

#### Paso 4: Instalar PHP

```bash
sudo apt install php  php-fpm php-mysql -y
```

#### Paso 5: Instalar Python y librerias
```bash
sudo apt install python3 python3-pip
sudo pip install --break-system-packages fillpdf
sudo pip install --break-system-packages pymupdf
sudo pip install --break-system-packages qrcode
```
### Configuración

Una vez hayas instalado todo vamos a comenzar con la puesta en marcha.

#### Descargar el proyecto
```bash
sudo git clone https://github.com/httpsrim/EtiquetadorOSL.git
```
esto se nos creara un directorio llamado EtiquetadorOSL.
> [!important]  
> En necesario que te encuentres en el directorio en el que has descargado el proyecto  

#### Configuración de Archivos
```bash
sudo mkdir /var/www/etiquetador
sudo cp -r EtiquetadorOSL/content/* /var/www/etiquetador/
sudo cp -r EtiquetadorOSL/includes /var/www/etiquetador/
sudo cp EtiquetadorOSL/login.php EtiquetadorOSL/register.php EtiquetadorOSL/index.html /var/www/etiquetador/
```

#### Configuración de nginx
```bash
sudo cp /etc/nginx/sites-available/default.conf  /etc/nginx/sites-available/etiquetador.conf
sudo nano /etc/nginx/sites-available/etiquetador.conf
```

Dentro de este nuevo archivo de configuración debemos cambiar algunas cosas, el fichero se nos debería de quedar de la siguiente forma:

```
server {
  listen 80 default_server;
  listen [::]:80 default_server;
  root /var/www/etiquetador/EtiquetadorOSL;

  index index.php index.html index.htm index.nginx-debian.html;

  server_name _;

  location / {
	try_files $uri $uri/ =404;
  }

  location /Admin {
    	alias /var/www/etiquetador/Admin; # Ruta real a los archivos de tu app en el subdirectorio
    	try_files $uri $uri/ /Admin/index.php?$query_string;

    	location ~ \.php$ {
        	include snippets/fastcgi-php.conf;
        	fastcgi_pass unix:/run/php/php8.3-fpm.sock; # Asegúrate que esta es la ruta correcta de tu socket PHP 8.3
        	fastcgi_param SCRIPT_FILENAME $request_filename;
    	}
  }

  location ~ \.php$ {
	include snippets/fastcgi-php.conf;
	fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  }
 }
```

#### Configuración de MySQL
```bash
mysql -u root -p
```
o
```bash
sudo mysql
```
Dentro de la consola de MySQL o MariaDB ejecutaremos  
```sql
CREATE DATABASE etiquetador;
CREATE USER 'etiquetador'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON etiquetador.* TO 'etiquetador'@'localhost';
FLUSH etiquetador;
USE etiquetador;
SOURCE /var/www/etiquetador/scripts/template.sql;
EXIT;
```
> [!important]  
> Inicialmente tiene un usuario que tiene acceso a todo, usuario = admin, contraseña = admin123.
> Es importante que una vez que se instale y se acceda a todo, el admin debe de cambiar la contraseña.  

Una vez hayamos configurado la base de datos debemos de iniciar la pagina y reiniciar nginx con el siguiente comando:
```bash
sudo rm /etc/nginx/sites-enabled/default.conf
sudo ln -s /etc/nginx/sites-available/etiquetador.conf /etc/nginx/sites-enabled/
systemctl restart nginx
```
Con esto ya estaria todo listo.
