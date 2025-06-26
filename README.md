# Sistema de Gestión de Entradas/Salidas (In/Out Management System) v1.1.1

## Descripción

Este proyecto es un sistema de gestión de entradas y salidas de usuarios, diseñado principalmente para monitorear el flujo de personas en bibliotecas (CRAI) y otras instalaciones. El sistema registra la hora de entrada y salida de cada usuario mediante un número de tarjeta o código de identificación y se integra con la base de datos de Koha para obtener información del usuario.

Esta versión (`v1.1.1`) introduce un sistema de mensajes dinámico para saludar a los usuarios según su rol y género, además de mejoras de seguridad y configuración.

## Características

* Registro de entradas y salidas de usuarios en tiempo real.
* Panel de visualización (`dashboard`) para el registro de actividad.
* Integración con el sistema de gestión de bibliotecas Koha.
* Sistema de roles de usuario (Master, Admin, User).
* Generación de reportes de actividad.
* Noticias y anuncios para los usuarios.
* Mensajes de saludo dinámicos y personalizados según rol y género.
* Avisos automáticos cuando la cuenta de un usuario está expirada.
* Configuración flexible mediante variables de entorno o `config.php`.
* Sanitización de entradas y consultas para mayor seguridad.

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
4. **Configurar la conexión** creando un archivo `config.php` en la raíz del proyecto (o definiendo variables de entorno) con las credenciales de tu servidor. Un ejemplo de `config.php` es:

    ```php
    <?php
    return [
        'inout_servername' => 'IP_DE_TU_SERVIDOR_DB',
        'inout_username'   => 'Uinoutl',
        'inout_password'   => 'DbL1n0u72023#$',
        'inout_db'         => 'inout_bul',

        'koha_servername'  => 'IP_DE_TU_SERVIDOR_DB',
        'koha_username'    => 'koha_bul',
        'koha_password'    => 'rP"K)|k#TjQEHs8w',
        'koha_db'          => 'koha_bul',
    ];
    ```

    Este archivo no debe subirse al repositorio. También puedes establecer las credenciales mediante variables de entorno (`INOUT_DB_HOST`, `INOUT_DB_USER`, etc.).

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
