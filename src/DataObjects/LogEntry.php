<?php

namespace Snawbar\LogViewer\DataObjects;

class LogEntry
{
    public string $timestamp;

    public string $environment;

    public string $level;

    public string $message;

    public string $extra;

    public function __construct(
        string $timestamp,
        string $environment,
        string $level,
        string $message,
        string $extra = ''
    ) {
        $this->timestamp = $timestamp;
        $this->environment = $environment;
        $this->level = $level;
        $this->message = $message;
        $this->extra = $extra;
    }

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
        switch ($this->level) {
            case 'debug':
            default:
                return 'level-debug';
            case 'info':
            case 'notice':
                return 'level-info';
            case 'warning':
                return 'level-warning';
            case 'error':
            case 'critical':
            case 'alert':
                return 'level-error';
            case 'emergency':
                return 'level-emergency';
        }
    }

    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical', 'alert', 'emergency']);
    }

    public function highlightSearchTerm(string $text, ?string $searchTerm): string
    {
        if (! $searchTerm || trim($searchTerm) === '') {
            return $text;
        }

        $searchTerm = trim($searchTerm);
        $highlighted = preg_replace(
            sprintf('/(%s)/i', preg_quote($searchTerm, '/')),
            '<span class="search-highlight">$1</span>',
            $text
        );

        return $highlighted ?: $text;
    }
}
