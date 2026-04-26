<?php

declare(strict_types=1);

namespace Snawbar\LogViewer\Services;

use Illuminate\Support\Collection;
use Snawbar\LogViewer\DataObjects\LogEntry;

class LogParserService
{
    protected const string LOG_REGEX = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](?:.*?(\w+)\.)?(\w+): (.*)/';

    protected const string DEFAULT_ENVIRONMENT = 'production';

    public function __construct(
        protected readonly LogFileService $logFileService,
    ) {}

    public function parseLogFile(string $filename, ?string $searchTerm = NULL): Collection
    {
        $content = $this->logFileService->getLogFileContent($filename);

        return $this->parseLogContent($content, $searchTerm);
    }

    public function parseLogContent(string $content, ?string $searchTerm = NULL): Collection
    {
        if (in_array(mb_trim($content), ['', '0'], TRUE)) {
            return collect();
        }

        $lines = explode("\n", $content);
        $entries = collect();
        $currentEntry = NULL;

        foreach ($lines as $line) {
            if ($this->isJsonLogEntry($line)) {
                if ($currentEntry !== NULL) {
                    $logEntry = $this->createLogEntry($currentEntry);
                    if ($this->matchesSearchTerm($logEntry, $searchTerm)) {
                        $entries->push($logEntry);
                    }

                    $currentEntry = NULL;
                }

                $jsonEntry = $this->parseJsonLogEntry($line);
                if ($jsonEntry !== NULL) {
                    $logEntry = $this->createLogEntry($jsonEntry);
                    if ($this->matchesSearchTerm($logEntry, $searchTerm)) {
                        $entries->push($logEntry);
                    }
                }
            } elseif ($this->isLogEntryStart($line)) {
                if ($currentEntry !== NULL) {
                    $logEntry = $this->createLogEntry($currentEntry);
                    if ($this->matchesSearchTerm($logEntry, $searchTerm)) {
                        $entries->push($logEntry);
                    }
                }

                $currentEntry = $this->parseLogEntryHeader($line);
            } elseif ($currentEntry !== NULL && mb_trim($line) !== '' && mb_trim($line) !== '0') {
                $currentEntry['extra'] .= $line . "\n";
            }
        }

        if ($currentEntry !== NULL) {
            $logEntry = $this->createLogEntry($currentEntry);
            if ($this->matchesSearchTerm($logEntry, $searchTerm)) {
                $entries->push($logEntry);
            }
        }

        return $entries->reverse()->values();
    }

    protected function isLogEntryStart(string $line): bool
    {
        return preg_match(self::LOG_REGEX, $line) === 1;
    }

    protected function parseLogEntryHeader(string $line): array
    {
        preg_match(self::LOG_REGEX, $line, $matches);

        $message = $matches[4] ?? '';

        return [
            'timestamp' => $matches[1] ?? '',
            'environment' => $matches[2] ?? self::DEFAULT_ENVIRONMENT,
            'level' => mb_strtolower($matches[3] ?? 'info'),
            'message' => $message,
            'extra' => '',
            'context' => $this->extractContext($message),
        ];
    }

    protected function createLogEntry(array $data): LogEntry
    {
        return new LogEntry(
            timestamp: $data['timestamp'],
            environment: $data['environment'],
            level: $data['level'],
            message: $data['message'],
            extra: mb_trim($data['extra']),
            context: $data['context'] ?? [],
        );
    }

    protected function isJsonLogEntry(string $line): bool
    {
        $trimmed = mb_ltrim($line);

        return $trimmed !== '' && $trimmed[0] === '{';
    }

    protected function parseJsonLogEntry(string $line): ?array
    {
        $decoded = json_decode(mb_trim($line), TRUE);

        if (! is_array($decoded) || ! isset($decoded['message'], $decoded['level_name'])) {
            return NULL;
        }

        return [
            'timestamp' => $this->normalizeJsonTimestamp($decoded['datetime'] ?? ''),
            'environment' => $decoded['channel'] ?? self::DEFAULT_ENVIRONMENT,
            'level' => mb_strtolower($decoded['level_name']),
            'message' => $decoded['message'],
            'extra' => $this->buildJsonExtra($decoded),
            'context' => $this->extractJsonContextBadges($decoded['context'] ?? []),
        ];
    }

    protected function normalizeJsonTimestamp(string $datetime): string
    {
        if ($datetime === '') {
            return '';
        }

        $ts = strtotime($datetime);

        return $ts !== FALSE ? date('Y-m-d H:i:s', $ts) : $datetime;
    }

    protected function extractJsonContextBadges(array $context): array
    {
        $skip = ['exception', 'input'];

        return array_map(
            static fn (mixed $v): string => (string) $v,
            array_filter(
                array_diff_key($context, array_flip($skip)),
                static fn (mixed $v): bool => is_scalar($v) || $v === NULL,
            ),
        );
    }

    protected function buildJsonExtra(array $decoded): string
    {
        $parts = [];
        $context = $decoded['context'] ?? [];

        if (isset($context['exception']) && is_array($context['exception'])) {
            $exc = $context['exception'];
            $lines = [];

            if (isset($exc['class'])) {
                $lines[] = $exc['class'] . ': ' . ($exc['message'] ?? '');
            }

            if (isset($exc['file'])) {
                $lines[] = 'at ' . $exc['file'];
            }

            if (isset($exc['previous']) && is_array($exc['previous'])) {
                $prev = $exc['previous'];
                $lines[] = "\nCaused by: " . ($prev['class'] ?? '') . ': ' . ($prev['message'] ?? '');

                if (isset($prev['file'])) {
                    $lines[] = 'at ' . $prev['file'];
                }
            }

            if ($lines !== []) {
                $parts[] = implode("\n", $lines);
            }
        }

        if (isset($context['input']) && is_array($context['input']) && $context['input'] !== []) {
            $parts[] = "Request Input:\n" . json_encode(
                $context['input'],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        if (isset($decoded['extra']) && is_array($decoded['extra']) && $decoded['extra'] !== []) {
            $parts[] = "Extra:\n" . json_encode(
                $decoded['extra'],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return implode("\n\n", $parts);
    }

    protected function extractContext(string $message): array
    {
        $context = [];

        if (! preg_match_all('/"([^"]+)":("([^"]*)"|(\d+)|true|false|null)/', $message, $matches, PREG_SET_ORDER)) {
            return $context;
        }

        foreach ($matches as $match) {
            $key = $match[1];

            if ($key === 'exception') {
                break;
            }

            $context[$key] = match (TRUE) {
                isset($match[4]) && $match[4] !== '' && $match[4] !== '0' => $match[4],
                isset($match[3]) && $match[3] !== '' => $match[3],
                default => mb_trim($match[2], '"'),
            };
        }

        return $context;
    }

    protected function matchesSearchTerm(LogEntry $logEntry, ?string $searchTerm): bool
    {
        if ($searchTerm === NULL || mb_trim($searchTerm) === '') {
            return TRUE;
        }

        $term = mb_strtolower(mb_trim($searchTerm));

        return str_contains(mb_strtolower($logEntry->message), $term)
            || str_contains(mb_strtolower($logEntry->level), $term)
            || str_contains(mb_strtolower($logEntry->extra), $term)
            || str_contains(mb_strtolower($logEntry->timestamp), $term)
            || str_contains(mb_strtolower($logEntry->environment), $term);
    }
}
