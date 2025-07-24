# Guía de instalación del Etiquetador OSL
> [!Warning]  
> Esta guía se ha hecho sobre un sistema basado en debian (ubuntu 24.04.2 LTS).  

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


#### Paso 3: Instalar MySQL o MariaDB

```bash
sudo apt install mariadb-server -y
```


#### Paso 4: Instalar PHP

```bash
sudo apt install php libapache2-mod-php php-mysql -y
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
##### Configuración de Apache
```bash
sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/etiquetador.conf
sudo nano /etc/apache2/sites-available/etiquetador.conf
```
Dentro de este nuevo archivo de configuración debemos cambiar la linea `DocumentRoot /var/www/html` por `DocumentRoot /var/www/etiquetador`

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

Una vez hayamos configurado la base de datos debemos de iniciar la pagina y reiniciar Apache con el siguiente comando:
```bash
a2dissite 000-default.conf
a2ensite etiquetador.conf
systemctl restart apache2
```
Con esto ya estaria todo listo.
