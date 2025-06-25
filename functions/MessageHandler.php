<?php
class MessageHandler {
    private $templates;

    public function __construct() {
        $this->templates = [
            '1' => "<span class='text-primary'>Your {label} is: {usn}<br>Entry time is: {time}</span>",
            '2' => "<span class='text-warning'>You just Checked In.<br> Wait for 10 Seconds to Check Out.</span>",
            '3' => "<span class='text-danger'>Invalid or Expired {label}<br> Contact Librarian for more details.</span>",
            '4' => "<span class='text-success'>Your Exit time is: {time}<br><span class='text-warning'>Total Time Duration : {duration}</span>",
            '5' => "<span class='text-info'>You just Checked Out.<br> Wait for 10 Seconds to Check In.</span>"
        ];
    }

    public function getMessage($code, array $data = []) {
        if (!isset($this->templates[$code])) {
            return '';
        }
        $message = $this->templates[$code];
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $message);
        }
        // Remove any unreplaced placeholders
        return preg_replace('/{[^}]+}/', '', $message);
    }
}
?>
