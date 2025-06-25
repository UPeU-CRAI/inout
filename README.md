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

## Instalación

Para desplegar la aplicación sigue este resumen. Si necesitas un paso a paso más detallado consulta el archivo [install.md](install.md).

1. **Instalar dependencias básicas**: Apache o Nginx, PHP 8.1 y las extensiones de MySQL.
2. **Clonar el repositorio** en el directorio deseado del servidor.
3. **Importar la base de datos** ejecutando `DB/inout.sql` sobre tu instancia de MariaDB/MySQL.
4. **Configurar la conexión** editando `functions/dbconn.php` con las credenciales de tu servidor:

    ```php
    <?php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $inout_servername = "IP_DE_TU_SERVIDOR_DB";
    $inout_username   = "Uinoutl";
    $inout_password   = "DbL1n0u72023#$";
    $inout_db         = "inout_bul";

    $koha_servername  = "IP_DE_TU_SERVIDOR_DB";
    $koha_username    = "koha_bul";
    $koha_password    = 'rP"K)|k#TjQEHs8w';
    $koha_db          = "koha_bul";
    ```

5. **Configurar tu servidor web** creando un VirtualHost que apunte al directorio del proyecto y habilitando el módulo `rewrite`.

Tras estos pasos la aplicación quedará lista para acceder desde `http://tu-dominio/login.php`.

### Credenciales de prueba

Si importaste la base de datos incluida encontrarás estos usuarios iniciales:

- **master / superuser**
- **user / 123456**
- **admin / library**

---

## Contribuciones

Las contribuciones son bienvenidas. Para cambios mayores, por favor abre un "issue" primero para discutir lo que te gustaría cambiar.

## Licencia

Este proyecto está bajo la Licencia [MIT](https://choosealicense.com/licenses/mit/).
