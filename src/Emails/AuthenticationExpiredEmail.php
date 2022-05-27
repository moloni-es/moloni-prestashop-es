<?php

namespace Moloni\Emails;

class AuthenticationExpiredEmail extends SendEmail
{
    public function handle(): void
    {
        $payload = json_encode($this->data);

        $this->msg = <<<HTML
        <html>
        <head>
            <title>The Moloni authentication expired</title>
        </head>
        <body>
            <p>Please insert your creadentials again.</p>
            <br>
            <p>Payload:</p>
            <pre>
                $payload
            </pre>
        </body>
        </html>
HTML;

        $this->sendEmail();
    }
}
