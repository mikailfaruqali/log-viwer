<?php

namespace Snawbar\LogViewer\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Snawbar\LogViewer\Http\Requests\DeleteLogFileRequest;
use Snawbar\LogViewer\Services\LogFileService;
use Snawbar\LogViewer\Services\LogParserService;

class LogViewerController extends Controller
{
    public function __construct(
        protected LogFileService $logFileService,
        protected LogParserService $logParserService
    ) {
    }

    public function index(Request $request): View
    {
        $logFiles = $this->logFileService->getLogFiles();
        $currentFile = $this->getCurrentFile($request, $logFiles);
        $logEntries = $this->getLogEntries($currentFile);

        return view('snawbar-log-viewer::index', ['logFiles' => $logFiles, 'currentFile' => $currentFile, 'logEntries' => $logEntries]);
    }

    public function delete(DeleteLogFileRequest $deleteLogFileRequest): RedirectResponse
    {
        $filename = $deleteLogFileRequest->validated('file');

        try {
            $this->logFileService->deleteLogFile($filename);

            return to_route('log-viewer.index')
                ->with('success', sprintf("Log file '%s' has been deleted successfully.", $filename));
        } catch (Throwable $throwable) {
            return back()->with('error', 'Failed to delete log file: ' . $throwable->getMessage());
        }
    }

    protected function getCurrentFile(Request $request, Collection $logFiles): ?string
    {
        $requestedFile = $request->input('file');

        if ($requestedFile && $logFiles->contains($requestedFile)) {
            return $requestedFile;
        }

        return $logFiles->first();
    }

    protected function getLogEntries(?string $filename): Collection
    {
        if (!$filename) {
            return collect();
        }

        try {
            return $this->logParserService->parseLogFile($filename);
        } catch (Throwable $throwable) {
            logger()->warning('Failed to parse log file', [
                'filename' => $filename,
                'error' => $throwable->getMessage()
            ]);

            return collect();
        }
    }
}
