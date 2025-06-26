<?php

class MessageHandler {
    /**
     * Plantillas genéricas para mensajes que no dependen de datos
     * personalizados del usuario (por ejemplo, cuando el código no
     * coincide o el usuario intenta registrar dos veces la misma acción).
     */
    private array $generalTemplates = [
        'not_found' => [
            "Código de usuario no reconocido. Por favor, intente de nuevo o consulte con el personal.",
            "Usuario no encontrado en nuestra base de datos. Verifique su código o contacte a un asistente.",
            "El código escaneado no corresponde a ningún usuario registrado. ¿Necesita ayuda?",
        ],
        'recent_entry' => [
            "Acabas de registrar tu entrada. Por favor, espera unos segundos antes de intentar una nueva acción.",
            "Ya te has registrado. Si deseas salir, por favor, espera un momento.",
            "¡Entrada confirmada! Permanece unos segundos antes de registrar tu salida.",
        ],
        'recent_exit' => [
            "Acabas de registrar tu salida. Espera unos segundos antes de intentar una nueva entrada.",
            "Salida confirmada. Si deseas volver a entrar, espera un breve momento.",
            "¡Hasta pronto! Si necesitas volver a entrar, aguarda un poco.",
        ],
    ];

    /**
     * Plantillas breves para los mensajes mostrados en pantalla. Estos textos
     * se enfocan en ser concisos y se utilizarán únicamente para el mensaje
     * visible, mientras que la versión detallada será reproducida por TTS.
     */
    private array $screenTemplates = [
        'not_found'    => 'Código no reconocido.',
        'recent_entry' => 'Entrada ya registrada.',
        'recent_exit'  => 'Salida ya registrada.',
        'expired'      => 'Membresía expirada.',
        'entry'        => 'Entrada registrada.',
        'exit'         => 'Salida registrada.',
    ];

