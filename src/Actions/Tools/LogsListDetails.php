<?php

namespace Moloni\Actions\Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogsListDetails
{
    private $logs;

    public function __construct(?array $logs = [])
    {
        $this->logs = $logs;
    }

    public function handle(): array
    {
        if (empty($this->logs)) {
            return $this->logs;
        }

        foreach ($this->logs as &$log) {
            $log['message'] = json_decode($log['message'], true);
            $log['extra'] = json_decode($log['extra'], true);
        }

        return $this->logs;
    }
}
