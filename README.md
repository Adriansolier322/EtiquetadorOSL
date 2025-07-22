# Guía de instalación del Etiquetador OSL
> [!Warning]  
> Esta guía se ha hecho sobre un sistema basado en debian (ubuntu 24.04.2 LTS), pero tambien es compatible con multiples sistemas incluidos sistemas windows.  

### Instalación 


#### Paso 1: Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```



#### Paso 2: Instalar Apache

```bash
sudo apt install apache2 -y
```

**Verificar que funciona:**

Abre tu navegador y visita:

```
http://localhost
```

Deberías ver la página de bienvenida de Apache (si no funciona prueba cambiando localhost por la ip de la maquina donde lo has instalado).

##### En el caso de usar nginx:

```bash
sudo apt install nginx -y
```

**Verificar que funciona:**

Abre tu navegador y visita:

```
http://localhost
```

Deberías ver la página de bienvenida de nginx (si no funciona prueba cambiando localhost por la ip de la maquina donde lo has instalado).

Ahora debemos de instalar php fpm para usar php con nginx:
```bash
sudo apt install php-fpm
```
Comprobamos que esté corriendo:
```bash
sudo systemctl status php8.3-fpm.service
```
---

#### Paso 3: Instalar MySQL o MariaDB

```bash
sudo apt install mariadb-server -y
```
---

#### Paso 4: Instalar PHP

```bash
sudo apt install php libapache2-mod-php php-mysql -y
```

#### Paso 5: Instalar Python y librerias
```bash
sudo apt install python3 python3-pip
sudo pip install --break-system-packages fillpdf
sudo pip install --break-system-packages pymupdf
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

#### Configuración de Apache

```bash
sudo mkdir /var/www/etiquetador
sudo cp EtiquetadorOSL/content/* /var/www/etiquetador/
sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/etiquetador.conf
sudo nano /etc/apache2/sites-available/etiquetador.conf
```
Dentro de este nuevo archivo de configuración debemos cambiar la linea `DocumentRoot /var/www/html` por `DocumentRoot /var/www/etiquetador`

#### Configuración de nginx(en caso de usarlo)
```bash
sudo mkdir /var/www/etiquetador
sudo cp EtiquetadorOSL/content/* /var/www/etiquetador/
sudo cp /etc/nginx/sites-available/default  /etc/nginx/sites-available/etiquetador
sudo nano /etc/nginx/sites-available/etiquetador

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
GRANT ALL PRIVILEGES ON nombre_DB.* TO 'etiquetador'@'localhost';
FLUSH etiquetador;
USE nombre_DB;
SOURCE /var/www/etiquetador/scripts/template.sql;
EXIT;
```

Una vez hayamos configurado la base de datos debemos introducir los datos de acceso en el archivo configuration.php, y tras esto iniciamos la pagina y reiniciamos Apache con el siguiente comando:
```bash
a2dissite 000-default.conf
a2ensite etiquetador.conf
systemctl restart apache2
```
Con esto ya estaria todo listo.