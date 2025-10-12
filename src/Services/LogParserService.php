<?php

namespace Snawbar\LogViewer\Services;

use Illuminate\Support\Collection;
use Snawbar\LogViewer\DataObjects\LogEntry;

class LogParserService
{
    protected const LOG_REGEX = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](?:.*?(\w+)\.)?(\w+): (.*)/';

    protected const DEFAULT_ENVIRONMENT = 'production';

    public function __construct(
        protected LogFileService $logFileService
    ) {}

    public function parseLogFile(string $filename, ?string $searchTerm = NULL): Collection
    {
        $content = $this->logFileService->getLogFileContent($filename);

        return $this->parseLogContent($content, $searchTerm);
    }

    public function parseLogContent(string $content, ?string $searchTerm = NULL): Collection
    {
        if (in_array(trim($content), ['', '0'], TRUE)) {
            return collect();
        }

        $lines = $this->splitContentIntoLines($content);
        $entries = collect();
        $currentEntry = NULL;

        foreach ($lines as $line) {
            if ($this->isLogEntryStart($line)) {
                if ($currentEntry !== NULL) {
                    $logEntry = $this->createLogEntry($currentEntry);
                    if ($this->matchesSearchTerm($logEntry, $searchTerm)) {
                        $entries->push($logEntry);
                    }
                }

                $currentEntry = $this->parseLogEntryHeader($line);
            } elseif ($currentEntry && $this->isStackTraceLine($line)) {
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

    protected function splitContentIntoLines(string $content): array
    {
        return explode("\n", $content);
    }

    protected function isLogEntryStart(string $line): bool
    {
        return preg_match(self::LOG_REGEX, $line) === 1;
    }

    protected function isStackTraceLine(string $line): bool
    {
        return ! in_array(trim($line), ['', '0'], TRUE);
    }

    protected function parseLogEntryHeader(string $line): array
    {
        preg_match(self::LOG_REGEX, $line, $matches);

        return [
            'timestamp' => $matches[1] ?? '',
            'environment' => $matches[2] ?? self::DEFAULT_ENVIRONMENT,
            'level' => mb_strtolower($matches[3] ?? 'info'),
            'message' => $matches[4] ?? '',
            'extra' => '',
        ];
    }

    protected function createLogEntry(array $data): LogEntry
    {
        return new LogEntry(
            $data['timestamp'],
            $data['environment'],
            $data['level'],
            $data['message'],
            trim($data['extra'])
        );
    }

    protected function matchesSearchTerm(LogEntry $logEntry, ?string $searchTerm): bool
    {
        if ($searchTerm === NULL || trim($searchTerm) === '') {
            return TRUE;
        }

        $searchTerm = mb_strtolower(trim($searchTerm));

        if (mb_strpos(mb_strtolower($logEntry->message), $searchTerm) !== FALSE) {
            return TRUE;
        }

        if (mb_strpos(mb_strtolower($logEntry->level), $searchTerm) !== FALSE) {
            return TRUE;
        }

        if (mb_strpos(mb_strtolower($logEntry->extra), $searchTerm) !== FALSE) {
            return TRUE;
        }

        if (mb_strpos(mb_strtolower($logEntry->timestamp), $searchTerm) !== FALSE) {
            return TRUE;
        }

        return mb_strpos(mb_strtolower($logEntry->environment), $searchTerm) !== FALSE;
    }
}
