<?php

namespace Moloni\Emails;

class DocumentWarningEmail extends SendEmail
{
    public function handle(): void
    {
        $orderId = $this->data["order_id"] ?? 0;

        $this->msg = <<<HTML
        <html>
        <head>
            <title>Moloni document warning</title>
        </head>
        <body>
            <p>A document was created as draft because the order and document values do not match.</p>
            <p>Please check the resulting document.</p>
            <br>
            <p>Order ID: $orderId</p>
        </body>
        </html>
HTML;

        $this->sendEmail();
    }
}
