<?php

class MessageHandler {
    private array $timeGreetings = [
        'morning'   => ['¡Buenos días', 'Muy buenos días', 'Saludos matutinos'],
        'afternoon' => ['¡Buenas tardes', 'Que tenga una excelente tarde', 'Saludos cordiales esta tarde'],
        'night'     => ['¡Buenas noches', 'Le deseamos una noche tranquila', 'Saludos en esta hermosa noche'],
        'default'   => ['Saludos', 'Hola', 'Qué tal'],
    ];

    // Plantillas para voz (TTS), ahora más formales y divertidas
    private array $ttsTemplates = [
        'not_found' => [
            "El código ingresado no corresponde a ningún usuario. ¿Probamos de nuevo?",
            "No encontramos registro con ese código. ¿Desea ayuda del personal?",
            "Usuario no reconocido. Si el problema persiste, por favor acérquese al mostrador.",
            "No se encontró al usuario. ¡Quizá es hora de renovar la matrícula!",
        ],
        'recent_entry' => [
            "Ya ha registrado su entrada recientemente. Disfrute su estadía.",
            "Su ingreso ya fue anotado. Siéntase como en casa.",
            "Entrada duplicada detectada. Puede continuar su visita con tranquilidad.",
        ],
        'recent_exit' => [
            "Ya se registró su salida. ¡Que tenga un excelente día!",
            "Su salida ya está anotada. Esperamos verle de nuevo muy pronto.",
            "Registro de salida duplicado. ¡Hasta la próxima!",
        ],
        'expired' => [
            "{greeting}, {nombre}. Le informamos que su contrato de matrícula ha expirado. Ha sido registrado como visitante. Bienvenido!.",
            "{greeting}, {nombre}. Su matrícula está vencida. Puedes acceder como visitante autorizado.",
            "{greeting}, {nombre}. Matrícula caducada. Puedes acceder como visitante.",
        ],
        'birthday' => [
            "¡Feliz cumpleaños, {nombre}! Que tu día esté lleno de éxitos y buenos momentos en el CRAI.",
            "Celebramos junto a ti, {nombre}, un año más de vida. ¡Muchas felicidades!",
            "¡Enhorabuena, {nombre}! te deseo un cumpleaños inolvidable.",
        ],
        'borrower_note' => [
            "Atención!, Mensaje para {nombre} : {note}",
            "{nombre}, tienes un mensaje: {note}",
            "{nombre}, El personal te informa: {note}",
        ],
        // Mensajes de entrada por rol, saludos breves
        'entry' => [
            'DOCEN' => [
                "Bienvenido(a), {prof_title} {nombre}.",
                "Qué tal, {prof_title} {nombre}. Adelante.",
                "Buenas tardes, {prof_title} {nombre}.",
            ],
            'INVESTI' => [
                "¡Hola, {nombre}! Éxitos en su búsqueda.",
                "Adelante, {nombre}. Un gusto verle.",
                "Bienvenido(a), {nombre}.",
            ],
            'STAFF' => [
                "¡Habla, {nombre}!",
                "¡Qué tal, {nombre}! ¡Vamos!",
                "¡Hola, {nombre}! Con todo hoy.",
            ],
            'ADMIN' => [
                "Hola, {nombre}. Bienvenido(a).",
                "Adelante, {nombre}.",
                "Bienvenido(a) al sistema, {nombre}.",
            ],
            'VISITA' => [
                "¡Hola, {nombre}! ¡Bienvenido(a)!",
                "Pasa nomás, {nombre}.",
                "¡Qué bueno que viniste, {nombre}!",
            ],
            'ESTUDI' => [
                "¡Habla, {nombre}!",
                "¡Qué tal, {nombre}! Pasa.",
                "¡Adelante, {nombre}!",
                "¡Hola, {nombre}! ¡Con todo!",
                "Pasa, {nombre}. ¡A estudiar!",
            ],
            'DEFAULT' => [
                "¡Hola, {nombre}! Adelante.",
                "Acceso correcto, {nombre}.",
            ],
        ],
        // Mensajes de salida por rol, saludos breves
        'exit' => [
            'DOCEN' => [
                "Hasta luego, {prof_title} {nombre}.",
                "Que tenga una buena tarde, {prof_title} {nombre}.",
                "Le esperamos pronto, {prof_title} {nombre}.",
            ],
            'INVESTI' => [
                "Hasta pronto, {nombre}. Éxitos.",
                "Nos vemos, {nombre}.",
                "Gracias por su visita, {nombre}.",
            ],
            'STAFF' => [
                "¡Nos vemos, {nombre}!",
                "¡Listo por hoy, {nombre}! Cuídate.",
                "¡Hasta mañana, {nombre}!",
            ],
            'ADMIN' => [
                "Hasta luego, {nombre}.",
                "Jornada finalizada, {nombre}.",
                "Nos vemos, {nombre}. Buen trabajo.",
            ],
            'VISITA' => [
                "¡Vuelve pronto, {nombre}!",
                "¡Gracias por la visita, {nombre}!",
                "¡Nos vemos, {nombre}!",
            ],
            'ESTUDI' => [
                "¡Nos vemos, {nombre}!",
                "¡Cuídate, {nombre}!",
                "¡Chau, {nombre}! ¡Éxitos!",
                "¡Listo, {nombre}! A descansar.",
                "¡Hasta la próxima, {nombre}!",
            ],
            'DEFAULT' => [
                "Salida registrada. ¡Nos vemos, {nombre}!",
                "¡Hasta luego, {nombre}!",
            ],
        ],
    ];

