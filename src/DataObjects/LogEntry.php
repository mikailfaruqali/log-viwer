<?php

declare(strict_types=1);

namespace Snawbar\LogViewer\DataObjects;

readonly class LogEntry
{
    /**
     * @param  array<string,string>  $context       Short scalar values shown as badges
     * @param  array<string,mixed>   $longContext    Long/complex values shown expanded (e.g. request input, nested arrays)
     * @param  string                $stackTrace     Raw stack trace text
     */
    public function __construct(
        public string $timestamp,
        public string $environment,
        public string $level,
        public string $message,
        public string $extra = '',
        public array $context = [],
        public array $longContext = [],
        public string $stackTrace = '',
    ) {}

    public function hasStackTrace(): bool
    {
        return $this->stackTrace !== '' && $this->stackTrace !== '0';
    }

    public function hasExtra(): bool
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
            return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $highlighted = preg_replace(
            sprintf('/(%s)/i', preg_quote(mb_trim(htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8')), '/')),
            '<mark class="search-highlight">$1</mark>',
            $escaped
        );

        return $highlighted ?: $escaped;
    }

    public function hasContext(): bool
    {
        return $this->context !== [];
    }

    public function hasLongContext(): bool
    {
        return $this->longContext !== [];
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getLongContext(): array
    {
        return $this->longContext;
    }
}
