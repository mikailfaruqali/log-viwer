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

        [$cleanMessage, $context, $longContext] = $this->extractInlineJsonContext($message);

        return [
            'timestamp' => $matches[1] ?? '',
            'environment' => $matches[2] ?? self::DEFAULT_ENVIRONMENT,
            'level' => mb_strtolower($matches[3] ?? 'info'),
            'message' => $cleanMessage,
            'extra' => '',
            'context' => $context,
            'longContext' => $longContext,
            'stackTrace' => '',
        ];
    }

    /**
     * Depth-aware top-level walker: finds the inline JSON object inside a
     * Laravel default-format message line and extracts ONLY top-level keys.
     * Handles unclosed JSON (e.g. when [stacktrace] is appended without closing).
     *
     * @return array{0: string, 1: array<string,string>, 2: array<string,mixed>}
     */
    protected function extractInlineJsonContext(string $message): array
    {
        // Find the inline context blob — Laravel separates it with " {"
        $pos = mb_strpos($message, ' {"');

        if ($pos === FALSE) {
            return [$message, [], []];
        }

        $cleanMessage = mb_trim(mb_substr($message, 0, $pos));
        $i = $pos + 1;
        $len = strlen($message);

        if ($i >= $len || $message[$i] !== '{') {
            return [$cleanMessage, [], []];
        }

        $i++; // skip opening '{'
        $depth = 1;

        $allowed = [
            'tenant', 'channel', 'env', 'environment', 'url', 'method',
            'userid', 'user_id', 'user', 'request_id', 'requestid',
            'ip', 'session_id', 'sessionid', 'route', 'guard',
        ];

        $badges = [];
        $longContext = [];

        while ($i < $len && $depth > 0) {
            // skip whitespace and commas
            while ($i < $len && (ctype_space($message[$i]) || $message[$i] === ',')) {
                $i++;
            }

            if ($i >= $len) {
                break;
            }

            if ($message[$i] === '}') {
                $depth--;
                $i++;

                break;
            }

            // expect a string key
            if ($message[$i] !== '"') {
                break;
            }

            [$key, $i] = $this->readJsonString($message, $i);

            if ($key === NULL) {
                break;
            }

            // skip whitespace and ':'
            while ($i < $len && (ctype_space($message[$i]) || $message[$i] === ':')) {
                $i++;
            }

            if ($i >= $len) {
                break;
            }

            // read value
            [$value, $i, $isComplex] = $this->readJsonValue($message, $i);

            $lkey = mb_strtolower($key);

            if ($lkey === 'exception') {
                continue;
            }

            if ($isComplex && is_array($value)) {
                if ($value !== []) {
                    $label = $lkey === 'input' ? 'Request Input' : $key;
                    $longContext[$label] = $value;
                }

                continue;
            }

            if (! $isComplex) {
                $str = $value === NULL ? '' : (string) $value;

                if (in_array($lkey, $allowed, TRUE) && $str !== '' && mb_strlen($str) <= 100) {
                    $badges[$key] = $str;
                } elseif (mb_strlen($str) > 100) {
                    $longContext[$key] = $str;
                }
            }
        }

        return [$cleanMessage, $badges, $longContext];
    }

    /**
     * Reads a JSON-style string starting at $i (which must point at '"').
     * Returns [decoded string|null, new position].
     *
     * @return array{0: ?string, 1: int}
     */
    private function readJsonString(string $s, int $i): array
    {
        $len = strlen($s);

        if ($i >= $len || $s[$i] !== '"') {
            return [NULL, $i];
        }

        $i++;
        $out = '';

        while ($i < $len) {
            $ch = $s[$i];

            if ($ch === '\\' && $i + 1 < $len) {
                $next = $s[$i + 1];
                $out .= match ($next) {
                    'n'     => "\n",
                    't'     => "\t",
                    'r'     => "\r",
                    '"'     => '"',
                    '\\'    => '\\',
                    '/'     => '/',
                    default => $next,
                };
                $i += 2;
            } elseif ($ch === '"') {
                return [$out, $i + 1];
            } else {
                $out .= $ch;
                $i++;
            }
        }

        return [NULL, $i];
    }

    /**
     * Reads a JSON value (string, number, bool, null, array, object).
     * Returns [value, new position, isComplex] where isComplex is TRUE for arrays/objects.
     *
     * @return array{0: mixed, 1: int, 2: bool}
     */
    private function readJsonValue(string $s, int $i): array
    {
        $len = strlen($s);

        if ($i >= $len) {
            return [NULL, $i, FALSE];
        }

        $ch = $s[$i];

        // String
        if ($ch === '"') {
            [$str, $next] = $this->readJsonString($s, $i);

            return [$str, $next, FALSE];
        }

        // Object or array — find balanced span and decode
        if ($ch === '{' || $ch === '[') {
            $open = $ch;
            $close = $ch === '{' ? '}' : ']';
            $start = $i;
            $depth = 0;
            $inStr = FALSE;
            $esc = FALSE;

            while ($i < $len) {
                $c = $s[$i];

                if ($esc) {
                    $esc = FALSE;
                    $i++;

                    continue;
                }

                if ($c === '\\' && $inStr) {
                    $esc = TRUE;
                    $i++;

                    continue;
                }

                if ($c === '"') {
                    $inStr = ! $inStr;
                    $i++;

                    continue;
                }

                if (! $inStr) {
                    if ($c === $open) {
                        $depth++;
                    } elseif ($c === $close) {
                        $depth--;

                        if ($depth === 0) {
                            $i++;

                            break;
                        }
                    }
                }

                $i++;
            }

            $jsonStr = substr($s, $start, $i - $start);
            $decoded = json_decode($jsonStr, TRUE);

            return [is_array($decoded) ? $decoded : [], $i, TRUE];
        }

        // Number, bool, null, or bare token
        $start = $i;

        while ($i < $len && $s[$i] !== ',' && $s[$i] !== '}' && $s[$i] !== ']' && ! ctype_space($s[$i])) {
            $i++;
        }

        $tok = substr($s, $start, $i - $start);

        $val = match (TRUE) {
            $tok === 'true'  => TRUE,
            $tok === 'false' => FALSE,
            $tok === 'null'  => NULL,
            is_numeric($tok) => $tok + 0,
            default          => $tok,
        };

        return [$val, $i, FALSE];
    }

    protected function createLogEntry(array $data): LogEntry
    {
        $extra = mb_trim($data['extra']);
        $stackTrace = $data['stackTrace'] ?? '';

        // For standard (non-JSON) log entries, the entire extra IS the stack trace
        if ($stackTrace === '' && $extra !== '') {
            $stackTrace = $extra;
            $extra = '';
        }

        return new LogEntry(
            timestamp: $data['timestamp'],
            environment: $data['environment'],
            level: $data['level'],
            message: $data['message'],
            extra: $extra,
            context: $data['context'] ?? [],
            longContext: $data['longContext'] ?? [],
            stackTrace: $stackTrace,
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

        [$stackTrace, $longContext] = $this->buildJsonStructuredExtra($decoded);

        return [
            'timestamp' => $this->normalizeJsonTimestamp($decoded['datetime'] ?? ''),
            'environment' => $decoded['channel'] ?? self::DEFAULT_ENVIRONMENT,
            'level' => mb_strtolower($decoded['level_name']),
            'message' => $decoded['message'],
            'extra' => '',
            'context' => $this->extractJsonContextBadges($decoded['context'] ?? []),
            'longContext' => $longContext,
            'stackTrace' => $stackTrace,
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
                static fn (mixed $v): bool => (is_scalar($v) || $v === NULL) && mb_strlen((string) $v) <= 80,
            ),
        );
    }

    /**
     * Returns [stackTrace, longContext] for a JSON log entry.
     * stackTrace is the formatted exception/stack trace string.
     * longContext is a keyed array of long/complex values to show as expandable sections.
     *
     * @return array{string, array<string,mixed>}
     */
    protected function buildJsonStructuredExtra(array $decoded): array
    {
        $stackTrace = '';
        $longContext = [];
        $context = $decoded['context'] ?? [];

        // Build exception / stack trace string
        if (isset($context['exception']) && is_array($context['exception'])) {
            $exc = $context['exception'];
            $lines = [];

            if (isset($exc['class'])) {
                $lines[] = $exc['class'] . ': ' . ($exc['message'] ?? '');
            }

            if (isset($exc['file'])) {
                $lines[] = 'at ' . $exc['file'] . (isset($exc['line']) ? ':' . $exc['line'] : '');
            }

            if (isset($exc['trace']) && is_array($exc['trace'])) {
                foreach ($exc['trace'] as $i => $frame) {
                    $fn = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
                    $loc = isset($frame['file']) ? $frame['file'] . (isset($frame['line']) ? ':' . $frame['line'] : '') : '[internal]';
                    $lines[] = "  #{$i} {$loc}: {$fn}()";
                }
            }

            if (isset($exc['previous']) && is_array($exc['previous'])) {
                $prev = $exc['previous'];
                $lines[] = "\nCaused by: " . ($prev['class'] ?? '') . ': ' . ($prev['message'] ?? '');

                if (isset($prev['file'])) {
                    $lines[] = 'at ' . $prev['file'] . (isset($prev['line']) ? ':' . $prev['line'] : '');
                }
            }

            if ($lines !== []) {
                $stackTrace = implode("\n", $lines);
            }
        }

        // Request input
        if (isset($context['input']) && is_array($context['input']) && $context['input'] !== []) {
            $longContext['Request Input'] = $context['input'];
        }

        // Any other complex (non-scalar) context keys
        foreach ($context as $key => $value) {
            if (in_array($key, ['exception', 'input'], TRUE)) {
                continue;
            }

            if (is_array($value) || (is_string($value) && mb_strlen($value) > 80)) {
                $longContext[$key] = $value;
            }
        }

        // Extra field from JSON (e.g. monolog extra)
        if (isset($decoded['extra']) && is_array($decoded['extra']) && $decoded['extra'] !== []) {
            $longContext['Extra'] = $decoded['extra'];
        }

        return [$stackTrace, $longContext];
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
