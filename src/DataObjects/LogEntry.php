<?php

declare(strict_types=1);

namespace Snawbar\LogViewer\DataObjects;

readonly class LogEntry
{
    public function __construct(
        public string $timestamp,
        public string $environment,
        public string $level,
        public string $message,
        public string $extra = '',
        public array $context = [],
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
            'info', 'notice' => 'level-info',
            'warning' => 'level-warning',
            'error', 'critical', 'alert' => 'level-error',
            'emergency' => 'level-emergency',
            default => 'level-debug',
        };
    }

    public function isError(): bool
    {
        return in_array($this->level, ['error', 'critical', 'alert', 'emergency'], TRUE);
    }

    public function highlightSearchTerm(string $text, ?string $searchTerm): string
    {
        if (! $searchTerm || mb_trim($searchTerm) === '') {
            return $text;
        }

        $highlighted = preg_replace(
            sprintf('/(%s)/i', preg_quote(mb_trim($searchTerm), '/')),
            '<span class="search-highlight">$1</span>',
            $text
        );

        return $highlighted ?: $text;
    }

    public function hasContext(): bool
    {
        return $this->context !== [];
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
