<?php

class MessageHandler {
    private array $timeGreetings = [
        'morning'   => ['¡Buenos días', 'Muy buenos días', 'Saludos matutinos'],
        'afternoon' => ['¡Buenas tardes', 'Que tenga una excelente tarde', 'Saludos cordiales esta tarde'],
        'night'     => ['¡Buenas noches', 'Le deseamos una noche tranquila', 'Saludos en esta hermosa noche'],
        'default'   => ['Saludos', 'Hola'],
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
            "{greeting}, {nombre}. Le informamos que su membresía ha expirado. Ha sido registrado como visitante. Consulte con el personal para renovaciones.",
            "{greeting}, {nombre}. Su matrícula está vencida. Acceso como visitante autorizado solo por hoy.",
            "{greeting}, {nombre}. Matrícula caducada. Por favor acérquese a informes para más información.",
        ],
        'birthday' => [
            "¡Feliz cumpleaños, {nombre}! Que su día esté lleno de éxitos y buenos momentos en el CRAI.",
            "Celebramos junto a usted, {nombre}, un año más de vida. ¡Muchas felicidades!",
            "¡Enhorabuena, {nombre}! El equipo del CRAI le desea un cumpleaños inolvidable.",
        ],
        'borrower_note' => [
            "Atención, {nombre}. Tiene un mensaje especial: {note}",
            "Mensaje para usted, {nombre}: {note}",
            "El personal desea informarle: {note}",
        ],
        // Entradas personalizadas por rol, formales y amables
        'entry' => [
            'DOCEN' => [
                "{greeting}, {prof_title} {nombre}. Le damos la más cordial bienvenida al CRAI.",
                "{greeting}, {prof_title} {nombre}. Esperamos que su jornada sea productiva.",
                "{greeting}, {prof_title} {nombre}. El conocimiento está de fiesta con su presencia.",
                "{greeting}, {prof_title} {nombre}. ¡Siempre es un honor recibirle!",
            ],
            'INVESTI' => [
                "{greeting}, investigador{gender_suffix} {nombre}. Le deseamos descubrimientos fructíferos.",
                "{greeting}, {nombre}. El CRAI está a su disposición para avanzar en su investigación.",
                "{greeting}, investigador{gender_suffix} {nombre}. ¡Las mejores ideas surgen aquí!",
            ],
            'STAFF' => [
                "{greeting}, colega {nombre}. Gracias por formar parte de este gran equipo.",
                "{greeting}, {nombre}. Su dedicación hace la diferencia.",
                "{greeting}, {nombre}. ¡A trabajar con energía positiva!",
            ],
            'ADMIN' => [
                "{greeting}, administrador{gender_suffix} {nombre}. Todo está listo para gestionar un gran día.",
                "{greeting}, {nombre}. Le damos la bienvenida al panel de administración.",
                "{greeting}, administrador{gender_suffix} {nombre}. Comencemos con éxito la jornada.",
            ],
            'VISITA' => [
                "{greeting}, visitante. Esperamos que disfrute plenamente su visita.",
                "{greeting}. Ha sido registrado como visitante. ¡Bienvenido!",
                "{greeting}, visitante. Gracias por elegirnos.",
            ],
            'ESTUDI' => [
                "{greeting}, {nombre}. El CRAI está a tu servicio para aprender y crecer.",
                "{greeting}, {nombre}. Te deseamos una jornada llena de éxitos académicos.",
                "{greeting}, {nombre}. ¡Aprovecha tu día de estudio al máximo!",
            ],
            'DEFAULT' => [
                "{greeting}, {nombre}. Acceso exitoso. ¡Adelante!",
                "{greeting}, {nombre}. Registro correcto, le deseamos lo mejor.",
            ],
        ],
        'exit' => [
            'DOCEN' => [
                "Le agradecemos su visita, {prof_title} {nombre}. ¡Hasta la próxima!",
                "Salida registrada. {prof_title} {nombre}, ha sido un honor recibirle.",
                "Que tenga un excelente resto del día, {prof_title} {nombre}.",
            ],
            'INVESTI' => [
                "Gracias por su visita, investigador{gender_suffix} {nombre}. Le esperamos pronto.",
                "Salida anotada. ¡Siga adelante con sus descubrimientos, {nombre}!",
            ],
            'STAFF' => [
                "¡Buen trabajo hoy, {nombre}! Que tenga un merecido descanso.",
                "Gracias por su dedicación, {nombre}. ¡Nos vemos en la próxima jornada!",
            ],
            'ADMIN' => [
                "Cierre de sesión completado. Hasta pronto, administrador{gender_suffix} {nombre}.",
                "Le esperamos nuevamente, {nombre}. Gestión finalizada.",
            ],
            'VISITA' => [
                "Gracias por preferirnos. Esperamos verle nuevamente.",
                "Salida registrada como visitante. ¡Buen camino!",
            ],
            'ESTUDI' => [
                "Hasta luego, {nombre}. ¡Sigue adelante con tus estudios!",
                "Registro de salida exitoso. ¡Te esperamos pronto, {nombre}!",
            ],
            'DEFAULT' => [
                "Salida registrada correctamente. ¡Hasta la próxima!",
                "Gracias por su visita. ¡Le esperamos de nuevo!",
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
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');
        $tplSet = $templates['entry'] ?? [];
        $candidates = $tplSet[$category] ?? $tplSet['DEFAULT'] ?? [];
        return $this->pickNonRepeated($candidates, "entry_" . $category, $avoidRepeat, $combinedData);
    }

    private function buildExitMessage(array $userData, array $templates, bool $avoidRepeat, array $combinedData): string {
        $category = strtoupper($userData['categorycode'] ?? 'DEFAULT');
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
        $lastUsed = $_SESSION['last_tts'][$sessionKey] ?? null;
        $available = $options;
        if ($avoidRepeat && count($available) > 1 && $lastUsed) {
            $available = array_values(array_diff($available, [$lastUsed]));
        }
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
