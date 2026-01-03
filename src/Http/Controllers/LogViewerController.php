<?php

namespace Snawbar\LogViewer\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Snawbar\LogViewer\Http\Requests\DeleteLogFileRequest;
use Snawbar\LogViewer\Services\LogFileService;
use Snawbar\LogViewer\Services\LogParserService;
use Throwable;

class LogViewerController extends Controller
{
    protected $logFileService;

    protected $logParserService;

    public function __construct(
        LogFileService $logFileService,
        LogParserService $logParserService
    ) {
        $this->logFileService = $logFileService;
        $this->logParserService = $logParserService;
    }

    public function index(Request $request): View
    {
        $logFiles = $this->logFileService->getLogFiles();
        $currentFile = $this->getCurrentFile($request, $logFiles);
        $searchTerm = $request->input('search');
        $logEntries = $this->getLogEntries($currentFile, $searchTerm);

        return view('snawbar-log-viewer::index', [
            'logFiles' => $logFiles,
            'currentFile' => $currentFile,
            'logEntries' => $logEntries,
            'searchTerm' => $searchTerm,
        ]);
    }

    public function delete(DeleteLogFileRequest $deleteLogFileRequest): RedirectResponse
    {
        $filename = $deleteLogFileRequest->validated()['file'];

        try {
            $this->logFileService->deleteLogFile($filename);

            return redirect()->route('log-viewer.index')
                ->with('success', sprintf("Log file '%s' has been deleted successfully.", $filename));
        } catch (Throwable $throwable) {
            return back()->with('error', sprintf('Failed to delete log file: %s', $throwable->getMessage()));
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

    protected function getLogEntries(?string $filename, ?string $searchTerm = NULL): Collection
    {
        if (! $filename) {
            return collect();
        }

        try {
            return $this->logParserService->parseLogFile($filename, $searchTerm);
        } catch (Throwable $throwable) {
            logger()->warning('Failed to parse log file', [
                'filename' => $filename,
                'error' => $throwable->getMessage(),
            ]);

            return collect();
        }
    }
}
