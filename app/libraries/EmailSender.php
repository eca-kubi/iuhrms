<?php

/**
 *  Sends emails asynchronously using php threads
 */
class EmailSender extends Thread {
    private string $to;
    private string $subject;
    private string $message;

    public function __construct($to, $subject, $message) {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     *
     * @throws Exception
     *
     */
    public function run() {
        // Create new email model from the data
        $email = EmailModel::factory([
            EmailModelSchema::RECIPIENT_ADDRESS => $this->to,
            EmailModelSchema::SUBJECT => $this->subject,
            EmailModelSchema::BODY => $this->message
        ]);
        // Send the email
        Helpers::send_email($email);
    }
}
