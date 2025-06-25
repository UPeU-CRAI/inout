<?php
class MessageHandler {
    private array $templates;

    public function __construct() {
        $this->templates = [
            'entry'        => "<span class='text-primary'>Your {label} is: {usn}<br>Entry time is: {time}</span>",
            'exit'         => "<span class='text-success'>Your Exit time is: {time}<br><span class='text-warning'>Total Time Duration : {duration}</span>",
            'expired'      => "<span class='text-danger'>Invalid or Expired {label}<br> Contact Librarian for more details.</span>",
            'recent_entry' => "<span class='text-warning'>You just Checked In.<br> Wait for 10 Seconds to Check Out.</span>",
            'recent_exit'  => "<span class='text-info'>You just Checked Out.<br> Wait for 10 Seconds to Check In.</span>",
            'birthday'     => "<span class='text-success'>Happy Birthday {name}!</span>",
            'not_found'    => "<span class='text-danger'>User not found</span>"
        ];
    }

    public function getMessage(string $eventType, ?array $userData = null): string {
        $userData = $userData ?? [];

        if (!empty($userData['borrowernotes'])) {
            return htmlspecialchars($userData['borrowernotes'], ENT_QUOTES, 'UTF-8');
        }

        if (isset($userData['dateofbirth']) && $this->isBirthday($userData['dateofbirth'])) {
            return $this->replacePlaceholders($this->templates['birthday'], $userData);
        }

        if (isset($this->templates[$eventType])) {
            return $this->replacePlaceholders($this->templates[$eventType], $userData);
        }

        return '';
    }

    private function replacePlaceholders(string $template, array $data): string {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $template);
        }

        return preg_replace('/{[^}]+}/', '', $template);
    }

    private function isBirthday(?string $dateOfBirth): bool {
        if (!$dateOfBirth) {
            return false;
        }

        $timestamp = strtotime($dateOfBirth);
        if ($timestamp === false) {
            return false;
        }

        return date('m-d') === date('m-d', $timestamp);
    }
}
?>
