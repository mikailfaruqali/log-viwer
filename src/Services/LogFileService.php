<?php

namespace Snawbar\LogViewer\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class LogFileService
{
    protected string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    public function getLogFiles(): Collection
    {
        return collect(File::glob(sprintf('%s/*.log', $this->logPath)))
            ->map(fn ($file) => basename($file))
            ->filter(fn ($filename) => $this->isValidLogFile($filename))
            ->sortByDesc(fn ($filename) => $this->getFileModificationTime($filename))
            ->values();
    }

    public function logFileExists(string $filename): bool
    {
        return File::exists($this->getLogFilePath($filename));
    }

    public function getLogFileContent(string $filename): string
    {
        $path = $this->getLogFilePath($filename);

        if (!$this->logFileExists($filename)) {
            throw new InvalidArgumentException(sprintf("Log file '%s' does not exist.", $filename));
        }

        return File::get($path);
    }

    public function deleteLogFile(string $filename): bool
    {
        if ($filename === '' || $filename === '0') {
            throw new InvalidArgumentException('Filename cannot be empty.');
        }

        if (!$this->logFileExists($filename)) {
            throw new InvalidArgumentException(sprintf("Log file '%s' does not exist.", $filename));
        }

        $path = $this->getLogFilePath($filename);

        if (!File::delete($path)) {
            throw new RuntimeException(sprintf("Failed to delete log file '%s'.", $filename));
        }

        return TRUE;
    }

    protected function getLogFilePath(string $filename): string
    {
        $filename = basename($filename);

        return sprintf('%s/%s', $this->logPath, $filename);
    }

    protected function isValidLogFile(string $filename): bool
    {
        return substr($filename, -4) === '.log' &&
               File::isReadable($this->getLogFilePath($filename));
    }

    protected function getFileModificationTime(string $filename): int
    {
        return File::lastModified($this->getLogFilePath($filename));
    }
}
