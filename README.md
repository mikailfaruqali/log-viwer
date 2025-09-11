# 📋 Snawbar Log Viewer

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-%3E%3D5.0-red.svg)](https://laravel.com)

A modern, elegant, and feature-rich log viewer for Laravel applications. Experience a beautiful glassmorphism-inspired interface that makes viewing and managing your application logs both intuitive and visually appealing.

## ✨ Features

### 🎨 **Modern UI/UX**
- **Glassmorphism Design**: Beautiful translucent interface with backdrop blur effects
- **Dark Theme**: Eye-friendly dark interface perfect for development environments
- **Responsive Layout**: Fully responsive design that works on desktop, tablet, and mobile
- **Smooth Animations**: CSS transitions and hover effects for enhanced user experience
- **Mobile-First**: Collapsible sidebar and touch-friendly interactions on mobile devices

### 📁 **File Management**
- **Smart File Detection**: Automatically discovers all `.log` files in your `storage/logs` directory
- **File Switching**: Seamlessly switch between different log files with a single click
- **File Deletion**: Safely delete old log files directly from the interface with confirmation
- **File Sorting**: Log files are automatically sorted by modification time (newest first)
- **File Status**: Visual indicators for active/selected files

### 📊 **Log Parsing & Display**
- **Intelligent Parsing**: Advanced regex-based log parsing that handles Laravel's log format
- **Multi-line Support**: Properly handles stack traces and multi-line log entries
- **Log Levels**: Color-coded log levels (debug, info, notice, warning, error, critical, alert, emergency)
- **Timestamp Formatting**: Human-readable timestamp display
- **Environment Detection**: Automatically detects and displays the environment (production, local, etc.)

### 🔍 **Enhanced Viewing Experience**
- **Expandable Entries**: Click to expand log entries and view full messages and stack traces
- **Stack Trace Viewer**: Properly formatted stack trace display for debugging
- **Message Truncation**: Long messages are truncated with ellipsis and expandable on demand
- **Empty State Handling**: Graceful handling of empty log files and missing selections
- **Error Recovery**: Robust error handling for corrupted or unreadable log files

### 🔐 **Security & Access Control**
- **Authentication Required**: Protected by Laravel's authentication middleware by default
- **Configurable Middleware**: Easily add custom authorization middleware
- **CSRF Protection**: All delete operations are CSRF protected
- **Input Validation**: Comprehensive validation for all user inputs
- **Safe File Operations**: Prevents directory traversal and unauthorized file access

### ⚡ **Performance & Reliability**
- **Efficient Parsing**: Optimized log parsing algorithm that handles large files
- **Memory Management**: Processes logs efficiently without excessive memory usage
- **Error Logging**: Internal errors are logged for debugging purposes
- **Graceful Degradation**: Continues working even when individual log files have issues

### 🛠️ **Developer Experience**
- **Zero Dependencies**: No external CSS or JS frameworks required
- **Easy Installation**: Simple Composer installation with auto-discovery
- **Configurable Routes**: Customize the route path and middleware
- **Publishable Views**: Customize the interface to match your application's design
- **Clean Architecture**: Well-structured codebase following Laravel conventions

## 🚀 Installation

Install the package via Composer:

```bash
composer require mikailfaruqali/log-viewer
```

The package uses Laravel's auto-discovery feature, so no additional setup is required.

## ⚙️ Configuration

### Basic Configuration

The package works out-of-the-box, but you can publish the configuration file for customization:

```bash
php artisan vendor:publish --provider="Snawbar\LogViewer\LogViewerServiceProvider" --tag="config"
```

This creates a `config/snawbar-log-viewer.php` file:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Log Viewer Route Path
    |--------------------------------------------------------------------------
    | This is the URI path where the log viewer will be accessible.
    | Default: 'logs' (accessible at /logs)
    |
    */
    'route-path' => 'logs',

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Route Middleware
    |--------------------------------------------------------------------------
    | These middleware will be assigned to every route in the package.
    | You can add your own middleware here to restrict access.
    | Default: ['web', 'auth'] - requires authentication
    |
    */
    'middleware' => ['web', 'auth'],
];
```

### Advanced Configuration

**Custom Route Path:**
```php
'route-path' => 'admin/logs', // Accessible at /admin/logs
```

**Custom Middleware:**
```php
'middleware' => ['web', 'auth', 'admin'], // Add admin middleware
```

**Role-based Access:**
```php
'middleware' => ['web', 'auth', 'can:view-logs'], // Laravel Gates/Policies
```

### View Customization

Publish the views to customize the interface:

```bash
php artisan vendor:publish --provider="Snawbar\LogViewer\LogViewerServiceProvider" --tag="views"
```

This publishes views to `resources/views/vendor/snawbar-log-viewer/` for customization.

## 📖 Usage

### Accessing the Log Viewer

After installation, navigate to the configured route:

```
https://your-app.com/logs
```

**Note:** You must be authenticated to access the log viewer (default middleware: `['web', 'auth']`)

### Interface Overview

1. **Sidebar**: Lists all available log files, sorted by modification date
2. **Main Panel**: Displays log entries for the selected file
3. **Log Entries**: Click to expand and view full messages and stack traces
4. **Delete Button**: Remove old log files (with confirmation)

### Log Entry Features

Each log entry displays:
- **Log Level**: Color-coded badges (debug, info, warning, error, etc.)
- **Timestamp**: When the log entry was created
- **Environment**: Application environment (production, local, staging, etc.)
- **Message**: The log message (truncated with expand option)
- **Stack Trace**: Full error details and stack trace (when available)

### Mobile Experience

The interface is fully responsive:
- Collapsible sidebar on mobile devices
- Touch-friendly interactions
- Optimized layout for smaller screens
- Swipe gestures and mobile navigation

## 🎨 Screenshots

### Desktop View
The main interface showing log files in the sidebar and expanded log entries:

```
┌─────────────────────────────────────────────────────────────┐
│ [☰] Log Viewer Dashboard                                    │
├─────────────────────────────────────────────────────────────┤
│ 📁 Log Files        │ 📋 Viewing: laravel-2024-01-15.log   │
│                     │                                       │
│ laravel-2024-01-15  │ [ERROR] 2024-01-15 10:30:45         │
│ laravel-2024-01-14  │ Undefined variable: user             │
│ laravel-2024-01-13  │ ▼ Click to expand stack trace        │
│                     │                                       │
│ [🗑️] Delete buttons │ [INFO] 2024-01-15 10:25:12          │
│                     │ User login successful                 │
│                     │                                       │
│                     │ [WARNING] 2024-01-15 09:15:33        │
│                     │ Deprecated function usage            │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 Advanced Usage

