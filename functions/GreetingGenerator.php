<?php

class GreetingGenerator {
    private $userName;
    private $userRole;
    private $currentTime;
    private $eventType;
    private $greetingText;

    public function __construct($userName, $userRole, $currentTime, $eventType) {
        $this->userName = $userName;
        $this->userRole = $userRole;
        $this->currentTime = $currentTime;
        $this->eventType = $eventType;
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

        // Capitalize the first letter of the role
        $capitalizedRole = ucfirst(strtolower($this->userRole));

        $this->greetingText = sprintf(
            "%s, %s %s. %s.",
            $timeOfDayGreeting,
            $capitalizedRole,
            $this->userName,
            $actionGreeting
        );
    }

    public function getGreetingText() {
        $this->generate();
        return $this->greetingText;
    }
}
?>
