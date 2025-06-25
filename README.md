# Sistema de Gestión de Entradas/Salidas (In/Out Management System) v1.1.0

## Descripción

Este proyecto es un sistema de gestión de entradas y salidas de usuarios, diseñado principalmente para monitorear el flujo de personas en bibliotecas (CRAI) y otras instalaciones. El sistema registra la hora de entrada y salida de cada usuario mediante un número de tarjeta o código de identificación y se integra con la base de datos de Koha para obtener información del usuario.

Esta versión (`v1.1.0`) incluye correcciones críticas de conexión a bases de datos remotas, resolución de errores de redirección y limpieza general del código para una mayor estabilidad.

## Características

* Registro de entradas y salidas de usuarios en tiempo real.
* Panel de visualización (`dashboard`) para el registro de actividad.
* Integración con el sistema de gestión de bibliotecas Koha.
* Sistema de roles de usuario (Master, Admin, User).
* Generación de reportes de actividad.
* Noticias y anuncios para los usuarios.

## Pila Tecnológica

* **Backend:** PHP 8.1
* **Base de Datos:** MariaDB / MySQL
* **Servidor Web:** Apache2 (compatible con Nginx como proxy inverso)
* **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap Material Design

---

## Instalación en Servidor Ubuntu 22.04 LTS

Esta guía asume que se está instalando en un entorno dedicado y que la base de datos se encuentra en un servidor separado.

### 1. Prerrequisitos

Asegúrate de tener acceso `root` o un usuario con privilegios `sudo` en el servidor de la aplicación.

### 2. Creación de Usuario e Instalación de Dependencias

Se recomienda crear un usuario dedicado para la aplicación y luego instalar el software necesario.

```bash
# Como root, crea el nuevo usuario administrador
adduser inoutadmin
usermod -aG sudo,www-data inoutadmin

# Inicia sesión como inoutadmin para el resto de la instalación
# su - inoutadmin

# Actualiza el sistema e instala los paquetes necesarios
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 git mysql-client php libapache2-mod-php php-mysql php-gd php-mbstring php-xml php-curl -y
```

### 3. Descarga del Proyecto

Clona el repositorio en la estructura de directorios deseada.

```bash
# Crea la ruta y asigna permisos a tu usuario
sudo mkdir -p /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima
sudo chown -R inoutadmin:inoutadmin /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/

# Navega al directorio y clona el proyecto
cd /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima
git clone [https://github.com/UPeU-CRAI/inout.git](https://github.com/UPeU-CRAI/inout.git) inout_bul
```

### 4. Configuración de la Base de Datos

Este script actualizará una base de datos existente o creará las tablas necesarias en una base de datos limpia.

```bash
# Ejecuta este comando para importar la estructura de la base de datos
mysql -h TU_SERVIDOR_DB -u Uinoutl -p inout_bul < /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima/inout_bul/DB/inout.sql
```
*Nota: Si este paso falla por tablas existentes, consulta la guía de instalación detallada para el script de migración.*

### 5. Configuración de la Aplicación

1.  **Configurar Conexión a Base de Datos:**
    Edita el archivo `.../inout_bul/functions/dbconn.php` y asegúrate de que el contenido sea el siguiente, reemplazando con la **IP real** de tu servidor de base de datos.

    ```php
    <?php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Conexión para InOut
    $inout_servername = "IP_DE_TU_SERVIDOR_DB";
    $inout_username   = "Uinoutl";
    $inout_password   = "DbL1n0u72023#$";
    $inout_db         = "inout_bul";

    // Conexión para Koha
    $koha_servername = "IP_DE_TU_SERVIDOR_DB";
    $koha_username   = "koha_bul";
    $koha_password   = 'rP"K)|k#TjQEHs8w';
    $koha_db         = "koha_bul";

    date_default_timezone_set("America/Lima");

    try {
        $conn = new mysqli($inout_servername, $inout_username, $inout_password, $inout_db);
        $conn->set_charset("utf8mb4");
        $koha = new mysqli($koha_servername, $koha_username, $koha_password, $koha_db);
        $koha->set_charset("utf8mb4");
    } catch (mysqli_sql_exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    function sanitize(mysqli $conn, string|null $str): string {
        return $str !== null ? $conn->real_escape_string($str) : '';
    }
    ?>
    ```

2.  **Corregir Redirección en `dash.php`:**
    Edita el archivo `.../inout_bul/dash.php` y cambia la línea `window.location.replace("/inout/dash.php");` por `window.location.replace("dash.php");`.

3.  **Asignar Permisos para Apache:**
    ```bash
    sudo chown -R www-data:www-data /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima/inout_bul
    ```

### 6. Configuración del Servidor Web (Apache)

Crea un Virtual Host para apuntar tu dominio al directorio del proyecto.

1.  Crea el archivo `/etc/apache2/sites-available/inout.conf`:
    ```apache
    <VirtualHost *:80>
        ServerName inout-dev.upeu.edu.pe
        DocumentRoot /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima/inout_bul

        <Directory /u01/vhosts/inout.upeu.edu.pe/httpdocs/koha-inout/fronts/koha-inout-lima/inout_bul>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/inout-error.log
        CustomLog ${APACHE_LOG_DIR}/inout-access.log combined
    </VirtualHost>
    ```
2.  Habilita el sitio:
    ```bash
    sudo a2ensite inout.conf
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    ```

### 7. Uso

La aplicación debería estar disponible en `http://inout-dev.upeu.edu.pe/login.php`. Puedes usar las credenciales por defecto (`admin`/`admin`) si has importado la base de datos por primera vez.

---

## Contribuciones

Las contribuciones son bienvenidas. Para cambios mayores, por favor abre un "issue" primero para discutir lo que te gustaría cambiar.

## Licencia

Este proyecto está bajo la Licencia [MIT](https://choosealicense.com/licenses/mit/).