    /**
     * Punto de entrada principal para generar un mensaje.
     * La prioridad de generación es la siguiente:
     *  1. Cumpleaños.
     *  2. Mensajes globales (not_found, recent_entry, recent_exit).
     *  3. Notas de personal.
     *  4. Membresía expirada.
     *  5. Mensajes de entrada o salida normales.
     */
    public function getMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        return $this->getTTSMessage($eventType, $userData, $miscData);
    }

    /**
     * Obtiene el texto completo que será reproducido por el sistema TTS.
     * La lógica sigue el mismo orden de prioridades que tenía anteriormente.
     */
    public function getTTSMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        $combinedData = array_merge($userData ?? [], $miscData);

        if ($eventType === 'not_found') {
            return $this->getRandomGeneral('not_found');
        }

        if (in_array($eventType, ['recent_entry', 'recent_exit'])) {
            return $this->getRandomGeneral($eventType);
        }

        if ($userData === null) {
            return "Error interno: Se esperaba información del usuario para este evento.";
        }

        if ($this->isBirthday($userData['dateofbirth'] ?? null)) {
            return $this->replacePlaceholders($this->buildBirthdayMessage(), $combinedData);
        }

        if (!empty($userData['borrowernotes'])) {
            $combinedData['note'] = $userData['borrowernotes'];
            return $this->replacePlaceholders($this->buildBorrowerNoteMessage(), $combinedData);
        }

        if ($eventType === 'expired') {
            return $this->replacePlaceholders($this->buildExpiredMessage(), $combinedData);
        }

        if ($eventType === 'entry') {
            $hour = $miscData['current_hour'] ?? (int)date('H');
            return $this->replacePlaceholders($this->buildEntryMessage($userData, $hour), $combinedData);
        }

        if ($eventType === 'exit') {
            return $this->replacePlaceholders($this->buildExitMessage($userData), $combinedData);
        }

        return '';
    }

    /**
     * Obtiene el mensaje corto que se mostrará en pantalla. Devuelve la cadena
     * ya envuelta en un elemento span con las clases de animación.
     */
    public function getScreenMessage(string $eventType, array $vars = []): string {
        $combinedData = $vars;

        // Mensajes simples basados en plantillas predefinidas
        if (isset($this->screenTemplates[$eventType])) {
            $text = $this->replacePlaceholders($this->screenTemplates[$eventType], $combinedData);
            return "<span class=\"animated flash tts-text\">$text</span>";
        }

        // Mensajes especiales que dependen de datos del usuario
        if ($eventType !== 'not_found' && !empty($vars)) {
            if ($this->isBirthday($vars['dateofbirth'] ?? null)) {
                $text = $this->replacePlaceholders('¡Feliz cumpleaños, {nombre}!', $combinedData);
                return "<span class=\"animated flash tts-text\">$text</span>";
            }

            if (!empty($vars['borrowernotes'])) {
                $combinedData['note'] = $vars['borrowernotes'];
                $text = $this->replacePlaceholders('Nota: {note}', $combinedData);
                return "<span class=\"animated flash tts-text\">$text</span>";
            }
        }

        return '';
    }

    /**
     * Construye el mensaje de entrada utilizando la franja horaria,
     * la categoría y el género del usuario.
     */
    private function buildEntryMessage(array $userData, int $hour): string {
        $gender   = strtoupper($userData['gender'] ?? 'DEFAULT');
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');

        $greeting = $this->getTimeGreeting($hour);

        switch ($category) {
            case 'DOCEN':
                $prof = $this->genderize('Profesor', $gender, 'Profesora', 'Profesor/a');
                return "$greeting, $prof {nombre}. Su entrada es a las: {time}.";
            case 'INVESTI':
                $title  = $this->genderize('Estimado', $gender, 'Estimada', 'Estimado/a');
                $role   = $this->genderize('Investigador', $gender, 'Investigadora', 'Investigador/a');
                return "$greeting, $title $role {apellido}. Entrada: {time}.";
            case 'STAFF':
                $welcome = $this->genderize('Bienvenido', $gender);
                return "$greeting, $welcome, colega {nombre}. Entrada: {time}.";
            case 'ADMIN':
                $role = $this->genderize('creador', $gender, 'creadora', 'creador/a');
                return "$greeting, $role {nombre}. Entrada: {time}.";
            case 'VISITA':
                return "$greeting. Le damos una cordial bienvenida. Su USN es: {usn}. Hora de entrada: {time}.";
            case 'ESTUDI':
                $welcome = $this->genderize('Bienvenido', $gender);
                return "$greeting, $welcome {nombre}. Tu USN es: {usn}. Hora de entrada: {time}.";
            default:
                $welcome = $this->genderize('Bienvenido', $gender);
                return "$greeting, $welcome {nombre}. Entrada: {time}.";
        }
    }

    /**
     * Construye el mensaje de salida de acuerdo con la categoría del usuario.
     */
    private function buildExitMessage(array $userData): string {
        $gender   = strtoupper($userData['gender'] ?? 'DEFAULT');
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');

        switch ($category) {
            case 'DOCEN':
                $prof = $this->genderize('Profesor', $gender, 'Profesora', 'Profesor/a');
                return "Hasta pronto, $prof {nombre}. Su duración total fue de: {duration}.";
            case 'INVESTI':
                $role = $this->genderize('Investigador', $gender, 'Investigadora', 'Investigador/a');
                return "Despedida, $role {apellido}. Duración: {duration}.";
            case 'STAFF':
                return "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.";
            case 'ADMIN':
                $role = $this->genderize('creador', $gender, 'creadora', 'creador/a');
                return "Hasta pronto, $role {nombre}. Duración: {duration}.";
            case 'VISITA':
                return "Gracias por visitarnos. Tu visita duró: {duration}.";
            case 'ESTUDI':
                return "¡Hasta pronto, {nombre}! Tu duración total fue de: {duration}.";
            default:
                return "¡Hasta pronto, {nombre}! Duración total: {duration}.";
        }
    }

    /**
     * Mensaje mostrado cuando la cuenta está expirada.
     */
    private function buildExpiredMessage(): string {
        return "Atención, {nombre}. Tu membresía ha expirado. Has ingresado como VISITA. Por favor, acércate al mostrador para renovarla.";
    }

    /**
     * Mensaje de cumpleaños.
     */
    private function buildBirthdayMessage(): string {
        return "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.";
    }

    /**
     * Mensaje para mostrar notas de personal.
     */
    private function buildBorrowerNoteMessage(): string {
        return "Un mensaje importante del personal: {note}";
    }

    /**
     * Devuelve un saludo basado en la franja horaria.
     */
    private function getTimeGreeting(int $hour): string {
        $time = $this->getTimeOfDay($hour);
        return match ($time) {
            'morning'   => '¡Buenos días',
            'afternoon' => '¡Buenas tardes',
            'night'     => '¡Buenas noches',
            default     => '¡Hola',
        };
    }

    /**
     * Ajusta una palabra según el género.
     */
    private function genderize(string $male, string $gender, ?string $female = null, ?string $neutral = null): string {
        return match ($gender) {
            'M' => $male,
            'F' => $female ?? $male . 'a',
            default => $neutral ?? $male . '/a',
        };
    }

    /**
     * Selecciona de forma aleatoria uno de los mensajes genéricos.
     */
    private function getRandomGeneral(string $type): string {
        $templates = $this->generalTemplates[$type] ?? [];
        return $templates[array_rand($templates)] ?? '';
    }

    /**
     * Determina la franja horaria del día.
     */
    private function getTimeOfDay(int $hour): string {
        if ($hour >= 5 && $hour < 12) {
            return 'morning';
        } elseif ($hour >= 12 && $hour < 19) {
            return 'afternoon';
        } elseif ($hour >= 19 || $hour < 5) {
            return 'night';
        }
        return 'default';
    }

    /**
     * Reemplaza los placeholders en la plantilla con los datos proporcionados.
     */
    private function replacePlaceholders(string $template, array $data): string {
        $placeholders = [
            '{name}'      => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{firstname}' => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{nombre}'    => htmlspecialchars($data['firstname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{surname}'   => htmlspecialchars($data['surname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{apellido}'  => htmlspecialchars($data['surname'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{title}'     => htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{titulo}'    => htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{usn}'       => htmlspecialchars($data['usn'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{label}'     => htmlspecialchars($data['label'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{time}'      => htmlspecialchars($data['time'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{duration}'  => htmlspecialchars($data['duration'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{note}'      => htmlspecialchars($data['note'] ?? '', ENT_QUOTES, 'UTF-8'),
        ];

        $template = str_replace(array_keys($placeholders), array_values($placeholders), $template);
        return preg_replace('/{[^}]+}/', '', $template);
    }

    /**
     * Verifica si la fecha de nacimiento coincide con el día actual.
     */
    private function isBirthday(?string $dateOfBirth): bool {
        if (empty($dateOfBirth)) {
            return false;
        }
        $timestamp = strtotime($dateOfBirth);
        if ($timestamp === false) {
            return false;
        }
        return date('m-d') === date('m-d', $timestamp);
    }
}
