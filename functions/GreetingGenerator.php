<?php

class GreetingGenerator {
    private $userName;
    private $userRole;
    private $currentTime;
    private $eventType;
    private $birthDate; // Nueva propiedad
    private $greetingText;

    // Constructor actualizado
    public function __construct($userData) {
        $this->userName = isset($userData['name']) ? $userData['name'] : 'Usuario Desconocido';
        $this->userRole = isset($userData['role']) ? $userData['role'] : 'Usuario';
        $this->currentTime = isset($userData['currentTime']) ? $userData['currentTime'] : date('H:i');
        $this->eventType = isset($userData['eventType']) ? $userData['eventType'] : 'entry';
        $this->birthDate = isset($userData['birthDate']) ? $userData['birthDate'] : null; // Almacenar fecha de nacimiento
    }

    private function generate() {
        $hour = (int)substr($this->currentTime, 0, 2);
        $timeOfDayGreeting = "";

        if ($hour >= 0 && $hour < 12) {
            $timeOfDayGreeting = "Buenos días";
        } elseif ($hour >= 12 && $hour < 20) {
            $timeOfDayGreeting = "Buenas tardes";
        } else {
            $timeOfDayGreeting = "Buenas noches";
        }

        $actionGreeting = ($this->eventType === 'entry') ? "Bienvenido" : "Hasta pronto";

        // Capitalizar el rol como antes
        $capitalizedRole = ucfirst(strtolower($this->userRole));

        $this->greetingText = sprintf(
            "%s, %s %s. %s.",
            $timeOfDayGreeting,
            $capitalizedRole,
            $this->userName,
            $actionGreeting
        );

        // Lógica de cumpleaños
        if ($this->birthDate) {
            $currentDate = date('m-d');
            // Extraer mes y día de la fecha de nacimiento. Asegurarse que no sea null.
            $birthDateMonthDay = date('m-d', strtotime($this->birthDate));

            if ($currentDate === $birthDateMonthDay) {
                $this->greetingText .= " ¡Feliz cumpleaños!";
            }
        }
    }

    public function getGreetingText() {
        $this->generate(); // Asegura que el saludo (y el de cumpleaños) se genere
        return $this->greetingText;
    }
}

?>
