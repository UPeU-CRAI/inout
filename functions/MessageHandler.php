<?php

class MessageHandler {
    // Banco centralizado de plantillas de mensajes, organizadas por evento, categoría, género y franja horaria
    private array $templates;

    public function __construct() {
        $this->templates = [
            'entry' => [
                'time_based' => [ // Nuevo nivel para saludos basados en la hora del día
                    'morning' => [
                        'M' => [
                            "¡Buenos días, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenido. Ingreso registrado a las: {time}.",
                            "Que tengas un excelente día, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'F' => [
                            "¡Buenos días, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenida. Ingreso registrado a las: {time}.",
                            "Que tengas un excelente día, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'DEFAULT' => [
                            "¡Buenos días, {nombre}! Bienvenido/a. Entrada a las: {time}.",
                            "Hola, {nombre}. Ingreso registrado a las: {time}.",
                            "Excelente mañana, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ]
                    ],
                    'afternoon' => [
                        'M' => [
                            "¡Buenas tardes, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenido. Ingreso registrado a las: {time}.",
                            "Que tengas una excelente tarde, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'F' => [
                            "¡Buenas tardes, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenida. Ingreso registrado a las: {time}.",
                            "Que tengas una excelente tarde, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'DEFAULT' => [
                            "¡Buenas tardes, {nombre}! Bienvenido/a. Entrada a las: {time}.",
                            "Hola, {nombre}. Ingreso registrado a las: {time}.",
                            "Excelente tarde, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ]
                    ],
                    'night' => [
                        'M' => [
                            "¡Buenas noches, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenido. Ingreso registrado a las: {time}.",
                            "Que tengas una buena noche, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'F' => [
                            "¡Buenas noches, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}.",
                            "¡Hola, {nombre}! Bienvenida. Ingreso registrado a las: {time}.",
                            "Que tengas una buena noche, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ],
                        'DEFAULT' => [
                            "¡Buenas noches, {nombre}! Bienvenido/a. Entrada a las: {time}.",
                            "Hola, {nombre}. Ingreso registrado a las: {time}.",
                            "Excelente noche, {nombre}. Tu USN: {usn}. Entrada: {time}."
                        ]
                    ],
                ],
                // Plantillas por categoría (se usarán si no se activa el saludo por hora, o como fallback)
                'ESTUDI' => [
                    'M' => [
                        "¡Qué tal, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}. Disfruta tu estadía.",
                        "¡Bienvenido, {nombre}! Ingreso a las: {time}. Es un gusto verte.",
                    ],
                    'F' => [
                        "¡Qué tal, {nombre}! Tu USN es: {usn}. Hora de entrada: {time}. Disfruta tu estadía.",
                        "¡Bienvenida, {nombre}! Ingreso a las: {time}. Es un gusto verte.",
                    ],
                    'DEFAULT' => [
                        "¡Hola, {nombre}! Bienvenido/a. Entrada registrada a las: {time}.",
                        "¡Bienvenido/a, {nombre}! Tu USN es: {usn}. Hora de ingreso: {time}.",
                    ]
                ],
                'DOCEN' => [
                    'M' => [
                        "Estimado {titulo} {apellido}, un placer recibirle. Su entrada es a las: {time}.",
                        "Bienvenido, {titulo} {apellido}. Ingreso: {time}. A su disposición.",
                    ],
                    'F' => [
                        "Estimada {titulo} {apellido}, un placer recibirle. Su entrada es a las: {time}.",
                        "Bienvenida, {titulo} {apellido}. Ingreso: {time}. A su disposición.",
                    ],
                    'DEFAULT' => [
                        "Estimado/a {titulo} {apellido}, bienvenido/a. Entrada: {time}.",
                        "Un placer recibirle, {titulo} {apellido}. Ingreso a las: {time}.",
                    ]
                ],
                'INVESTI' => [
                    'M' => [
                        "Estimado Investigador {apellido}, bienvenido. Entrada: {time}. Su USN: {usn}.",
                        "Bienvenido, {nombre} {apellido}. Ingreso: {time}. Éxito en su investigación.",
                    ],
                    'F' => [
                        "Estimada Investigadora {apellido}, bienvenida. Entrada: {time}. Su USN: {usn}.",
                        "Bienvenida, {nombre} {apellido}. Ingreso: {time}. Éxito en su investigación.",
                    ],
                    'DEFAULT' => [
                        "Estimado/a Investigador/a {apellido}, bienvenido/a. Entrada: {time}.",
                        "Bienvenido/a, {nombre} {apellido}. Hora de entrada: {time}.",
                    ]
                ],
                'STAFF' => [
                    'M' => [
                        "¡Hola, colega {nombre}! Qué gusto verte. Entrada: {time}.",
                        "¡Bienvenido, {nombre}! Ingreso a las: {time}. Que tengas un buen turno.",
                    ],
                    'F' => [
                        "¡Hola, colega {nombre}! Qué gusto verte. Entrada: {time}.",
                        "¡Bienvenida, {nombre}! Ingreso a las: {time}. Que tengas un buen turno.",
                    ],
                    'DEFAULT' => [
                        "¡Hola, colega {nombre}! Qué bueno verte. Entrada: {time}.",
                        "¡Qué bueno verte, {nombre}! Ingreso a las: {time}.",
                    ]
                ],
                'ADMIN' => [
                    'M' => [
                        "Saludos, creador {nombre}. Entrada: {time}. El sistema a tu servicio.",
                        "Bienvenido, Super-Usuario {nombre}. Ingreso a las: {time}.",
                    ],
                    'F' => [
                        "Saludos, creadora {nombre}. Entrada: {time}. El sistema a tu servicio.",
                        "Bienvenida, Super-Usuaria {nombre}. Ingreso a las: {time}.",
                    ],
                    'DEFAULT' => [
                        "Saludos, creador/a {nombre}. Entrada: {time}.",
                        "Bienvenido/a, Super-Usuario/a {nombre}. Hora de ingreso: {time}.",
                    ]
                ],
                'VISITA' => [
                    'M' => [
                        "Le damos una cordial bienvenida. Su USN es: {usn}. Hora de entrada: {time}.",
                        "Bienvenido a nuestra biblioteca. Ingreso registrado a las: {time}.",
                    ],
                    'F' => [
                        "Le damos una cordial bienvenida. Su USN es: {usn}. Hora de entrada: {time}.",
                        "Bienvenida a nuestra biblioteca. Ingreso registrado a las: {time}.",
                    ],
                    'DEFAULT' => [
                        "Le damos una cordial bienvenida. Su USN es: {usn}. Hora de entrada: {time}.",
                        "Bienvenido/a a nuestra biblioteca. Ingreso registrado a las: {time}.",
                    ]
                ],
                'DEFAULT' => [
                    'M' => [
                        "¡Hola, {nombre}! Bienvenido. Entrada: {time}. Tu USN: {usn}.",
                        "Bienvenido, {nombre}. Ingreso: {time}.",
                    ],
                    'F' => [
                        "¡Hola, {nombre}! Bienvenida. Entrada: {time}. Tu USN: {usn}.",
                        "Bienvenida, {nombre}. Ingreso: {time}.",
                    ],
                    'DEFAULT' => [
                        "¡Hola, {nombre}! Bienvenido/a. Entrada: {time}.",
                        "Bienvenido/a, {nombre}. Tu USN es: {usn}. Hora de ingreso: {time}.",
                    ]
                ]
            ],
            'exit' => [
                'ESTUDI' => [
                    'M' => [
                        "¡Hasta pronto, {nombre}! Tu duración total fue de: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ],
                    'F' => [
                        "¡Hasta pronto, {nombre}! Tu duración total fue de: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ],
                    'DEFAULT' => [
                        "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ]
                ],
                'DOCEN' => [
                    'M' => [
                        "Hasta pronto, {titulo} {apellido}. Su duración total fue de: {duration}.",
                        "Gracias por su visita, {titulo} {apellido}. Permaneció por: {duration}.",
                    ],
                    'F' => [
                        "Hasta pronto, {titulo} {apellido}. Su duración total fue de: {duration}.",
                        "Gracias por su visita, {titulo} {apellido}. Permaneció por: {duration}.",
                    ],
                    'DEFAULT' => [
                        "Hasta pronto, {titulo} {apellido}. Su duración total fue de: {duration}.",
                        "Gracias por su visita, {titulo} {apellido}. Permaneció por: {duration}.",
                    ]
                ],
                'INVESTI' => [
                    'M' => [
                        "Despedida, Investigador {apellido}. Duración: {duration}.",
                        "Gracias, Investigador {apellido}. Su tiempo fue de: {duration}.",
                    ],
                    'F' => [
                        "Despedida, Investigadora {apellido}. Duración: {duration}.",
                        "Gracias, Investigadora {apellido}. Su tiempo fue de: {duration}.",
                    ],
                    'DEFAULT' => [
                        "Despedida, Investigador/a {apellido}. Duración: {duration}.",
                        "Gracias, Investigador/a {apellido}. Su tiempo fue de: {duration}.",
                    ]
                ],
                'STAFF' => [
                    'M' => [
                        "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.",
                        "Gracias por tu jornada, {nombre}. Tiempo registrado: {duration}.",
                    ],
                    'F' => [
                        "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.",
                        "Gracias por tu jornada, {nombre}. Tiempo registrado: {duration}.",
                    ],
                    'DEFAULT' => [
                        "¡Hasta luego, {nombre}! Que tengas un buen día. Duración: {duration}.",
                        "Gracias por tu jornada, {nombre}. Tiempo registrado: {duration}.",
                    ]
                ],
                'ADMIN' => [
                    'M' => [
                        "Hasta pronto, creador {nombre}. Duración: {duration}.",
                        "Registro de salida para {nombre}. Tiempo total: {duration}.",
                    ],
                    'F' => [
                        "Hasta pronto, creadora {nombre}. Duración: {duration}.",
                        "Registro de salida para {nombre}. Tiempo total: {duration}.",
                    ],
                    'DEFAULT' => [
                        "Hasta pronto, creador/a {nombre}. Duración: {duration}.",
                        "Registro de salida para {nombre}. Tiempo total: {duration}.",
                    ]
                ],
                'VISITA' => [
                    'M' => [
                        "Gracias por visitarnos. Vuelva pronto. Duración total: {duration}.",
                        "Esperamos verte de nuevo. Tu visita duró: {duration}.",
                    ],
                    'F' => [
                        "Gracias por visitarnos. Vuelva pronto. Duración total: {duration}.",
                        "Esperamos verte de nuevo. Tu visita duró: {duration}.",
                    ],
                    'DEFAULT' => [
                        "Gracias por visitarnos. Vuelva pronto. Duración total: {duration}.",
                        "Esperamos verte de nuevo. Tu visita duró: {duration}.",
                    ]
                ],
                'DEFAULT' => [
                    'M' => [
                        "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ],
                    'F' => [
                        "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ],
                    'DEFAULT' => [
                        "¡Hasta pronto, {nombre}! Duración total: {duration}.",
                        "Gracias por tu visita, {nombre}. Has estado con nosotros por: {duration}.",
                    ]
                ]
            ],
            'expired' => [ // Mensaje específico para usuarios con membresía expirada (ingresan como VISITA)
                'M' => [
                    "Atención, {nombre}. Tu membresía ha expirado. Has ingresado como VISITA. Por favor, acércate al mostrador para renovarla.",
                    "Hola, {nombre}. Tu cuenta está inactiva. Se ha registrado tu ingreso como VISITA. Te invitamos a renovar tu membresía.",
                    "Estimado {nombre}, tu membresía ha caducado. Tu acceso se ha registrado como VISITA. Favor regularizar tu situación en recepción."
                ],
                'F' => [
                    "Atención, {nombre}. Tu membresía ha expirado. Has ingresado como VISITA. Por favor, acércate al mostrador para renovarla.",
                    "Hola, {nombre}. Tu cuenta está inactiva. Se ha registrado tu ingreso como VISITA. Te invitamos a renovar tu membresía.",
                    "Estimada {nombre}, tu membresía ha caducado. Tu acceso se ha registrado como VISITA. Favor regularizar tu situación en recepción."
                ],
                'DEFAULT' => [
                    "Atención, {nombre}. Tu membresía ha expirado. Has ingresado como VISITA. Por favor, acércate al mostrador para renovarla.",
                    "Hola, {nombre}. Tu cuenta está inactiva. Se ha registrado tu ingreso como VISITA. Te invitamos a renovar tu membresía.",
                    "Estimado/a {nombre}, tu membresía ha caducado. Tu acceso se ha registrado como VISITA. Favor regularizar tu situación en recepción."
                ]
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
            'birthday' => [
                'M' => [
                    "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.",
                    "¡Un muy feliz cumpleaños, {nombre}! Que tengas un día lleno de alegría y muchas lecturas. ¡Te esperamos!",
                    "El personal de la biblioteca te envía un gran saludo de cumpleaños, {nombre}. ¡Que lo disfrutes mucho!",
                ],
                'F' => [
                    "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.",
                    "¡Un muy feliz cumpleaños, {nombre}! Que tengas un día lleno de alegría y muchas lecturas. ¡Te esperamos!",
                    "El personal de la biblioteca te envía un gran saludo de cumpleaños, {nombre}. ¡Que lo disfrutes mucho!",
                ],
                'DEFAULT' => [
                    "¡Feliz cumpleaños, {nombre}! Todo el equipo de la biblioteca te desea un día maravilloso.",
                    "¡Un muy feliz cumpleaños, {nombre}! Que tengas un día lleno de alegría y muchas lecturas.",
                    "El personal de la biblioteca te envía un gran saludo de cumpleaños, {nombre}. ¡Que lo disfrutes mucho!"
                ]
            ],
            'not_found' => [
                "Código de usuario no reconocido. Por favor, intente de nuevo o consulte con el personal.",
                "Usuario no encontrado en nuestra base de datos. Verifique su código o contacte a un asistente.",
                "El código escaneado no corresponde a ningún usuario registrado. ¿Necesita ayuda?",
            ],
            'borrowernotes' => [
                "{note}",
                "Un mensaje importante del personal: {note}",
                "Para su atención, {nombre}: {note}"
            ]
        ];
    }

    /**
     * Punto de entrada principal para generar un mensaje.
     * @param string $eventType - El evento que ocurre ('entry', 'exit', 'birthday', 'expired', 'not_found', etc.).
     * @param array|null $userData - Array asociativo con datos del usuario. Debe incluir 'gender' si se quiere distinguir.
     * @param array $miscData - Array asociativo para datos adicionales como 'time', 'duration', 'current_hour' etc.
     * @return string
     */
    public function getMessage(string $eventType, ?array $userData = null, array $miscData = []): string {
        // Unir userData y miscData para que todos los placeholders estén disponibles
        $combinedData = array_merge($userData ?? [], $miscData);

        // Prioridad 1: Mensaje de cumpleaños (siempre predomina sobre otros mensajes)
        if ($userData && isset($userData['dateofbirth']) && $this->isBirthday($userData['dateofbirth'])) {
            return $this->replacePlaceholders($this->getGenderedTemplate('birthday', $userData), $combinedData);
        }
        
        // Prioridad 2: Mensaje de "no encontrado" (no depende de userData, es un estado global)
        if ($eventType === 'not_found') {
            return $this->getRandomTemplate('not_found'); 
        }

        // Prioridad 3: Mensajes de acciones recientes (no dependen de categoría/género, son directos)
        if (in_array($eventType, ['recent_entry', 'recent_exit'])) {
            return $this->getRandomTemplate($eventType); // Usar getRandomTemplate para estas plantillas planas
        }

        // Si se espera userData pero no se proporciona (para eventos que requieren un usuario y no son los de arriba)
        if ($userData === null) {
            return "Error interno: Se esperaba información del usuario para este evento.";
        }

        // Prioridad 4: Mostrar notas del prestatario si existen (ahora después de cumpleaños y mensajes directos)
        if (!empty($userData['borrowernotes'])) {
            return $this->replacePlaceholders($this->getGenderedTemplate('borrowernotes', $userData), $combinedData);
        }
        
        // Prioridad 5: Mensaje de usuario expirado
        if ($eventType === 'expired') {
            return $this->replacePlaceholders($this->getGenderedTemplate('expired', $userData), $combinedData);
        }

        // Prioridad 6: Mensajes de 'entry' y 'exit' (normales con lógica de franja horaria o categoría/género)
        if ($eventType === 'entry') {
            // Intentar usar saludo por franja horaria para 'entry'
            $currentHour = $miscData['current_hour'] ?? null;
            if ($currentHour !== null) {
                $timeOfDay = $this->getTimeOfDay($currentHour);
                if (isset($this->templates['entry']['time_based'][$timeOfDay])) {
                    return $this->replacePlaceholders($this->getGenderedTemplate('entry', $userData, $timeOfDay), $combinedData);
                }
            }
        }
        
        // Si no se usó el saludo por hora (para 'entry'), o para 'exit' y otros eventos que usan categoría/género
        $template = $this->getGenderedTemplate($eventType, $userData);
        
        return $this->replacePlaceholders($template, $combinedData);
    }

    /**
     * Determina la franja horaria del día.
     * @param int $hour La hora actual (0-23).
     * @return string 'morning', 'afternoon', 'night' o 'default'.
     */
    private function getTimeOfDay(int $hour): string {
        if ($hour >= 5 && $hour < 12) {
            return 'morning'; // 5 AM - 11:59 AM
        } elseif ($hour >= 12 && $hour < 19) {
            return 'afternoon'; // 12 PM - 6:59 PM
        } elseif ($hour >= 19 || $hour < 5) {
            return 'night'; // 7 PM - 4:59 AM
        }
        return 'default'; // Fallback
    }

    /**
     * Selecciona la plantilla de mensaje adecuada basada en el evento, la categoría del usuario y su género,
     * o la franja horaria para el evento 'entry'.
     * @param string $eventType
     * @param array $userData
     * @param string|null $timeOfDay Opcional, para la franja horaria específica.
     * @return string
     */
    private function getGenderedTemplate(string $eventType, array $userData, ?string $timeOfDay = null): string {
        $category = $userData['categorycode'] ?? 'DEFAULT';
        $gender = strtoupper($userData['gender'] ?? 'DEFAULT'); 
        
        // Manejar el caso de 'entry' con saludo por franja horaria
        if ($eventType === 'entry' && $timeOfDay !== null && isset($this->templates['entry']['time_based'][$timeOfDay])) {
            $templatesForTime = $this->templates['entry']['time_based'][$timeOfDay];
            // Intentar obtener por género, si no, usar DEFAULT
            $genderTemplates = $templatesForTime[$gender] ?? $templatesForTime['DEFAULT'] ?? [];
            if (is_array($genderTemplates) && !empty($genderTemplates)) {
                return $genderTemplates[array_rand($genderTemplates)];
            }
        }

        // Para otros eventos o si no se usa saludo por hora, seguir con la lógica de categoría y género
        $eventSpecificTemplates = $this->templates[$eventType] ?? null;

        // Si la plantilla específica del evento no es un array o es una cadena directa, la devuelve.
        // Esto es un fallback, pero para plantillas como 'recent_entry'/'recent_exit' es mejor usar getRandomTemplate directamente en getMessage.
        if (!is_array($eventSpecificTemplates)) {
            return is_string($eventSpecificTemplates) ? $eventSpecificTemplates : ''; 
        }

        // Si el evento tiene categorías anidadas (como 'entry' por categoría, 'exit', 'expired', 'birthday', 'borrowernotes')
        $categorySpecificTemplates = $eventSpecificTemplates[$category] ?? null;

        // Fallback a 'DEFAULT' de evento si no hay categoría específica, o si la categoría es un array pero no tiene género/DEFAULT
        if ($categorySpecificTemplates === null || (is_array($categorySpecificTemplates) && !isset($categorySpecificTemplates[$gender]) && !isset($categorySpecificTemplates['DEFAULT']))) {
            $categorySpecificTemplates = $eventSpecificTemplates['DEFAULT'] ?? [];
        }

        $genderSpecificTemplates = $categorySpecificTemplates[$gender] ?? $categorySpecificTemplates['DEFAULT'] ?? [];
        
        if (is_array($genderSpecificTemplates) && !empty($genderSpecificTemplates)) {
            return $genderSpecificTemplates[array_rand($genderSpecificTemplates)];
        } elseif (is_string($genderSpecificTemplates)) {
            return $genderSpecificTemplates; 
        }

        return ''; 
    }
    
    /**
     * Obtiene una plantilla aleatoria de un tipo de evento sin distinción de categoría o género.
     * Utilizado para mensajes globales o cuando la especificidad no es necesaria/disponible.
     * @param string $eventType
     * @return string
     */
    private function getRandomTemplate(string $eventType): string {
        $templates = $this->templates[$eventType] ?? [];
        
        if (isset($templates[0]) && is_string($templates[0])) { // Es un array de strings directos (como 'not_found', 'recent_entry', 'recent_exit')
            return $templates[array_rand($templates)];
        } 
        // Si las plantillas están anidadas (por género, o categoría y género), intenta obtener de 'DEFAULT'
        elseif (isset($templates['DEFAULT'])) {
            $defaultTemplates = $templates['DEFAULT'];
            if (is_array($defaultTemplates) && !empty($defaultTemplates)) {
                // Si el 'DEFAULT' en este nivel tiene sub-niveles de género (ej. para 'birthday' o 'borrowernotes' con un DEFAULT general)
                if (isset($defaultTemplates['DEFAULT']) && is_array($defaultTemplates['DEFAULT'])) {
                    return $defaultTemplates['DEFAULT'][array_rand($defaultTemplates['DEFAULT'])];
                }
                // Si el 'DEFAULT' es directamente un array de opciones (ej. 'DEFAULT' de categoría es un array de mensajes)
                return $defaultTemplates[array_rand($defaultTemplates)];
            } elseif (is_string($defaultTemplates)) { // Si DEFAULT es un string simple
                return $defaultTemplates;
            }
        }
        // Si es una cadena simple directamente bajo el evento (caso menos común para estas plantillas, pero como fallback)
        elseif (is_string($templates)) {
            return $templates;
        }
        return ''; 
    }

    /**
     * Reemplaza los placeholders en la plantilla con los datos proporcionados.
     * @param string $template La plantilla de mensaje con placeholders.
     * @param array $data Los datos para reemplazar los placeholders.
     * @return string La plantilla con los placeholders reemplazados.
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
            '{label}'        => htmlspecialchars($data['label'] ?? '', ENT_QUOTES, 'UTF-8'), 
            '{time}'         => htmlspecialchars($data['time'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{duration}'     => htmlspecialchars($data['duration'] ?? '', ENT_QUOTES, 'UTF-8'),
            '{note}'         => htmlspecialchars($data['note'] ?? '', ENT_QUOTES, 'UTF-8'),
        ];

        $template = str_replace(array_keys($placeholders), array_values($placeholders), $template);
        return preg_replace('/{[^}]+}/', '', $template);
    }

    /**
     * Verifica si la fecha de nacimiento coincide con el día actual.
     * @param string|null $dateOfBirth La fecha de nacimiento del usuario.
     * @return bool True si es el cumpleaños, false en caso contrario o si la fecha es inválida.
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
