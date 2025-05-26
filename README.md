# Guia de instalación del Etiquetador OSL
> [!Warning]  
> Esta guía se ha hecho sobre un sistema basado en debian (ubuntu 24.04.2 LTS), pero tambien es compatible con multiples sistemas incluidos sistemas windows.  

## Instalación
---

## Paso 1: Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```

---

## Paso 2: Instalar Apache

```bash
sudo apt install apache2 -y
```

**Verificar que funciona:**

Abre tu navegador y visita:

```
http://localhost
```

Deberías ver la página de bienvenida de Apache (si no funciona prueba cambiando localhost por la ip de la maquina donde lo has instalado).

---

## Paso 3: Instalar MySQL o MariaDB

```bash
sudo apt install mariadb-server -y
```
---

## Paso 4: Instalar PHP

```bash
sudo apt install php libapache2-mod-php php-mysql -y
```

## Paso 5: Instalar Python y librerias
```bash
sudo apt install python3 python3-pip
sudo pip install --break-system-packages fillpdf
sudo pip install --break-system-packages pymupdf
```
## Configuración

Una vez hayas instalado todo vamos a comenzar con la puesta en marcha.

### Descargar el proyecto
```bash
sudo git clone https://github.com/Adriansolier322/EtiquetadorOSL.git
```
esto se nos creara un directorio llamado EtiquetadorOSL.

### Configuración de Apache
> [!important]  
> En necesario que te encuentres en el directorio en el que has descargado el proyecto  

```bash
sudo mkdir /var/www/etiquetador
sudo cp EtiquetadorOSL/content/* /var/www/etiquetador/
sudo cp /etc/apache2/sites-avaliable/000-default.conf /etc/apache2/sites-avaliable/etiquetador.conf
sudo nano /etc/apache2/sites-avaliable/etiquetador.conf
```
Dentro de este nuevo archivo de configuración debemos cambiar la linea `DocumentRoot /var/www/html` por `DocumentRoot /var/www/etiquetador`

### Configuración de MySQL
```bash
mysql -u root -p
```
o
```bash
sudo mysql
```
Dentro de la consola de MySQL o MariaDB ejecutaremos  
> [!important]
> Cambia nombre_DB, nombre_usuario y contraseña por el nombre de base de datos, usuario y contraseña que desees  

```sql
CREATE DATABASE nombre_DB;
CREATE USER 'nombre_usuario'@'localhost' IDENTIFIED BY 'contraseña';
GRANT ALL PRIVILEGES ON nombre_DB.* TO 'nombre_usuario'@'localhost';
FLUSH PRIVILEGES;
USE nombre_DB;
SOURCE /var/www/etiquetador/scripts/template.sql;
EXIT;
```

Una vez hayamos configurado la base de datos debemos introducir los datos de acceso en el archivo configuration.php, y tras esto iniciamos la pagina y reiniciamos apache con el siguiente comando:
```bash
a2dissite 000-default.conf
a2ensite etiquetador.conf
systemctl restart apache2
```
Con esto ya estaria todo listo.