### Custom Middleware

Create custom middleware for role-based access:

```php
// app/Http/Middleware/LogViewerAccess.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogViewerAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Access denied to log viewer');
        }

        return $next($request);
    }
}
```

Register and use the middleware:

```php
// config/snawbar-log-viewer.php
'middleware' => ['web', 'auth', App\Http\Middleware\LogViewerAccess::class],
```

### Integration with Laravel Gates

Use Laravel's authorization gates:

```php
// app/Providers/AuthServiceProvider.php
Gate::define('view-logs', function ($user) {
    return $user->hasPermission('view-logs');
});
```

```php
// config/snawbar-log-viewer.php
'middleware' => ['web', 'auth', 'can:view-logs'],
```

### Environment-Specific Configuration

Configure different access rules per environment:

```php
// config/snawbar-log-viewer.php
'middleware' => app()->environment('production') 
    ? ['web', 'auth', 'admin'] 
    : ['web'],
```

## 🧪 Log Format Support

The package supports Laravel's default log format and handles:

- **Standard Laravel Logs**: `[timestamp] environment.LEVEL: message`
- **Stack Traces**: Multi-line error details and backtraces
- **Context Data**: Additional context information in log entries
- **Custom Formats**: Extensible parsing for custom log formats

### Supported Log Levels

