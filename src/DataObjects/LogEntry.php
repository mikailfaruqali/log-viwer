<?php

namespace Snawbar\LogViewer\DataObjects;

class LogEntry
{
    public function __construct(
        public readonly string $timestamp,
        public readonly string $environment,
        public readonly string $level,
        public readonly string $message,
        public readonly string $extra = ''
    ) {}

    public function hasStackTrace(): bool
    {
        return $this->extra !== '' && $this->extra !== '0';
    }

    public function getFormattedTimestamp(): string
    {
        return date('M j, Y g:i:s A', strtotime($this->timestamp));
    }

    public function getLevelCssClass(): string
    {
        return match ($this->level) {
            'debug' => 'level-debug',
            'info', 'notice' => 'level-info',
            'warning' => 'level-warning',
            'error', 'critical', 'alert' => 'level-error',
            'emergency' => 'level-emergency',
            default => 'level-debug'
        };
    }

    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical', 'alert', 'emergency']);
    }
}
