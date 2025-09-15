# Guía de instalación del Etiquetador OSL
> [!Warning]  
> Esta guía se ha hecho sobre un sistema basado en debian (ubuntu 24.04.2 LTS).  

## Instalación
---

## Paso 1: Actualizar el sistema

```bash
sudo apt update
```

---

## Paso 2: Instalar nginx

```bash
sudo apt install nginx -y
```

**Verificar que funciona:**

Abre tu navegador y visita:

```
http://localhost
```

Deberías ver la página de bienvenida de nginx (si no funciona prueba cambiando localhost por la ip de la maquina donde lo has instalado).

---

## Paso 3: Instalar MySQL o MariaDB

```bash
sudo apt install mariadb-server -y
```
---

## Paso 4: Instalar PHP

```bash
sudo apt install php php-fpm php-mysql -y
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

### Configuración de nginx
> [!important]  
> En necesario que te encuentres en el directorio en el que has descargado el proyecto  

```bash
sudo mkdir /var/www/etiquetador
sudo cp EtiquetadorOSL/content/* /var/www/etiquetador/
sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-avaliable/etiquetador.conf
sudo nano /etc/nginx/sites-available/etiquetador.conf
```
Dentro de este nuevo archivo de configuración debemos de escribir lo siguiente:
```
```

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
> Cambia nombre_DB(etiquetador), nombre_usuario(etiquetador) y contraseña(password) por el nombre de base de datos, usuario y contraseña que desees  

```sql
CREATE DATABASE etiquetador;
CREATE USER 'etiquetador'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON etiquetador.* TO 'etiquetador'@'localhost';
FLUSH PRIVILEGES;
USE etiquetador;
SOURCE /var/www/etiquetador/EtiquetadorOSL/scripts/template.sql;
EXIT;
```

Una vez hayamos configurado la base de datos debemos introducir los datos de acceso en el archivo configuration.php, y tras esto iniciamos la pagina y reiniciamos apache con el siguiente comando:
```bash
sudo ln -s /etc/nginx/sites-available/etiquetador.conf /etc/nginx/sites-enabled/etiquetador.conf
systemctl restart nginx
```
Con esto ya estaria todo listo.

> [!important]  
> Debemos de ejecutar el siguiente comando para que la página web funcione correctamente
```bash
sudo chown -R www-data:www-data '/var/www' && sudo chmod -R 660 '/var/www' && sudo find '/var/www' -type d -exec chmod 2770 {} +
```  
>[!important]
> El usuario admin inicial tiene las siguientes credenciales, por favor, es importante cambiarlo una vez esté desplegado y configurado.
> Usuario: admin
> Contraseña: admin123 