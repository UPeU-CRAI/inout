# Sistema de Gestión de Entradas/Salidas (In/Out Management System) v1.3.0

## Descripción

Este proyecto es un sistema de gestión de entradas y salidas de usuarios, diseñado principalmente para monitorear el flujo de personas en bibliotecas (CRAI) y otras instalaciones. El sistema registra la hora de entrada y salida de cada usuario mediante un número de tarjeta o código de identificación y se integra con la base de datos de Koha para obtener información del usuario.

Esta versión (`v1.3.0`) incorpora la síntesis de voz de Google Cloud para generar saludos audibles y refina el manejo de mensajes tanto en pantalla como por voz. Además mantiene la configuración mediante el archivo `.env` y las mejoras de seguridad previas.

## Características

* Registro de entradas y salidas de usuarios en tiempo real.
* Panel de visualización (`dashboard`) para el registro de actividad.
* Integración con el sistema de gestión de bibliotecas Koha.
* Sistema de roles de usuario (Master, Admin, User).
* Generación de reportes de actividad.
* Noticias y anuncios para los usuarios.
* Mensajes de saludo dinámicos y personalizados según rol y género.
* Avisos automáticos cuando la cuenta de un usuario está expirada.
* Síntesis de voz con Google Cloud Text-to-Speech para mensajes audibles.
* Configuración flexible mediante variables de entorno.
* Consultas preparadas y sanitización de entradas para mayor seguridad.
* El campo de escaneo mantiene el foco automáticamente en el dashboard.

## Pila Tecnológica

* **Backend:** PHP 8.1
* **Base de Datos:** MariaDB / MySQL
* **Servidor Web:** Apache2 (compatible con Nginx como proxy inverso)
* **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap Material Design


## Instalación

Para desplegar la aplicación sigue este resumen. Si necesitas un paso a paso más detallado consulta el archivo [install.md](install.md).

1. **Instalar dependencias básicas**: Apache o Nginx, PHP 8.1 y las extensiones de MySQL.
2. **Clonar el repositorio** en el directorio deseado del servidor.
3. **Importar la base de datos** ejecutando `DB/inout.sql` sobre tu instancia de MariaDB/MySQL.
4. **Configurar la conexión** copiando el archivo `.env.example` a `.env` y completando tus credenciales de base de datos.
   Si deseas utilizar la síntesis de voz, proporciona también la ruta del JSON de Google en `TTS_CREDENTIALS_PATH`.

5. **Configurar tu servidor web** creando un VirtualHost que apunte al directorio del proyecto y habilitando el módulo `rewrite`.

Tras estos pasos la aplicación quedará lista para acceder desde `http://tu-dominio/login.php`.

### Credenciales de prueba

Si importaste la base de datos incluida encontrarás estos usuarios iniciales:

- **master / superuser**
- **user / 123456**
- **admin / library**

---
## Historial de versiones

- **v1.3.0** - Integración con Google Cloud Text-to-Speech y plantillas de mensajes por voz. Mejora la lógica de mensajes en pantalla y audio, evitando repeticiones y normalizando categorías.
- **v1.2.0** - Nueva carga de credenciales mediante archivo `.env`.
- **v1.1.2** - Mejoras de seguridad con consultas preparadas y compatibilidad con contraseñas antiguas.
- **v1.1.1** - Mensajes de saludo dinámicos según rol y género mediante la nueva clase `MessageHandler`, configuración de base de datos desde variables de entorno y sanitización reforzada.
- **v1.1.0** - Parches de estabilidad y conexión: soporte para bases de datos remotas, compatibilidad con PHP 8.1, corrección de redirecciones y script de migración.
- **v1.0.0** - Lanzamiento inicial con gestión completa de entradas y salidas, panel de control, roles, reportes y autenticación segura.


## Contribuciones

Las contribuciones son bienvenidas. Para cambios mayores, por favor abre un "issue" primero para discutir lo que te gustaría cambiar.

## Licencia

Este proyecto está bajo la Licencia [MIT](https://choosealicense.com/licenses/mit/).
