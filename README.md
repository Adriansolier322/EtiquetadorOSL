
# EtiquetadorOSL

EtiquetadorOSL es una herramienta open source que sirve para etiquetar distintos equipos informáticos, como ordenadores de sobremesa y portátiles.
En la página, se especifica los distintos componentes para después ser metidos en una etiqueta que se puede imprimir.
Asimismo, hay un dashboard para administradores donde se puede ver los distintos ordenadores que hay guarados, así como gestionarlos, y gestionar a los usuarios.

## Instalación

Hay dos formas de instalar el etiquetador. Por un lado, se puede usar Apache(_leyendo el README que se llama [README-Apache.md](https://github.com/Adriansolier322/EtiquetadorOSL/blob/main/README-Apache.md_)) o se puede usar nginx(_viendo el README que se llama [README-nginx.md](https://github.com/Adriansolier322/EtiquetadorOSL/blob/main/README-nginx.md)_)

## Uso
Una vez que entramos en la página, lo primero que hay que hacer es registrarse si no tenemos usuario, y si tenemos usuario iniciar sesión.
Después de esto, se nos abrirá una pestaña para que se introduzca un código 2FA, el cuál se manda al correo electrónico.  Una vez hayamos metido el código, nos aparecerá la siguiente pantalla:

> En la pantalla del etiquetador, se debe de introducir las siguientes características
> * Tipo de placa: BIOS o UEFI
> * Nombre de CPU
> * Tamaño y tipo de la memoria(_DDR2,DDR3,DDR4,DDR5_)
> * Tamaño y tipo de disco duro(_HDD,SSD,NVMe_)
> * Tipo de gráfica(_integrada o externa_) y el nombre.

Luego, si le damos a "Panel administración, nos redirige a un login donde los admins pueden introducir sus credenciales y entrar.
> En la pantalla de admin dashboard, se puede ver una estadística de cuántos ordenadores hay etiquetados,cuántos de cada tipo de componente hay registrado, cuántos son con bluetooth y/o con wifi. Además de modelos guarados.
>Además, podemos ver y gestionar cada tipo de componente, así como el número de serie.
> 
> Asimismo, hay un apartado donde se pueden gestionar a los usuarios que se encuentran registrados en la plataforma, ahí se pueden borrar o añadir usuarios, así como editar el correo, rol y contraseña de éstos.

## Contribuciones

Pull request son bienvenidos. Si se quiere hacer mayores cambios, por favor abra un "issue" para poder discutir qué se gustaría cambiar.

## Licencia

[GPL-3.0](https://www.gnu.org/licenses/gpl-3.0.html)