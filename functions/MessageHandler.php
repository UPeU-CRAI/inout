<?php

class MessageHandler {
    /**
     * $eventType posibles:
     *   - entry
     *   - exit
     *   - recent_entry
     *   - recent_exit
     *   - expired
     *   - not_found
     *   - birthday
     *   - borrower_note
     */

    /** Plantillas para pantalla: SOLO datos o alertas técnicas. */
    private array $screenTemplates = [
        'not_found' => "",
        'recent_entry' => "",
        'recent_exit' => "",
        'expired' => "",
        'birthday' => "",
        'borrower_note' => "",
        'entry' => [
            'DOCEN'   => "",
            'INVESTI' => "",
            'STAFF'   => "",
            'ADMIN'   => "",
            'VISITA'  => "",
            'ESTUDI'  => "",
            'DEFAULT' => "",
        ],
        'exit' => [
            'DOCEN'   => "",
            'INVESTI' => "",
            'STAFF'   => "",
            'ADMIN'   => "",
            'VISITA'  => "",
            'ESTUDI'  => "",
            'DEFAULT' => "",
        ],
    ];

    /**
     * Plantillas SOLO para voz (TTS), aquí van los saludos, bienvenida, etc.
     */
    private array $ttsTemplates = [
        'not_found' => "El código no fue reconocido. Por favor, intente de nuevo o solicite ayuda al personal.",
        'recent_entry' => "Ya has registrado tu entrada. Por favor, espera antes de volver a intentar.",
        'recent_exit' => "Ya has registrado tu salida. Por favor, espera antes de volver a ingresar.",
        'expired' => "Atención {nombre}, tu membresía ha expirado. Has ingresado como visitante.",
        'birthday' => "¡Feliz cumpleaños, {nombre}! Todo el equipo te desea un gran día.",
        'borrower_note' => "Tienes un mensaje del personal: {note}",
        'entry' => [
            'DOCEN'   => "Hola {nombre}, bienvenido al CRAI. Registro de entrada exitoso.",
            'INVESTI' => "Hola {nombre}, acceso autorizado. Te deseamos un día productivo.",
            'STAFF'   => "Bienvenido {nombre}, gracias por tu dedicación.",
            'ADMIN'   => "Hola {nombre}, acceso de administrador autorizado.",
            'VISITA'  => "Bienvenido, visitante. Esperamos que tu experiencia sea agradable.",
            'ESTUDI'  => "Hola {nombre}, bienvenido al CRAI. ¡Éxitos en tus estudios!",
            'DEFAULT' => "Hola {nombre}, acceso registrado correctamente.",
        ],
        'exit' => [
            'DOCEN'   => "Hasta luego, {nombre}. Que tengas un excelente día.",
            'INVESTI' => "Despedida registrada, {nombre}. Hasta pronto.",
            'STAFF'   => "¡Gracias por tu trabajo, {nombre}! Nos vemos.",
            'ADMIN'   => "Hasta pronto, {nombre}. Cierre de sesión de administrador realizado.",
            'VISITA'  => "Gracias por tu visita, vuelve pronto.",
            'ESTUDI'  => "Hasta luego, {nombre}. Esperamos verte de nuevo.",
            'DEFAULT' => "Salida registrada. ¡Hasta luego!",
        ],
    ];

    /**
     * Devuelve ambos mensajes (pantalla y voz) en un array asociativo.
     * 'visual' => mensaje para mostrar en pantalla (vacío en casi todos los casos).
     * 'voice'  => mensaje para reproducir por TTS.
     */
    public function getBothMessages(string $eventType, ?array $userData = null, array $miscData = []): array {
        return [
            'visual' => $this->getScreenMessage($eventType, $userData, $miscData),
            'voice'  => $this->getTTSMessage($eventType, $userData, $miscData),
        ];
    }

    /** Obtiene el mensaje para mostrar en pantalla */
    public function getScreenMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        // Puedes retornar "" aquí, pues los datos visuales se renderizan directamente desde variables en tu PHP
        return $this->generateMessage($eventType, $userData, $miscData, $this->screenTemplates);
    }

    /** Obtiene el mensaje para TTS */
    public function getTTSMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        // SOLO usa ttsTemplates, nunca caigas en pantalla.
        return $this->generateMessage($eventType, $userData, $miscData, $this->ttsTemplates);
    }

    /** Lógica principal de generación de mensajes */
    private function generateMessage(string $eventType, ?array $userData, array $miscData, array $templates): string {
        $combinedData = array_merge($userData ?? [], $miscData);

        // Sin usuario (ejemplo: error de código, eventos generales)
        if (in_array($eventType, ['not_found', 'recent_entry', 'recent_exit'])) {
            return $this->fetchTemplate($templates, $eventType);
        }

        if ($userData === null) {
            return "";
        }

        // Cumpleaños
        if ($this->isBirthday($userData['dateofbirth'] ?? null)) {
            $msg = $this->fetchTemplate($templates, 'birthday');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        // Nota de personal
        if (!empty($userData['borrowernotes'])) {
            $combinedData['note'] = $userData['borrowernotes'];
            $msg = $this->fetchTemplate($templates, 'borrower_note');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        // Expirado
        if ($eventType === 'expired') {
            $msg = $this->fetchTemplate($templates, 'expired');
            return $this->replacePlaceholders($msg, $combinedData);
        }

        // Entrada
        if ($eventType === 'entry') {
            $hour = $miscData['current_hour'] ?? (int)date('H');
            return $this->replacePlaceholders($this->buildEntryMessage($userData, $hour, $templates), $combinedData);
        }

        // Salida
        if ($eventType === 'exit') {
            return $this->replacePlaceholders($this->buildExitMessage($userData, $templates), $combinedData);
        }

        return '';
    }

    /** Mensaje entrada SOLO para voz */
    private function buildEntryMessage(array $userData, int $hour, array $templates): string {
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');
        $tplSet = $templates['entry'] ?? [];
        $template = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? '';
        return $template;
    }

    /** Mensaje salida SOLO para voz */
    private function buildExitMessage(array $userData, array $templates): string {
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');
        $tplSet = $templates['exit'] ?? [];
        $template = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? '';
        return $template;
    }

    /** Devuelve uno de los mensajes posibles (aleatorio si es array). */
    private function fetchTemplate(array $templates, string $key): string {
        $tpl = $templates[$key] ?? '';
        if (is_array($tpl)) {
            if (!$tpl) return '';
            return $tpl[array_rand($tpl)];
        }
        return $tpl;
    }

    /** Reemplaza los placeholders en la plantilla con los datos proporcionados. */
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

    /** Verifica si la fecha de nacimiento coincide con el día actual. */
    private function isBirthday(?string $dateOfBirth): bool {
        if (empty($dateOfBirth)) return false;
        $timestamp = strtotime($dateOfBirth);
        if ($timestamp === false) return false;
        return date('m-d') === date('m-d', $timestamp);
    }
}
