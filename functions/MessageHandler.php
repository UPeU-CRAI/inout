<?php

class MessageHandler {
    // Banco centralizado de plantillas de mensajes, organizadas por evento y categoría
    private array $templates;

    public function __construct() {
        $this->templates = [
            'entry' => [
                'ESTUDI' => [
                    "¡Qué tal, {nombre}! Tu código USN es: {usn}. Hora de entrada: {time}.",
                    "¡Hola, {nombre}! Bienvenido/a de vuelta. Entrada registrada a las: {time}.",
                    "¡Bienvenido/a, {nombre}! Tu USN es: {usn}. Hora de ingreso: {time}."
                ],
                'DOCEN' => [
                    "Estimado/a {titulo} {apellido}, un placer recibirle. Entrada registrada a las: {time}.",
                    "Bienvenido/a, {titulo} {apellido}. Su código USN es: {usn}. Hora de entrada: {time}.",
                    "Saludos, {titulo} {apellido}. Ingreso verificado a las: {time}."
                ],
                'INVESTI' => [
                    "Estimado/a Investigador/a {apellido}, bienvenido/a. Entrada: {time}.",
                    "Bienvenido/a, {nombre} {apellido}. Su USN es: {usn}. Hora de entrada: {time}.",
                    "Registro de ingreso para el/la Investigador/a {apellido}: {time}."
                ],
                'STAFF' => [
                    "¡Hola, colega {nombre}! Qué bueno verte. Entrada registrada: {time}.",
                    "¡Qué bueno verte, {nombre}! Tu USN es: {usn}. Ingreso a las: {time}.",
                    "Saludos, {nombre}. Tu entrada ha sido registrada: {time}."
                ],
                'ADMIN' => [
                    "Saludos, creador {nombre}. Entrada registrada: {time}.",
                    "Bienvenido, Super-Usuario {nombre}. Tu USN es: {usn}. Hora de ingreso: {time}.",
                    "Acceso concedido, {nombre}. Registro de entrada: {time}."
                ],
                'VISITA' => [
                    "Le damos una cordial bienvenida. Su código USN es: {usn}. Hora de entrada: {time}.",
                    "Bienvenido/a a nuestra biblioteca. Ingreso registrado a las: {time}.",
                    "Gracias por visitarnos. Su hora de entrada es: {time}."
                ],
                'DEFAULT' => [
                    "¡Hola, {nombre}! Bienvenido/a. Entrada registrada a las: {time}.",
                    "Bienvenido/a, {nombre}. Tu USN es: {usn}. Hora de ingreso: {time}.",
                    "Acceso registrado. Hora de entrada: {time}."
                ]
            ],
            'exit' => [
                'ESTUDI' => [
                    "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                    "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    "¡Vuelve pronto, {nombre}! Tiempo en el edificio: {duration}."
                ],
                'DOCEN' => [
                    "Despedida, {titulo} {apellido}. Su duración total fue de: {duration}.",
                    "Gracias por su visita, {titulo} {apellido}. Permaneció por: {duration}.",
                    "Hasta la próxima, {titulo} {apellido}. Su tiempo de permanencia: {duration}."
                ],
                'INVESTI' => [
                    "Despedida, Investigador/a {apellido}. Duración: {duration}.",
                    "Gracias, Investigador/a {apellido}. Su tiempo fue de: {duration}.",
                    "Hasta la próxima, Investigador/a {apellido}. Permaneció por: {duration}."
                ],
                'STAFF' => [
                    "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.",
                    "Gracias por tu jornada, {nombre}. Tiempo registrado: {duration}.",
                    "Nos vemos, {nombre}. Tu tiempo de permanencia fue de: {duration}."
                ],
                'ADMIN' => [
                    "Despedida, creador {nombre}. Duración: {duration}.",
                    "Registro de salida para {nombre}. Tiempo total: {duration}.",
                    "Hasta la próxima, {nombre}. Tu permanencia fue de: {duration}."
                ],
                'VISITA' => [
                    "Gracias por visitarnos. Vuelva pronto. Duración total: {duration}.",
                    "Esperamos verte de nuevo. Tu visita duró: {duration}.",
                    "Despedida. Su tiempo de permanencia fue de: {duration}."
                ],
                'DEFAULT' => [
                    "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                    "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    "¡Vuelve pronto! Tiempo en el edificio: {duration}."
                ]
            ],
            'expired' => [
                "Hola, {nombre}. Tu membresía ha expirado. Te hemos registrado como visita. Por favor, acércate al mostrador para renovarla.",
                "Atención, {nombre}. Tu cuenta parece estar inactiva o expirada. Para continuar disfrutando de nuestros servicios, por favor, renueva tu membresía en recepción.",
                "Estimado/a {nombre}, tu membresía ha caducado. Has sido registrado/a temporalmente como visita. Te invitamos a pasar por el mostrador para regularizar tu situación."
            ],
            'recent_entry' => [
                "Acabas de registrar tu entrada. Por favor, espera unos segundos antes de intentar una nueva acción.",
                "Ya te has registrado. Si deseas salir, por favor, espera un momento.",
                "¡Entrada confirmada! Permanece unos segundos antes de registrar tu salida."
            ],
            'recent_exit' => [
                "Acabas de registrar tu salida. Espera unos segundos antes de intentar una nueva entrada.",
                "Salida confirmada. Si deseas volver a entrar, espera un breve momento.",
                "¡Hasta pronto! Si necesitas volver a entrar, aguarda un poco."
            ],
            'birthday' => [
                "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.",
                "¡Un muy feliz cumpleaños, {nombre}! Que tengas un día lleno de alegría y muchas lecturas.",
                "El personal de la biblioteca te envía un gran saludo de cumpleaños, {nombre}. ¡Que lo disfrutes mucho!"
            ],
            'not_found' => [
                "Código de usuario no reconocido. Por favor, intente de nuevo o consulte con el personal.",
                "Usuario no encontrado en nuestra base de datos. Verifique su código o contacte a un asistente.",
                "El código escaneado no corresponde a ningún usuario registrado. ¿Necesita ayuda?"
            ],
            'borrowernotes' => [
                "{note}", // Placeholder para las notas del prestatario
            ]
        ];
    }