    // Pantalla solo para datos, puede estar vacío
    private array $screenTemplates = [
        'not_found' => "",
        'recent_entry' => "",
        'recent_exit' => "",
        'expired' => "",
        'birthday' => "",
        'borrower_note' => "",
        'entry' => [],
        'exit' => [],
    ];

    public function getBothMessages(string $eventType, ?array $userData = null, array $miscData = []): array {
        return [
            'visual' => $this->getScreenMessage($eventType, $userData, $miscData),
            'voice'  => $this->getTTSMessage($eventType, $userData, $miscData),
        ];
    }

    public function getScreenMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        return $this->generateMessage($eventType, $userData, $miscData, $this->screenTemplates);
    }

    public function getTTSMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        return $this->generateMessage($eventType, $userData, $miscData, $this->ttsTemplates, true);
    }

    /**
     * @param bool $avoidRepeat Si es true (TTS), intenta evitar repetir el último saludo usado.
     */
    private function generateMessage(
        string $eventType,
        ?array $userData,
        array $miscData,
        array $templates,
        bool $avoidRepeat = false
    ): string {
        $combinedData = array_merge($userData ?? [], $miscData);

        // Prepara saludo de hora y género
        $combinedData['greeting'] = $this->getTimeGreeting($miscData['current_hour'] ?? (int)date('H'));
        $gender = strtoupper($userData['gender'] ?? '');
        $combinedData['gender_suffix'] = ($gender == 'F') ? 'a' : 'o';
        $combinedData['prof_title'] = ($gender == 'F') ? 'profesora' : 'profesor';

        // Sin usuario
        if (in_array($eventType, ['not_found', 'recent_entry', 'recent_exit'])) {
            return $this->fetchTemplate($templates, $eventType, $eventType, $avoidRepeat);
        }
        if ($userData === null) return "";

        // Cumpleaños
        if ($this->isBirthday($userData['dateofbirth'] ?? null)) {
            return $this->fetchTemplate($templates, 'birthday', 'birthday', $avoidRepeat, $combinedData);
        }
        // Nota de personal
        if (!empty($userData['borrowernotes'])) {
            $combinedData['note'] = $userData['borrowernotes'];
            return $this->fetchTemplate($templates, 'borrower_note', 'borrower_note', $avoidRepeat, $combinedData);
        }
        // Expirado
        if ($eventType === 'expired') {
            return $this->fetchTemplate($templates, 'expired', 'expired', $avoidRepeat, $combinedData);
        }
        // Entrada
        if ($eventType === 'entry') {
            $msg = $this->buildEntryMessage($userData, $templates, $avoidRepeat, $combinedData);
            return $this->replacePlaceholders($msg, $combinedData);
        }
        // Salida
        if ($eventType === 'exit') {
            $msg = $this->buildExitMessage($userData, $templates, $avoidRepeat, $combinedData);
            return $this->replacePlaceholders($msg, $combinedData);
        }
        return '';
    }

    private function buildEntryMessage(array $userData, array $templates, bool $avoidRepeat, array $combinedData): string {
        $valid = ['DOCEN', 'ADMIN', 'ESTUDI', 'STAFF', 'VISITA', 'INVESTI'];
        $category = strtoupper(trim($userData['categorycode'] ?? ''));
        if (!in_array($category, $valid)) {
            $category = 'DEFAULT';
        }
        $tplSet = $templates['entry'] ?? [];
        $candidates = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? [];
        return $this->pickNonRepeated($candidates, "entry_" . $category, $avoidRepeat, $combinedData);
    }

    private function buildExitMessage(array $userData, array $templates, bool $avoidRepeat, array $combinedData): string {
        $valid = ['DOCEN', 'ADMIN', 'ESTUDI', 'STAFF', 'VISITA', 'INVESTI'];
        $category = strtoupper(trim($userData['categorycode'] ?? ''));
        if (!in_array($category, $valid)) {
            $category = 'DEFAULT';
        }
        $tplSet = $templates['exit'] ?? [];
        $candidates = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? [];
        return $this->pickNonRepeated($candidates, "exit_" . $category, $avoidRepeat, $combinedData);
    }

    /**
     * Elige aleatoriamente evitando repetir el último (si hay más de 1 opción)
     */
    private function fetchTemplate(array $templates, string $key, string $sessionKey, bool $avoidRepeat = false, array $data = []): string {
        $tpl = $templates[$key] ?? '';
        if (is_array($tpl) && count($tpl) > 0) {
            return $this->pickNonRepeated($tpl, $sessionKey, $avoidRepeat, $data);
        }
        return $this->replacePlaceholders($tpl, $data);
    }

    /**
     * Elige una frase al azar evitando la última usada (solo si $avoidRepeat).
     * Guarda la frase elegida en $_SESSION['last_tts'][$sessionKey]
     */
    private function pickNonRepeated(array $options, string $sessionKey, bool $avoidRepeat, array $data = []): string {
        if (empty($options)) return ''; // <-- Esto previene el error si no hay opciones
    
        $lastUsed = $_SESSION['last_tts'][$sessionKey] ?? null;
        $available = $options;
        if ($avoidRepeat && count($available) > 1 && $lastUsed) {
            $available = array_values(array_diff($available, [$lastUsed]));
        }
        if (empty($available)) return ''; // <-- También previene si tras filtrar no quedan opciones
    
        $chosen = $available[array_rand($available)];
        if ($avoidRepeat) {
            $_SESSION['last_tts'][$sessionKey] = $chosen;
        }
        return $this->replacePlaceholders($chosen, $data);
    }

    private function getTimeGreeting(int $hour): string {
        if ($hour >= 5 && $hour < 12) {
            $set = $this->timeGreetings['morning'];
        } elseif ($hour >= 12 && $hour < 19) {
            $set = $this->timeGreetings['afternoon'];
        } elseif ($hour >= 19 || $hour < 5) {
            $set = $this->timeGreetings['night'];
        } else {
            $set = $this->timeGreetings['default'];
        }
        return $set[array_rand($set)];
    }

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
            '{greeting}'  => htmlspecialchars($data['greeting'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{gender_suffix}' => htmlspecialchars($data['gender_suffix'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{prof_title}'    => htmlspecialchars($data['prof_title'] ?? '', ENT_QUOTES, 'UTF-8'),
        ];
        $template = str_replace(array_keys($placeholders), array_values($placeholders), $template);
        return preg_replace('/{[^}]+}/', '', $template);
    }

    private function isBirthday(?string $dateOfBirth): bool {
        if (empty($dateOfBirth)) return false;
        $timestamp = strtotime($dateOfBirth);
        if ($timestamp === false) return false;
        return date('m-d') === date('m-d', $timestamp);
    }
}
