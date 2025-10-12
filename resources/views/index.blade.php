<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snawbar Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .delete-form {
            display: inline;
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
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #e0e0e0;
            margin: 0;
        }

        .search-container {
            display: flex;
            align-items: center;
            position: relative;
            max-width: 400px;
            min-width: 280px;
        }

        .search-form {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }

        .search-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            color: #ffffff;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.3s ease;
            outline: none;
            font-weight: 400;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(96, 165, 250, 0.4);
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.1);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-weight: 300;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            pointer-events: none;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .search-input:focus ~ .search-icon {
            color: #60a5fa;
        }

        .search-results-info {
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.1), rgba(139, 92, 246, 0.1));
            border: 1px solid rgba(96, 165, 250, 0.3);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            color: #60a5fa;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(96, 165, 250, 0.1);
            animation: slideInDown 0.5s ease-out;
            flex-wrap: nowrap;
            justify-content: space-between;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-results-info .clear-link {
            margin-left: auto;
            color: #60a5fa;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(96, 165, 250, 0.1);
            border: 1px solid rgba(96, 165, 250, 0.2);
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .search-results-info .result-text {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .search-results-info .clear-link:hover {
            background: rgba(96, 165, 250, 0.2);
            border-color: rgba(96, 165, 250, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(96, 165, 250, 0.2);
        }

        .empty-state-search h3 {
            font-size: 1.25rem;
        }

        .search-highlight {
            background: linear-gradient(120deg, #ffd60a, #ffbe0b);
            color: #000;
            padding: 0.1rem 0.2rem;
            border-radius: 4px;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(255, 214, 10, 0.3);
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .copy-btn {
            background: rgba(96, 165, 250, 0.1);
            border: 1px solid rgba(96, 165, 250, 0.3);
            color: #60a5fa;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .copy-btn:hover {
            background: rgba(96, 165, 250, 0.2);
            border-color: rgba(96, 165, 250, 0.5);
            transform: scale(1.05);
        }

        .copy-btn.copied {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
            color: #22c55e;
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
            position: relative;
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
                padding: 1rem;
                flex-direction: column;
                gap: 1.5rem;
                height: auto;
                min-height: 100px;
            }

            .header-left {
                width: 100%;
                justify-content: space-between;
                align-items: center;
            }

            .header-title {
                font-size: 1.1rem;
            }

            .search-container {
                width: 100%;
                max-width: none;
                min-width: auto;
            }

            .search-form {
                width: 100%;
            }

            .search-input {
                padding: 0.8rem 1rem 0.8rem 2.8rem;
                font-size: 1rem;
            }

            .search-icon {
                left: 1rem;
                font-size: 1.1rem;
            }

            .search-results-info {
                padding: 1rem;
                flex-direction: row;
                align-items: center;
                gap: 0.75rem;
                text-align: left;
                flex-wrap: nowrap;
                justify-content: space-between;
            }

            .search-results-info .clear-link {
                margin-left: auto;
                align-self: center;
                margin-top: 0;
                flex-shrink: 0;
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

        @media (max-width: 576px) {
            .header {
                padding: 0.75rem;
                gap: 1rem;
            }

            .header-title {
                font-size: 1rem;
                text-align: center;
            }

            .search-input {
                padding: 0.7rem 1rem 0.7rem 2.5rem;
                font-size: 0.95rem;
                border-radius: 22px;
            }

            .search-icon {
                left: 0.9rem;
                font-size: 1rem;
            }

            .search-results-info {
                padding: 0.8rem;
                font-size: 0.9rem;
                flex-direction: row;
                gap: 0.5rem;
                align-items: center;
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .search-results-info .clear-link {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
                align-self: center;
                margin-left: 0;
                width: fit-content;
                flex-shrink: 0;
            }

            .content {
                padding: 0.75rem;
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

        .swal-dark-theme {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
        }

        .swal2-popup.swal-dark-theme .swal2-title {
            color: #e0e0e0;
        }

        .swal2-popup.swal-dark-theme .swal2-html-container {
            color: #9ca3af;
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
                    <form action="{{ route('log-viewer.delete') }}" method="POST" class="delete-form" data-filename="{{ $file }}">
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
            <div class="header-left">
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
            @if ($currentFile)
                <div class="search-container">
                    <form action="{{ route('log-viewer.index') }}" method="GET" class="search-form" id="searchForm">
                        <div class="search-wrapper">
                            <i class="bi bi-search search-icon" id="searchIcon"></i>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $searchTerm }}" 
                                   placeholder="Search in logs..." 
                                   class="search-input" 
                                   id="searchInput"
                                   autocomplete="off">
                        </div>
                        <input type="hidden" name="file" value="{{ $currentFile }}">
                    </form>
                </div>
            @endif
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

            @if ($searchTerm && $currentFile)
                <div class="search-results-info">
                    <div class="result-text">
                        <i class="bi bi-search-heart"></i>
                        <span>
                            @if ($logEntries->count() > 0)
                                Found <strong>{{ $logEntries->count() }}</strong> {{ Str::plural('result', $logEntries->count()) }}
                            @else
                                No results found
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('log-viewer.index', ['file' => $currentFile]) }}" class="clear-link">
                        <i class="bi bi-x-circle"></i> Clear search
                    </a>
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
                        <div class="log-message" title="{{ $entry->message }}">
                            {!! $entry->highlightSearchTerm($entry->message, $searchTerm) !!}
                        </div>
                    </div>
                    <div class="log-details" id="details-{{ $index }}">
                        <h6>
                            Full Message
                            <button class="copy-btn" onclick="copyToClipboard('message-{{ $index }}', this)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </h6>
                        <pre id="message-{{ $index }}">{!! $entry->highlightSearchTerm($entry->message, $searchTerm) !!}</pre>
                        @if (!empty($entry->extra))
                            <h6>
                                Stack Trace
                                <button class="copy-btn" onclick="copyToClipboard('trace-{{ $index }}', this)">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </h6>
                            <pre id="trace-{{ $index }}">{!! $entry->highlightSearchTerm(trim($entry->extra), $searchTerm) !!}</pre>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    @if ($searchTerm)
                        <i class="bi bi-search"></i>
                        <h3 class="empty-state-search">No search results found</h3>
                        <p>Try searching with different keywords.</p>
                    @elseif ($currentFile)
                        <i class="bi bi-journal-x"></i>
                        <h3>Log file is empty</h3>
                        <p>This log file contains no entries.</p>
                    @else
                        <i class="bi bi-journal-x"></i>
                        <h3>No file selected</h3>
                        <p>Select a log file from the sidebar to view its contents.</p>
                    @endif
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

        function copyToClipboard(elementId, button) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i> Copied';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i> Copied';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('copied');
                }, 2000);
            });
        }

        document.querySelectorAll('.file-item a').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        });

        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');

        if (searchInput) {
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                
                const hasValue = this.value.trim();
                
                if (hasValue) {
                    searchTimeout = setTimeout(() => {
                        searchForm.submit();
                    }, 600);
                }
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    if (this.value.trim()) {
                        searchForm.submit();
                    }
                }
            });
        }

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const filename = this.dataset.filename;
                
                Swal.fire({
                    title: 'Delete Log File',
                    text: `Are you sure you want to delete "${filename}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    background: 'rgba(17, 17, 17, 0.95)',
                    color: '#e0e0e0',
                    customClass: {
                        popup: 'swal-dark-theme'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
</body>

</html>
