<?php

namespace Moloni\Emails;

abstract class SendEmail
{
    protected $email;
    protected $data;

    protected $subject;
    protected $msg;

    /**
     * Construct
     *
     * @param string|null $email
     * @param array|null $data
     */
    public function __construct(?string $email = '', ?array $data = [])
    {
        $this->email = $email;
        $this->data = $data;
    }

    protected function sendEmail(): void
    {
        if (empty($this->email)) {
            return;
        }

        mail($this->email, $this->subject, $this->msg);
    }
}
