<?php

class MessageHandler {
    /**
     * Gestor de mensajes mostrados en pantalla y/o reproducidos por TTS.
     *
     * Valores esperados en <code>$eventType</code>:
     *  - <code>entry</code>
     *  - <code>recent_entry</code>
     *  - <code>exit</code>
     *  - <code>recent_exit</code>
     *  - <code>expired</code>
     *  - <code>not_found</code>
     *  - <code>birthday</code> (automático si coincide la fecha)
     *  - <code>borrower_note</code> (si existe una nota del usuario)
     *
     * Claves de reemplazo disponibles en las plantillas:
     * <code>{firstname}</code>, <code>{surname}</code>, <code>{nombre}</code>,
     * <code>{apellido}</code>, <code>{title}</code>, <code>{titulo}</code>,
     * <code>{usn}</code>, <code>{label}</code>, <code>{time}</code>,
     * <code>{duration}</code> y <code>{note}</code>.
     */

    /** Default templates for screen output */
    private array $screenTemplates = [
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
        'expired' => "Atención, {nombre}. Tu membresía ha expirado. Has ingresado como VISITA. Por favor, acércate al mostrador para renovarla.",
        'birthday' => "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.",
        'borrower_note' => "Un mensaje importante del personal: {note}",
        'entry' => [
            'DOCEN'   => "{greeting}, {prof} {nombre}. Su entrada es a las: {time}.",
            'INVESTI' => "{greeting}, {title_greet} {role} {apellido}. Entrada: {time}.",
            'STAFF'   => "{greeting}, {welcome}, colega {nombre}. Entrada: {time}.",
            'ADMIN'   => "{greeting}, {role} {nombre}. Entrada: {time}.",
            'VISITA'  => "{greeting}. Le damos una cordial bienvenida. Su USN es: {usn}. Hora de entrada: {time}.",
            'ESTUDI'  => "{greeting}, {welcome} {nombre}. Tu USN es: {usn}. Hora de entrada: {time}.",
            'DEFAULT' => "{greeting}, {welcome} {nombre}. Entrada: {time}.",
        ],
        'exit' => [
            'DOCEN'   => "Hasta pronto, {prof} {nombre}. Su duración total fue de: {duration}.",
            'INVESTI' => "Despedida, {role} {apellido}. Duración: {duration}.",
            'STAFF'   => "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.",
            'ADMIN'   => "Hasta pronto, {role} {nombre}. Duración: {duration}.",
            'VISITA'  => "Gracias por visitarnos. Tu visita duró: {duration}.",
            'ESTUDI'  => "¡Hasta pronto, {nombre}! Tu duración total fue de: {duration}.",
            'DEFAULT' => "¡Hasta pronto, {nombre}! Duración total: {duration}.",
        ],
    ];

    /** Default templates for TTS output (solo se definen diferencias) */
    private array $ttsTemplates = [];

    /**
     * Punto de entrada principal para generar un mensaje.
     * La prioridad de generación es la siguiente:
     *  1. Cumpleaños.
     *  2. Mensajes globales (not_found, recent_entry, recent_exit).
     *  3. Notas de personal.
     *  4. Membresía expirada.
     *  5. Mensajes de entrada o salida normales.
     *
     * @deprecated Usa getScreenMessage() o getTTSMessage() en su lugar.
     */
    public function getMessage(string $eventType, ?array $userData = null, array $miscData = [], string $target = 'screen'): string {
        if ($target === 'tts') {
            return $this->getTTSMessage($eventType, $userData, $miscData);
        }
        return $this->getScreenMessage($eventType, $userData, $miscData);
    }