    /**
     * Punto de entrada principal para generar un mensaje.
     * @param string $eventType - El evento que ocurre ('entry', 'exit', 'birthday', 'expired', 'not_found', etc.).
     * @param array|null $userData - Array asociativo con datos del usuario.
     * @param array $miscData - Array asociativo para datos adicionales como 'time', 'duration', etc.
     * @return string
     */
    public function getMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        // Prioridad 1: Mostrar notas del prestatario si existen y el evento no es 'not_found'
        if ($userData && !empty($userData['borrowernotes']) && $eventType !== 'not_found') {
            return $this->replacePlaceholders($this->getRandomTemplate('borrowernotes'), ['note' => $userData['borrowernotes']]);
        }

        // Prioridad 2: Mensaje de cumpleaños
        if ($userData && isset($userData['dateofbirth']) && $this->isBirthday($userData['dateofbirth'])) {
            return $this->replacePlaceholders($this->getRandomTemplate('birthday'), $userData);
        }
        
        // Prioridad 3: Mensaje de "no encontrado" (no depende de userData)
        if ($eventType === 'not_found') {
            return $this->getRandomTemplate('not_found');
        }

        // Si se espera userData pero no se proporciona
        if ($userData === null) {
            return "Error: Se esperaba información del usuario para este evento.";
        }
        
        // Seleccionar la plantilla adecuada para el evento y la categoría
        $template = $this->getTemplate($eventType, $userData);
        
        // Unir userData y miscData para el reemplazo de placeholders
        $combinedData = array_merge($userData, $miscData);

