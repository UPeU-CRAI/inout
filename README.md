# Sistema de Gestión InOut

[![License: CC BY 4.0](https://img.shields.io/badge/License-CC_BY_4.0-lightgrey.svg)](https://creativecommons.org/licenses/by/4.0/)
[![PHP Version](https://img.shields.io/badge/PHP-7.x%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Framework-Bootstrap-purple.svg)](https://getbootstrap.com/)
[![DataTables](https://img.shields.io/badge/Library-DataTables-red.svg)](https://datatables.net/)

Este proyecto es un sistema de gestión web desarrollado en PHP que facilita el registro y control de entradas y salidas. Está diseñado con una interfaz de usuario moderna y responsiva, utilizando Bootstrap y Material Dashboard, e integra DataTables para una gestión eficiente de la información.

## ✨ Características Principales

-   **Gestión de Entradas y Salidas:** Registra y organiza de manera eficiente los movimientos de entrada y salida de recursos o personas.
-   **Panel de Control (Dashboard):** Proporciona una vista general y estadísticas clave a través de un dashboard intuitivo.
-   **Gestión de Usuarios y Roles:** Permite la creación, edición y eliminación de usuarios, asignando diferentes niveles de acceso (roles) para controlar las funcionalidades del sistema.
-   **Reportes Detallados:** Genera reportes basados en los datos registrados para un análisis completo de los movimientos.
-   **Sistema de Autenticación:** Incluye un sistema robusto de inicio de sesión y verificación de usuarios.
-   **Interfaz Responsiva:** Adaptado para funcionar en diferentes dispositivos gracias al uso de Bootstrap y Material Dashboard.
-   **Gestión de Tablas con DataTables:** Funcionalidades avanzadas de búsqueda, paginación y ordenamiento en las tablas de datos.
-   **Funcionalidad de Respaldo (Backup):** Permite realizar copias de seguridad de la base de datos para garantizar la integridad de la información.
-   **Saludos Personalizados con TTS:** Genera mensajes de bienvenida a partir de los datos del usuario usando Google Cloud Text-to-Speech.


## 🚀 Requisitos del Sistema

Para ejecutar este proyecto, necesitarás un entorno de servidor web que soporte PHP y una base de datos MySQL.

-   **Servidor Web:** Apache o Nginx
-   **PHP:** Versión 7.x o superior
-   **Base de Datos:** MySQL
-   **Extensiones de PHP:** `mysqli`, `json`, etc. (las extensiones comunes para aplicaciones PHP)
-   **Composer:** Necesario para instalar dependencias PHP adicionales.
-   **Librería de Google Cloud Text-to-Speech:** Instalable con `composer require google/cloud-text-to-speech`.

## 📦 Instalación

Sigue estos pasos para configurar el proyecto en tu entorno local:

1.  **Clonar el Repositorio:***
    ```bash
    git clone [https://github.com/tu-usuario/inout.git](https://github.com/tu-usuario/inout.git)
    cd inout
    ```
    (Nota: Reemplaza `https://github.com/tu-usuario/inout.git` con la URL real de tu repositorio si es diferente)
2.  **Instalar dependencias PHP:**
    ```bash
    composer install
    ```
    Esto creará el directorio `vendor/` y el archivo `vendor/autoload.php` con la clase `TextToSpeechClient` disponible.


3.  **Configurar la Base de Datos:**
    -   Crea una base de datos MySQL para el proyecto (ej. `inout_db`).
    -   Importa el esquema de la base de datos. Si no hay un archivo `.sql` provisto, la estructura de la base de datos necesitará ser creada manualmente o a través de un script de instalación que el proyecto pueda tener. (Basado en los archivos, parece que la base de datos se maneja a través de `functions/dbconn.php` y `functions/dbfunc.php`, por lo que necesitarías crearla manualmente o el proyecto debe tener una sección `setup.php` para la configuración inicial).
    -   Copia el archivo `.env.example` a `.env` y actualiza las credenciales de conexión. **Este archivo es obligatorio para que la aplicación pueda conectarse a la base de datos:**
        ```bash
        cp .env.example .env
        # Edita el archivo .env con los datos de tu base de datos
        # y establece GOOGLE_APPLICATION_CREDENTIALS con la ruta de tus
        # credenciales de Google Cloud TTS
        # puedes definir el idioma por defecto con TTS_LANGUAGE_CODE
```

4.  **Desplegar en el Servidor Web:**
    -   Copia todos los archivos del proyecto al directorio raíz de tu servidor web (ej. `htdocs` para Apache o `www` para Nginx).
    -   En cada entorno donde despliegues el proyecto debes ejecutar `composer install` para generar el directorio `vendor/`. Si este paso se omite, páginas como `login.php` terminarán con el mensaje **"Vendor autoload not found"**.
    -   Consulta el archivo `DEPLOYMENT.md` para ver un resumen de los pasos necesarios en producción.

5.  **Acceder al Sistema:**
    -   Abre tu navegador y navega a la URL donde desplegaste el proyecto (ej. `http://localhost/inout` o `http://tu_dominio/`).
    -   El sistema te redirigirá a la página de inicio de sesión (`login.php`).
6.  **Configurar Google Cloud Text-to-Speech:**
    -   Crea un proyecto en Google Cloud y genera una clave de servicio (archivo JSON).
    -   Define las variables `GOOGLE_APPLICATION_CREDENTIALS`, `TTS_CREDENTIALS_PATH`, `TTS_LANGUAGE_CODE` y `TTS_VOICE` en tu archivo `.env`:

        ```env
        TTS_CREDENTIALS_PATH=/u01/vhosts/inout.upeu.edu.pe/credentials/inout-tts.json
        TTS_LANGUAGE_CODE=es-ES
        TTS_VOICE=es-ES-Standard-A
        ```
    -   Asegúrate de que `GOOGLE_APPLICATION_CREDENTIALS` apunte al archivo JSON de tu cuenta de servicio de Google.
    -   Como verificación rápida, ejecuta `php tests/tts_test.php` después de definir las variables TTS. Este script creará `tests/tts_test.mp3` si las credenciales y dependencias de Google TTS están instaladas correctamente.
-   Si deseas ver los mensajes de error de PHP durante el desarrollo, establece `DEBUG=1` en tu archivo `.env`.


## ⚙️ Uso

Una vez instalado y configurado, puedes:

-   **Iniciar Sesión:** Utiliza las credenciales predeterminadas (si las hay) o regístrate si el `register.php` está habilitado para ello.
-   **Gestionar Usuarios:** Accede a la sección de administración para agregar, modificar o eliminar usuarios y sus roles.
-   **Registrar Entradas/Salidas:** Utiliza las interfaces designadas para registrar los movimientos.
-   **Generar Reportes:** Consulta los reportes para obtener información detallada sobre los datos.
-   **Probar Google TTS:** Ejecuta `php tests/tts_test.php` para confirmar rapidamente que las credenciales y dependencias de TTS están instaladas.
    ```bash
    php tests/tts_test.php
    ```
    Este comando debe crear `tests/tts_test.mp3` si todo está configurado correctamente.

### Ejecutar pruebas

Las pruebas automatizadas se ejecutan con PHPUnit. Instala primero las dependencias con `composer install` y luego ejecuta:

```bash
vendor/bin/phpunit
```

Esto ejecutará la suite ubicada en el directorio `tests`.

## 📂 Estructura del Proyecto

El proyecto está organizado de la siguiente manera:

-   `assets/`: Contiene archivos estáticos como CSS, JavaScript, fuentes e imágenes.
    -   `DataTables/`: Librerías y estilos de DataTables.
    -   `css/`: Archivos CSS personalizados y de librerías (Bootstrap, Material Dashboard, animate.css, font-awesome, etc.).
    -   `js/`: Archivos JavaScript personalizados y de librerías (jQuery, Bootstrap, Material Dashboard, etc.).
-   `functions/`: Archivos PHP con funciones centrales del sistema.
    -   `access.php`: Manejo de acceso y sesiones.
    -   `dbconn.php`: Configuración de la conexión a la base de datos.
    -   `dbfunc.php`: Funciones para interactuar con la base de datos.
    -   `general.php`: Funciones generales.
    -   `signout.php`: Lógica de cierre de sesión.
-   `process/`: Scripts PHP que manejan la lógica de procesamiento de datos.
    -   `admin/`: Procesos relacionados con la administración (ej. `usr_process.php` para usuarios, `backup.php`).
    -   `operations/`: Procesos relacionados con las operaciones principales (ej. `main.php`, `process.php`, `stats.php`).
-   `template/`: Archivos de plantilla (header, footer, sidebar) para la estructura de las páginas.
-   `CC_lIcense.html`: Archivo de licencia.
-   `backup.php`: Script para la funcionalidad de copia de seguridad.
-   `blank.php`: Página en blanco o de ejemplo.
-   `dash.php`: Archivo principal del dashboard.
-   `edit_role.php`: Edición de roles.
-   `edit_user.php`: Edición de usuarios.
-   `index.php`: Página de inicio (normalmente redirige al login o dashboard).
-   `login.php`: Página de inicio de sesión.
-   `login_verify.php`: Lógica de verificación de credenciales de inicio de sesión.
-   `notice.php`: Posiblemente para mostrar avisos o notificaciones.
-   `register.php`: Página de registro de nuevos usuarios.
-   `report.php`: Generación de un reporte individual o detallado.
-   `reports.php`: Vista de listado de reportes.
-   `setup.php`: Script para la configuración inicial del sistema.
-   `today.php`: Posiblemente para mostrar información del día actual.
-   `updatedb.txt`: Archivo de texto relacionado con actualizaciones de la base de datos.
-   `user.php`: Gestión de un usuario específico.
-   `user_mgnt.php`: Gestión general de usuarios.
-   `README.md`: Este archivo.
-   `.gitignore`: Archivo para ignorar ciertos archivos en el control de versiones.

## 🐛 Solución de Problemas

Si encuentras algún problema durante la instalación o el uso, considera los siguientes puntos:

-   **Errores de Conexión a la Base de Datos:** Asegúrate de que el archivo `.env` contenga las credenciales correctas y que tu servidor MySQL esté en ejecución.
-   **Permisos de Archivos:** Asegúrate de que el servidor web tenga los permisos necesarios para leer los archivos del proyecto y escribir en los directorios si es necesario (ej. para backups).
-   **Errores de PHP:** Revisa los logs de errores de tu servidor web para obtener detalles sobre cualquier problema de PHP.
-   **Credenciales TTS no legibles:** Si el archivo indicado en `TTS_CREDENTIALS_PATH` no existe o no tiene permisos de lectura, la aplicación mostrará una excepción indicando esa variable.
-   **Páginas en Blanco:** Si ves una página en blanco, puede ser un error de PHP no mostrado. Habilita `display_errors` en tu `php.ini` temporalmente para ver los mensajes de error.

## 📜 Licencia

Este proyecto está bajo la Licencia Creative Commons Attribution 4.0 International (CC BY 4.0). Consulta el archivo `CC_lIcense.html` para más detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Si deseas proponer mejoras, reportar errores o contribuir con código, por favor, abre un "issue" o envía un "pull request".