| Level | Color | Description |
|-------|-------|-------------|
| 🔍 DEBUG | Gray | Detailed debug information |
| ℹ️ INFO | Blue | General information messages |
| ℹ️ NOTICE | Blue | Normal but significant events |
| ⚠️ WARNING | Yellow | Warning messages |
| ❌ ERROR | Red | Error conditions |
| 🚨 CRITICAL | Dark Red | Critical conditions |
| 🚨 ALERT | Dark Red | Action must be taken immediately |
| 🆘 EMERGENCY | Purple | System is unusable |

## 🛡️ Security Considerations

### Production Environment

For production use, implement strict access controls:

```php
// Recommended production middleware
'middleware' => [
    'web', 
    'auth', 
    'role:admin',           // Role-based access
    'throttle:60,1',        // Rate limiting
    'verified',             // Email verification
],
```

### IP Restriction

Add IP-based restrictions:

```php
// In a custom middleware
public function handle($request, Closure $next)
{
    $allowedIps = ['192.168.1.100', '10.0.0.50'];
    
    if (!in_array($request->ip(), $allowedIps)) {
        abort(403);
    }
    
    return $next($request);
}
```

### Audit Logging

Log access to the log viewer:

```php
// In your middleware
Log::info('Log viewer accessed', [
    'user' => auth()->user()->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

## 🐛 Troubleshooting

### Common Issues

**1. "No log files found"**
- Ensure logs exist in `storage/logs/`
- Check file permissions (Laravel must be able to read the directory)
- Verify the log files have `.log` extension

**2. "Access denied" or 403 errors**
- Check authentication status
- Verify middleware configuration
- Ensure user has required permissions

**3. Empty or garbled log entries**
- Check log file format compatibility
- Ensure files are not corrupted
- Verify character encoding (UTF-8)

**4. Performance issues with large log files**
- Consider log rotation
- Archive old log files
- Use Laravel's log rotation features

### Debug Mode

Enable debug logging for troubleshooting:

```php
// In your .env file
LOG_LEVEL=debug

// This will log parsing errors and other debug information
```

## 🔄 Log Rotation & Maintenance

### Automatic Cleanup

Set up automatic log cleanup:

```php
// In a scheduled command
$this->command('log:clear --days=30'); // Keep 30 days of logs
```

### File Size Management

Monitor and manage large log files:

```bash
# Check log file sizes
du -sh storage/logs/*

# Compress old logs
gzip storage/logs/laravel-2024-01-*.log
```

## 🤝 Contributing

We welcome contributions! Please see our contributing guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/mikailfaruqali/log-viewer.git

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit
```

## 📚 API Reference

### Services

#### `LogFileService`
- `getLogFiles(): Collection` - Get all available log files
- `logFileExists(string $filename): bool` - Check if log file exists
- `getLogFileContent(string $filename): string` - Get log file content
- `deleteLogFile(string $filename): bool` - Delete a log file

#### `LogParserService`
- `parseLogFile(string $filename): Collection` - Parse log file into entries
- `parseLogContent(string $content): Collection` - Parse log content

#### `LogEntry` (Data Object)
- `$timestamp` - Log entry timestamp
- `$environment` - Application environment
- `$level` - Log level (debug, info, warning, error, etc.)
- `$message` - Log message
- `$extra` - Additional data (stack traces, context)

### Routes

| Method | URI | Action | Description |
|--------|-----|--------|-------------|
| GET | `/logs` | `LogViewerController@index` | Display log viewer interface |
| DELETE | `/logs/delete` | `LogViewerController@delete` | Delete a log file |

## 📄 License

This package is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

```
MIT License

Copyright (c) 2024 Mikail Faruq Ali

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 👨‍💻 Author

**Mikail Faruq Ali**
- Email: alanfaruq85@gmail.com
- GitHub: [@mikailfaruqali](https://github.com/mikailfaruqali)

## 🙏 Acknowledgments

- Laravel community for the amazing framework
- Bootstrap Icons for the beautiful icons
- All contributors who help improve this package

---

⭐ **If you find this package useful, please give it a star on GitHub!** ⭐