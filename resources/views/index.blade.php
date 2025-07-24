<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snawbar Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --sidebar-width: 320px;
            --header-height: 70px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #232526 0%, #414345 100%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 50%, #2d2d2d 100%);
            color: #e0e0e0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(17, 17, 17, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            background: var(--primary-gradient);
        }

        .sidebar-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .sidebar-title i {
            margin-right: 0.5rem;
        }

        .file-list {
            padding: 1rem 0;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            background: transparent;
            border: 1px solid transparent;
        }

        .file-item:hover {
            background: var(--glass-bg);
            border-color: var(--glass-border);
            transform: translateX(4px);
        }

        .file-item.active {
            background: var(--primary-gradient);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateX(4px);
        }

        .file-info {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
        }

        .file-icon {
            margin-right: 0.75rem;
            color: #64b5f6;
            font-size: 1.1rem;
        }

        .file-name {
            font-weight: 500;
            color: #e0e0e0;
            white-space: nowrap;
            overflow: hidden;
            text-decoration: none;
            text-overflow: ellipsis;
        }

        .file-item.active .file-name {
            color: white;
        }

        .delete-btn {
            opacity: 1;
            background: none;
            border: none;
            color: white;
            font-size: 1rem;
            padding: 0.25rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            transform: scale(1.1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            height: var(--header-height);
            background: rgba(17, 17, 17, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #e0e0e0;
            margin: 0;
        }

        .content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .alert {
            border: none;
            border-radius: 12px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #81c784;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #e57373;
        }

        .log-entry {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .log-entry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .log-header {
            padding: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .log-header:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .log-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .expand-icon {
            font-size: 0.875rem;
            color: #9ca3af;
            transition: transform 0.3s ease;
        }

        .expand-icon.rotated {
            transform: rotate(90deg);
        }

        .log-level {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .log-level.debug {
            background: rgba(156, 163, 175, 0.2);
            color: #9ca3af;
        }

        .log-level.info {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .log-level.notice {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        .log-level.warning {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .log-level.error {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .log-level.critical {
            background: rgba(220, 38, 38, 0.3);
            color: #fca5a5;
        }

        .log-level.alert {
            background: rgba(220, 38, 38, 0.3);
            color: #fca5a5;
        }

        .log-level.emergency {
            background: rgba(147, 51, 234, 0.2);
            color: #a78bfa;
        }

        .log-timestamp {
            color: #9ca3af;
            font-size: 0.875rem;
            font-family: 'Courier New', monospace;
        }

        .log-message {
            color: #e0e0e0;
            font-size: 0.9rem;
            max-width: 60ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-details {
            padding: 1.25rem;
            border-top: 1px solid var(--glass-border);
            background: rgba(0, 0, 0, 0.2);
            display: none;
        }

        .log-details.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .log-details h6 {
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .log-details pre {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1rem;
            color: #e0e0e0;
            font-size: 0.8rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #374151;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                padding: 0 1rem;
            }

            .content {
                padding: 1rem;
            }

            .log-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem;
            }

            .log-meta {
                flex-wrap: wrap;
                gap: 0.5rem;
                width: 100%;
            }

            .log-message {
                max-width: none;
                white-space: normal;
                overflow: visible;
                text-overflow: initial;
                word-break: break-word;
                line-height: 1.5;
                margin-top: 0.5rem;
                width: 100%;
            }

            .log-details {
                padding: 1rem;
            }

            .log-entry {
                border-radius: 12px;
            }

            .log-level {
                font-size: 0.65rem;
                padding: 0.2rem 0.5rem;
            }

            .log-timestamp {
                font-size: 0.8rem;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: #e0e0e0;
            font-size: 1.25rem;
            cursor: pointer;
            margin-right: 1rem;
        }

        @media (max-width: 991.98px) {
            .mobile-toggle {
                display: block;
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            .overlay.show {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="overlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-title">
                <i class="bi bi-journal-text"></i>
                Log Files
            </h1>
        </div>
        <div class="file-list">
            @forelse($logFiles as $file)
                <div class="file-item @if ($file === $currentFile) active @endif">
                    <div class="file-info">
                        <i class="bi bi-file-earmark-text file-icon"></i>
                        <a href="?file={{ urlencode($file) }}" class="file-name"
                            title="{{ $file }}">{{ $file }}</a>
                    </div>
                    <form action="{{ route('log-viewer.delete') }}" method="POST" style="display: inline;"
                        onsubmit="return confirm('Are you sure you want to delete {{ $file }}?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="file" value="{{ $file }}">
                        <button type="submit" class="delete-btn" title="Delete {{ $file }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <p>No log files found</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <button class="mobile-toggle" id="mobileToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="header-title">
                @if ($currentFile)
                    Viewing: {{ $currentFile }}
                @else
                    Log Viewer Dashboard
                @endif
            </h1>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            @forelse($logEntries as $index => $entry)
                @php
                    $levelClass = strtolower($entry->level);
                @endphp
                <div class="log-entry">
                    <div class="log-header" onclick="toggleLogDetails({{ $index }})">
                        <div class="log-meta">
                            <i class="bi bi-caret-right expand-icon" id="icon-{{ $index }}"></i>
                            <span class="log-level {{ $levelClass }}">{{ $entry->level }}</span>
                            <span class="log-timestamp">{{ $entry->timestamp }}</span>
                        </div>
                        <div class="log-message" title="{{ $entry->message }}">{{ $entry->message }}</div>
                    </div>
                    <div class="log-details" id="details-{{ $index }}">
                        <h6>Full Message</h6>
                        <pre>{{ $entry->message }}</pre>
                        @if (!empty($entry->extra))
                            <h6>Stack Trace</h6>
                            <pre>{{ trim($entry->extra) }}</pre>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-journal-x"></i>
                    <h3>{{ $currentFile ? 'Log file is empty' : 'No file selected' }}</h3>
                    <p>{{ $currentFile ? 'This log file contains no entries.' : 'Select a log file from the sidebar to view its contents.' }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        mobileToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        function toggleLogDetails(index) {
            const details = document.getElementById(`details-${index}`);
            const icon = document.getElementById(`icon-${index}`);

            details.classList.toggle('show');
            icon.classList.toggle('rotated');
        }

        document.querySelectorAll('.file-item a').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        });
    </script>
</body>

</html>
