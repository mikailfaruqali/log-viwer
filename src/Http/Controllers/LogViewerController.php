<?php

namespace Snawbar\LogViewer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Throwable;

class LogViewerController extends Controller
{
    protected string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    public function index(Request $request)
    {
        $logFiles = collect(File::glob(sprintf('%s/*.log', $this->logPath)))
            ->map(fn ($file) => basename((string) $file))
            ->sortDesc()
            ->values();

        $currentFile = $request->input('file', $logFiles->first());
        $filePath = sprintf('%s/%s', $this->logPath, $currentFile);

        $logContent = $this->readFileSafe($filePath);

        return view('log-viewer::index', ['logFiles' => $logFiles, 'currentFile' => $currentFile, 'logContent' => $logContent]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'file' => ['required', 'string'],
        ]);

        $path = sprintf('%s/%s', $this->logPath, $request->input('file'));

        try {
            File::delete($path);
        } catch (Throwable $throwable) {
            return back()->withErrors(['error' => sprintf('Error deleting file: %s', $throwable->getMessage())]);
        }
        return null;
    }

    private function readFileSafe(string $path): string
    {
        try {
            return File::exists($path) ? File::get($path) : '';
        } catch (Throwable $throwable) {
            return sprintf('Error reading file: %s', $throwable->getMessage());
        }
    }
}
