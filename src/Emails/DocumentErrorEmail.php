<?php

namespace Moloni\Emails;

class DocumentErrorEmail extends SendEmail
{
    public function handle(): void
    {
        $orderId = $this->data["order_id"] ?? 0;

        $this->msg = <<<HTML
        <html>
        <head>
            <title>Moloni document error</title>
        </head>
        <body>
            <p>An error occurred creating an Moloni document.</p>
            <p>Please check plugin logs for more information.</p>
            <br>
            <p>Order ID: $orderId</p>
        </body>
        </html>
HTML;

        $this->sendEmail();
    }
}
