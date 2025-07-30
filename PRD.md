# Documento de Requisitos del Producto (PRD)

## Objetivo
Este documento resume los requerimientos funcionales clave del **Sistema de Gestión de Entradas/Salidas (In/Out Management System)**. La aplicación permite registrar y controlar el acceso de usuarios a bibliotecas u otras instalaciones, integrándose con Koha para obtener datos de usuarios y mostrando novedades bibliográficas.

## Funciones principales
1. **Autenticación y Roles**
   - Inicio de sesión con verificación de contraseña y soporte para contraseñas SHA1 heredadas.
   - Roles disponibles: `Master`, `Admin` y `User`.
   - El acceso a cada sección se controla mediante códigos de permiso definidos en la base de datos (`access.php`).

2. **Registro de Entradas y Salidas**
   - Escaneo de código o tarjeta del usuario desde el `dashboard` (`dash.php`).
   - Consulta a la base de datos de Koha para validar al usuario y obtener datos de contacto.
   - Almacena fecha, hora, género y estado (`IN` o `OUT`) en la tabla `inout`.
   - Muestra en pantalla mensajes personalizados mediante `MessageHandler` y puede reproducir audio TTS (Google o Azure) según la configuración.

3. **Gestión de Usuarios y Roles**
   - Administración de usuarios desde `user_mgnt.php` y edición de roles en `edit_role.php` y `edit_user.php`.
   - Creación de nuevos roles con códigos de acceso específicos (`usr_process.php`).
   - Opción de activar/desactivar cuentas y modificar información básica.

4. **Reportes y Estadísticas**
   - Visualización de visitas diarias, conteo por género y estadísticas generales (`stats.php`).
   - Generación de reportes filtrados por fecha y horario (`report.php`, `reports.php`).
   - Exportación de datos y copia de seguridad de tablas desde `backup.php`.

5. **Configuración y Personalización**
   - Variables de entorno definidas en `.env` para credenciales de base de datos, proveedor de TTS y parámetros de Koha (`env_loader.php`).
   - Ajustes de banners, mensajes y otros valores en la tabla `setup` gestionados desde `setup.php`.
   - Panel opcional de "Novedades bibliográficas" que obtiene portadas recientes de Koha.

## Flujos principales
1. **Inicio de Sesión**
   1. El usuario accede a `login.php` y envía sus credenciales.
   2. `login_verify.php` valida usuario y contraseña; si el rol y la sede son correctos, se crean variables de sesión y se redirige a `index.php` o `dash.php` según el rol.
   3. Si la autenticación falla, se muestra un mensaje de error.

2. **Registro en el Dashboard**
   1. Un usuario con rol `User` abre `dash.php`.
   2. Escanea o ingresa el código del visitante.
   3. `process/operations/main.php` consulta la base de datos, registra la entrada/salida y devuelve un mensaje (y audio) de confirmación.
   4. El tablero se actualiza para mostrar estadísticas en tiempo real (`stats.php`).

3. **Administración de Usuarios**
   1. Un usuario con rol `Admin` o `Master` ingresa a `user_mgnt.php`.
   2. Desde esta pantalla se pueden crear usuarios, asignar roles y editar información existente (`edit_user.php`).
   3. Para definir los permisos de un rol se utiliza `edit_role.php`, donde se seleccionan los códigos de acceso.

4. **Generación de Reportes**
   1. En `report.php` se elige un rango de fechas y horario.
   2. El sistema consulta las tablas correspondientes y presenta el detalle de entradas/salidas.
   3. Los datos pueden exportarse o imprimirse para respaldo.

## Consideraciones Técnicas
- **Backend:** PHP 8.1 con extensiones de MySQLi y cURL.
- **Base de Datos:** MariaDB/MySQL. Incluye un script de ejemplo en `DB/inout.sql`.
- **Dependencias**: se gestionan mediante Composer (`google/cloud-text-to-speech`, `microsoft/cognitive-services-speech-sdk`, `vlucas/phpdotenv`).
- **Integración con Koha:** consultas directas a la base de datos para obtener información del usuario y carátulas de novedades.
- **Sintesis de voz (TTS):** configurable para usar Google o Azure mediante variables en `.env` y las clases `PersonalizedGreeting` o `AzureSpeech`.

## Alcances y Limitaciones
- El proyecto no incluye por defecto un mecanismo de pruebas automatizadas.
- La instalación requiere cargar las dependencias con Composer y configurar correctamente el archivo `.env`.
- El módulo de voz de Azure utiliza la API REST, por lo que se requiere conexión a internet y una clave de servicio válida.

