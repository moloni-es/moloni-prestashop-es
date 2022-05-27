<?php

namespace Moloni\Emails;

class DocumentWarningEmail extends SendEmail
{
    public function handle(): void
    {
        // todo: this
        $this->subject = '';
        $this->msg = '';

        $this->sendEmail();
    }
}
