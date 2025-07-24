<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Snawbar Log Viewer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .log-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .log-selector select,
        .log-selector button,
        .log-selector a {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #cbd5e0;
            background-color: #fff;
        }

        .log-selector button.delete-btn {
            background-color: #ef4444;
            color: white;
            border-color: #ef4444;
            cursor: pointer;
        }

        .log-selector button.delete-btn:hover {
            background-color: #dc2626;
        }

        .log-content {
            background-color: #1a202c;
            color: #a0aec0;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: monospace;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Log Viewer</h1>

            @if ($logFiles->isNotEmpty())
                <form class="log-selector" method="GET" onchange="this.submit()">
                    <select name="file" id="log-file-selector">
                        @foreach ($logFiles as $file)
                            <option value="{{ $file }}" @selected($file === $currentFile)>{{ $file }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if ($currentFile)
            <div style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
                <h2 style="font-size:1.25rem; margin:0;">
                    Viewing: <span style="font-weight:normal;">{{ $currentFile }}</span>
                </h2>
                <form action="{{ route('log-viewer.delete') }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this log file?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="file" value="{{ $currentFile }}">
                    <button type="submit" class="delete-btn">Delete this file</button>
                </form>
            </div>

            <div class="log-content">
                <code>{{ $logContent ?: 'Log file is empty.' }}</code>
            </div>
        @else
            <p>No log files found.</p>
        @endif
    </div>
</body>

</html>
