# Prueba - Task Manager

Segundo test
## Requisitos

Asegúrate de tener el siguiente software instalado en tu sistema:

- **PHP**: 8.0 o superior.
- **Composer**: Para la gestión de dependencias de PHP.
- **Servidor Web**: Apache o Nginx.
- **Base de Datos**: MySQL o MariaDB.

## Instrucciones de Instalación

Sigue estos pasos para configurar el proyecto en tu entorno de desarrollo local.

### 1. Clonar el Repositorio

Abre tu terminal y clona el repositorio en tu máquina local:

```bash
git clone https://github.com/marc601/test2.git
cd PruebaPeibo
```

### 2. Instalar Dependencias

Usa Composer para instalar las dependencias del proyecto.

```bash
composer install
```

### 3. Configuración de la Base de Datos

El proyecto necesita una base de datos para funcionar.

**a. Crear la base de datos:**

Crea una nueva base de datos en tu gestor (por ejemplo, `prueba_peibo`).

**b. Configurar la conexión:**

Deberás configurar los detalles de conexión a la base de datos. Es probable que necesites editar el archivo `/home/marco/www/PruebaPeibo/app/Core/Database.php`.

**c. Importar la estructura de la base de datos:**
ejecuta el script base.sql que esta en la raiz del directorio, este cuenta con un usario default

user : test@example.com

pass: password

### 4. Configuración del Servidor Web

Configura tu servidor web (Apache o Nginx) para que el "Document Root" apunte al directorio `public` del proyecto (si no existe, deberás crearlo y mover el `index.php` principal allí).

Asegúrate de que la reescritura de URL (URL rewriting) esté habilitada para que las rutas amigables (ej. `/user/edit/1`) funcionen correctamente.

**Ejemplo de configuración para Apache (`.htaccess` en el directorio `public`):**

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```
### 5. Pruebas
Los  medotos de consumo se encuentran en el archivo Pruebas.postman_collection.json, para usar con postman.

Las pruebas unitarias se encuentran en el directorio app/Test

### 6. Extensiones php requeridas

**[PHP Modules]**

- calendar
- Core
- ctype
- curl
- date
- dom
- exif
- FFI
- fileinfo
- filter
- ftp
- gettext
- hash
- iconv
- json
- libxml
- mbstring
- mysqli
- mysqlnd
- openssl
- pcntl
- pcre
- PDO
- pdo_mysql
- Phar
- posix
- random
- readline
- Reflection
- session
- shmop
- SimpleXML
- sockets
- sodium
- SPL
- standard
- sysvmsg
- sysvsem
- sysvshm
- tokenizer
- xml
- xmlreader
- xmlwriter
- xsl
- Zend OPcache
- zlib

**[Zend Modules]**

- Zend OPcache