    /** Obtiene el mensaje para mostrar en pantalla */
    public function getScreenMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        return $this->generateMessage($eventType, $userData, $miscData, $this->screenTemplates);
    }

    /** Obtiene el mensaje para TTS */
    public function getTTSMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        // Si no hay plantilla específica se usa la de pantalla
        $templates = array_replace_recursive($this->screenTemplates, $this->ttsTemplates);
        return $this->generateMessage($eventType, $userData, $miscData, $templates);
    }

    /** Lógica principal de generación de mensajes */
    private function generateMessage(string $eventType, ?array $userData, array $miscData, array $templates): string {
        $combinedData = array_merge($userData ?? [], $miscData);

        if (isset($templates[$eventType]) && !in_array($eventType, ['entry', 'exit'])) {
            return $this->fetchTemplate($templates, $eventType);
        }

        if ($userData === null) {
            return "Error interno: Se esperaba información del usuario para este evento.";
        }

        if ($this->isBirthday($userData['dateofbirth'] ?? null)) {
            $msg = $this->fetchTemplate($templates, 'birthday');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        if (!empty($userData['borrowernotes'])) {
            $combinedData['note'] = $userData['borrowernotes'];
            $msg = $this->fetchTemplate($templates, 'borrower_note');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        if ($eventType === 'expired') {
            $msg = $this->fetchTemplate($templates, 'expired');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        if ($eventType === 'entry') {
            $hour = $miscData['current_hour'] ?? (int)date('H');
            return $this->replacePlaceholders($this->buildEntryMessage($userData, $hour, $templates), $combinedData);
        }

        if ($eventType === 'exit') {
            return $this->replacePlaceholders($this->buildExitMessage($userData, $templates), $combinedData);
        }

        return '';
    }

    /**
     * Construye el mensaje de entrada utilizando la franja horaria,
     * la categoría y el género del usuario.
     */
    private function buildEntryMessage(array $userData, int $hour, array $templates): string {
        $gender   = strtoupper($userData['gender'] ?? 'DEFAULT');
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');

        $greeting = $this->getTimeGreeting($hour);

        $tplSet = $templates['entry'] ?? [];
        $template = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? '';

        $template = str_replace([
            '{greeting}',
            '{prof}',
            '{title_greet}',
            '{role}',
            '{welcome}'
        ], [
            $greeting,
            $this->genderize('Profesor', $gender, 'Profesora', 'Profesor/a'),
            $this->genderize('Estimado', $gender, 'Estimada', 'Estimado/a'),
            match ($category) {
                'INVESTI' => $this->genderize('Investigador', $gender, 'Investigadora', 'Investigador/a'),
                'ADMIN'   => $this->genderize('creador', $gender, 'creadora', 'creador/a'),
                default   => ''
            },
            $this->genderize('Bienvenido', $gender)
        ], $template);

        return $template;
    }

    /**
     * Construye el mensaje de salida de acuerdo con la categoría del usuario.
     */
    private function buildExitMessage(array $userData, array $templates): string {
        $gender   = strtoupper($userData['gender'] ?? 'DEFAULT');
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');

        $tplSet = $templates['exit'] ?? [];
        $template = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? '';

        $template = str_replace([
            '{prof}',
            '{role}'
        ], [
            $this->genderize('Profesor', $gender, 'Profesora', 'Profesor/a'),
            match ($category) {
                'INVESTI' => $this->genderize('Investigador', $gender, 'Investigadora', 'Investigador/a'),
                'ADMIN'   => $this->genderize('creador', $gender, 'creadora', 'creador/a'),
                default   => ''
            }
        ], $template);

        return $template;
    }

    /**
     * Mensaje mostrado cuando la cuenta está expirada.
     */
    private function buildExpiredMessage(array $templates): string {
        return $this->fetchTemplate($templates, 'expired');
    }

    /**
     * Mensaje de cumpleaños.
     */
    private function buildBirthdayMessage(array $templates): string {
        return $this->fetchTemplate($templates, 'birthday');
    }

    /**
     * Mensaje para mostrar notas de personal.
     */
    private function buildBorrowerNoteMessage(array $templates): string {
        return $this->fetchTemplate($templates, 'borrower_note');
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
     * Selecciona de forma aleatoria uno de los mensajes.
     */
    private function fetchTemplate(array $templates, string $key): string {
        $tpl = $templates[$key] ?? '';
        if (is_array($tpl)) {
            if (!$tpl) {
                return '';
            }
            return $tpl[array_rand($tpl)];
        }
        return $tpl;
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