        return $this->replacePlaceholders($template, $combinedData);
    }

    /**
     * Selecciona la plantilla de mensaje adecuada basada en el evento y la categoría del usuario.
     * @return string
     */
    private function getTemplate(string $eventType, array $userData): string {
        // Obtener la categoría del usuario o usar 'DEFAULT'
        $category = $userData['categorycode'] ?? 'DEFAULT';

        // Intentar obtener las plantillas para el evento específico
        $eventTemplates = $this->templates[$eventType] ?? null;

        // Si el evento no tiene plantillas o no es un array (ej. 'expired' o 'recent_entry/exit' que son strings directamente)
        if (!is_array($eventTemplates)) {
            return $eventTemplates ?? ''; // Retorna la plantilla directamente si no es un array, o cadena vacía
        }

        // Si el evento tiene plantillas organizadas por categoría
        // Intentar obtener las plantillas para la categoría específica dentro del evento
        $categoryTemplates = $eventTemplates[$category] ?? null;

        // Si no hay plantillas para la categoría específica, usar las plantillas 'DEFAULT' del evento
        if ($categoryTemplates === null) {
            $categoryTemplates = $eventTemplates['DEFAULT'] ?? [];
        }
        
        // Si las plantillas de la categoría son un array, elige una al azar
        if (is_array($categoryTemplates) && !empty($categoryTemplates)) {
            return $categoryTemplates[array_rand($categoryTemplates)];
        } elseif (is_string($categoryTemplates)) {
            return $categoryTemplates; // Si es una sola plantilla de cadena
        }

        return ''; // Retornar cadena vacía si no se encuentra ninguna plantilla
    }

    /**
     * Obtiene una plantilla aleatoria de un tipo específico de evento que no depende de la categoría.
     * Se usa para cumpleaños, expiración y no encontrado, que tienen múltiples opciones pero son globales.
     * @param string $eventType
     * @return string
     */
    private function getRandomTemplate(string $eventType): string {
        $templates = $this->templates[$eventType] ?? [];
        if (is_array($templates) && !empty($templates)) {
            return $templates[array_rand($templates)];
        } elseif (is_string($templates)) {
            return $templates;
        }
        return '';
    }
    
    /**
     * Reemplaza los placeholders en la plantilla con los datos proporcionados.
     * @param string $template
     * @param array $data
     * @return string
     */
    private function replacePlaceholders(string $template, array $data): string {
        $placeholders = [
            '{name}'         => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{firstname}'    => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{nombre}'       => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{surname}'      => htmlspecialchars($data['surname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{apellido}'     => htmlspecialchars($data['surname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{title}'        => htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{titulo}'       => htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{usn}'          => htmlspecialchars($data['usn'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{label}'        => htmlspecialchars($data['label'] ?? '', ENT_QUOTES, 'UTF-8'), // Para compatibilidad con tu código anterior
            '{time}'         => htmlspecialchars($data['time'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{duration}'     => htmlspecialchars($data['duration'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{note}'         => htmlspecialchars($data['note'] ?? '', ENT_QUOTES, 'UTF-8'), // Para borrowernotes
        ];

        // Reemplazar los placeholders
        $template = str_replace(array_keys($placeholders), array_values($placeholders), $template);

        // Opcional: Eliminar cualquier placeholder que no haya sido reemplazado para evitar ver {unreplaced}
        return preg_replace('/{[^}]+}/', '', $template);
    }

    /**
     * Verifica si la fecha de nacimiento coincide con el día actual.
     * @param string|null $dateOfBirth
     * @return bool
     */
    private function isBirthday(?string $dateOfBirth): bool {
        if (empty($dateOfBirth)) {
            return false;
        }
        $timestamp = strtotime($dateOfBirth);
        if ($timestamp === false) {
            return false; // Manejar error de parseo de fecha
        }
        return date('m-d') === date('m-d', $timestamp);
    }
}
