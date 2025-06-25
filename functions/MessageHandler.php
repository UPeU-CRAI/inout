<?php
class MessageHandler {
    private $templates;

    public function __construct() {
        $this->templates = [
            'entry'        => "<span class='text-primary'>Your {label} is: {usn}<br>Entry time is: {time}</span>",
            'recent_entry' => "<span class='text-warning'>You just Checked In.<br> Wait for 10 Seconds to Check Out.</span>",
            'expired'      => "<span class='text-danger'>Invalid or Expired {label}<br> Contact Librarian for more details.</span>",
            'exit'         => "<span class='text-success'>Your Exit time is: {time}<br><span class='text-warning'>Total Time Duration : {duration}</span>",
            'recent_exit'  => "<span class='text-info'>You just Checked Out.<br> Wait for 10 Seconds to Check In.</span>"
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
