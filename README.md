# Sistema de Gestión de Entradas/Salidas (In/Out Management System) v1.3.2

## Descripción

Este proyecto es un sistema de gestión de entradas y salidas de usuarios, diseñado principalmente para monitorear el flujo de personas en bibliotecas (CRAI) y otras instalaciones. El sistema registra la hora de entrada y salida de cada usuario mediante un número de tarjeta o código de identificación y se integra con la base de datos de Koha para obtener información del usuario.

Esta versión (`v1.3.2`) incorpora un panel de *Novedades bibliográficas* que consulta Koha para mostrar las carátulas de los últimos títulos ingresados. También mejora la carga del archivo `.env` y permite configurar la URL del OPAC para dichas carátulas. Se mantienen las mejoras de estilos y la integración con Google Cloud Text-to-Speech de versiones anteriores.

## Características

* Registro de entradas y salidas de usuarios en tiempo real.
* Panel de visualización (`dashboard`) para el registro de actividad.
* Integración con el sistema de gestión de bibliotecas Koha.
* Sistema de roles de usuario (Master, Admin, User).
* Generación de reportes de actividad.
* Noticias y anuncios para los usuarios.
* Novedades bibliográficas con imágenes de portada recientes.
* Mensajes de saludo dinámicos y personalizados según rol y género.
* Avisos automáticos cuando la cuenta de un usuario está expirada.
* Síntesis de voz con Google Cloud Text-to-Speech para mensajes audibles.
* El audio se genera directamente desde PHP sin utilizar un script `tts.php`.
* Configuración flexible mediante variables de entorno.
* Consultas preparadas y sanitización de entradas para mayor seguridad.
* El campo de escaneo mantiene el foco automáticamente en el dashboard.
* El panel de "New Arrivals" consulta Koha para mostrar las portadas de los últimos títulos ingresados.

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
   Si deseas utilizar la síntesis de voz, define el proveedor en `TTS_PROVIDER` (`google` o `azure`).
   Para Google especifica la ruta del JSON de credenciales en `GOOGLE_TTS_CREDENTIALS_PATH`.
   Para Azure configura `SPEECH_KEY` y `SPEECH_REGION`.
   Para mostrar las carátulas en "New Arrivals" especifica la dirección base de tu OPAC en `KOHA_OPAC_URL`.
   Asegúrate de que el archivo `.env` esté ubicado en la raíz del proyecto y que
   dicha variable tenga un valor válido; si está vacía no se cargará la URL.

5. **Configurar tu servidor web** creando un VirtualHost que apunte al directorio del proyecto y habilitando el módulo `rewrite`.

Tras estos pasos la aplicación quedará lista para acceder desde `http://tu-dominio/login.php`.

## Integración con Azure Speech TTS (REST API)

La síntesis de voz con Azure se implementa exclusivamente mediante la **REST API**.
No se utiliza ningún SDK oficial de PHP, ya que actualmente no existe soporte
completo para este lenguaje. La clase `AzureSpeech` hace uso de la extensión
**cURL** y funciones nativas de PHP para obtener el token de autenticación y
generar el audio.

### Requisitos

- PHP 8.1 con la extensión `cURL` habilitada. En sistemas basados en Debian o
  Ubuntu puede instalarse con:

  ```bash
  sudo apt-get install php8.1-curl
  ```

  Para otras distribuciones consulta el gestor de paquetes correspondiente.
- Definir `TTS_PROVIDER=azure` en el archivo `.env` o variables de entorno.
- Proveer las credenciales y valores de configuración que se indican a
  continuación.

### Variables necesarias

```ini
SPEECH_KEY=YOUR_AZURE_SPEECH_KEY
SPEECH_REGION=YOUR_AZURE_REGION
AZURE_TTS_VOICE_A=es-MX-JorgeNeural
AZURE_TTS_VOICE_B=es-MX-DaliaNeural
AZURE_TTS_LANGUAGE_CODE=es-MX
#AZURE_TTS_FORMAT=audio-16khz-32kbitrate-mono-mp3
```

Estas voces son solo un ejemplo. Puedes personalizarlas editando el archivo
`.env` o configurando variables de entorno del sistema. Al modificar estos
valores, el sistema usará las nuevas voces e idioma en las solicitudes a Azure.

Tras cambiar el `.env` recuerda reiniciar Apache/Nginx o el proceso PHP-FPM para
que PHP recargue las variables.

Consulta el [listado oficial de voces de Azure](https://learn.microsoft.com/azure/cognitive-services/speech-service/language-support?tabs=tts)
y la [documentación de la API REST](https://learn.microsoft.com/azure/cognitive-services/speech-service/rest-text-to-speech)
para más detalles.

### Credenciales de prueba

Si importaste la base de datos incluida encontrarás estos usuarios iniciales:

- **master / superuser**
- **user / 123456**
- **admin / library**

---
## Historial de versiones

- **v1.3.2** - Panel de "Novedades bibliográficas" con carátulas de los últimos libros de Koha y mejoras en la configuración de `.env`.
- **v1.3.1** - Correcciones y mejoras en los estilos CSS para una presentación más consistente.
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
