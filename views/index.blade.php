<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* Lighter dark palette — soft, airy slate */
            --bg:          #2a2f3a;
            --bg-soft:     #242832;
            --surface:     #343a47;
            --surface-2:   #404757;
            --surface-3:   #505868;
            --border:      #404757;
            --border-2:    #505868;

            --text:        #e4e7ee;
            --text-2:      #c5cad5;
            --text-3:      #9aa1b1;
            --text-4:      #6b7280;

            --accent:      #7dd3fc;
            --accent-soft: rgba(125,211,252,.14);
            --accent-2:    #a5d8ff;

            --red:         #fca5a5;
            --red-soft:    rgba(252,165,165,.13);
            --yellow:      #fcd34d;
            --yellow-soft: rgba(252,211,77,.13);
            --green:       #86efac;
            --green-soft:  rgba(134,239,172,.13);
            --blue:        #7dd3fc;
            --blue-soft:   rgba(125,211,252,.13);
            --purple:      #d8b4fe;
            --purple-soft: rgba(216,180,254,.13);
            --orange:      #fdba74;
            --orange-soft: rgba(253,186,116,.13);
            --cyan:        #5eead4;
            --cyan-soft:   rgba(94,234,212,.13);

            --sidebar-w:   260px;
            --topbar-h:    58px;
            --radius:      8px;
            --radius-sm:   6px;
            --radius-xs:   4px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.55;
            display: flex;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::-webkit-scrollbar { width: 9px; height: 9px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 99px; border: 2px solid transparent; background-clip: padding-box; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-4); border: 2px solid transparent; background-clip: padding-box; }

        button, input { font-family: inherit; }
        button { cursor: pointer; }

        /* ─────────── SIDEBAR ─────────── */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--bg-soft);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: transform .25s cubic-bezier(.4,0,.2,1);
        }

        .sidebar-brand {
            padding: 1.1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            border-bottom: 1px solid var(--border);
            min-height: var(--topbar-h);
        }

        .brand-icon {
            width: 32px; height: 32px;
            background: var(--accent-soft);
            border: 1px solid rgba(125,211,252,.3);
            border-radius: var(--radius-sm);
            display: grid; place-items: center;
            color: var(--accent);
            font-size: .95rem;
        }

        .brand-text strong {
            display: block;
            font-size: .9rem;
            font-weight: 600;
            color: var(--text);
            line-height: 1.2;
        }
        .brand-text small {
            font-size: .68rem;
            color: var(--text-3);
            line-height: 1.2;
        }

        .sidebar-section {
            padding: 1rem 1.25rem .5rem;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-4);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-section .count {
            background: var(--surface-2);
            color: var(--text-3);
            padding: 1px 7px;
            border-radius: 99px;
            font-size: .62rem;
            letter-spacing: 0;
            font-weight: 600;
        }

        .file-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 .65rem 1rem;
        }

        .file-item {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .5rem .65rem;
            border-radius: var(--radius-xs);
            transition: background .12s;
            min-width: 0;
            margin-bottom: 1px;
            position: relative;
            text-decoration: none;
            color: inherit;
        }
        .file-item:hover { background: var(--surface-2); }
        .file-item.active { background: var(--accent-soft); }
        .file-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 2px;
            background: var(--accent);
            border-radius: 2px;
        }

        .file-item-icon { color: var(--text-4); font-size: .85rem; flex-shrink: 0; }
        .file-item.active .file-item-icon { color: var(--accent); }

        .file-item-name {
            flex: 1;
            font-size: .8rem;
            color: var(--text-2);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
            min-width: 0;
        }
        .file-item.active .file-item-name { color: var(--text); }

        /* ─────────── MAIN ─────────── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            min-width: 0;
            background: var(--bg);
        }

        .topbar {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: 0 1.25rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
            flex-shrink: 0;
            height: var(--topbar-h);
        }

        .icon-btn {
            background: transparent;
            border: 1px solid var(--border-2);
            color: var(--text-2);
            font-size: .9rem;
            padding: 0;
            width: 34px; height: 34px;
            border-radius: var(--radius-sm);
            display: grid;
            place-items: center;
            flex-shrink: 0;
            text-decoration: none;
            transition: background .12s, border-color .12s, color .12s;
        }
        .icon-btn:hover { background: var(--surface-2); border-color: var(--text-4); color: var(--text); }
        .icon-btn.active { background: var(--accent-soft); border-color: rgba(125,211,252,.4); color: var(--accent); }
        .icon-btn.danger-btn:hover { background: var(--red-soft); border-color: rgba(252,165,165,.4); color: var(--red); }

        .mobile-menu-btn { display: none; }

        .topbar-title {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 1px;
            line-height: 1.2;
        }
        .topbar-title small {
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-4);
        }
        .topbar-title strong {
            font-family: 'JetBrains Mono', monospace;
            font-size: .82rem;
            color: var(--text);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Search */
        .search-wrap { display: flex; align-items: center; flex-shrink: 0; }

        /* Hide search-toggle when expanded (desktop) */
        .search-wrap.expanded .search-toggle { display: none; }

        .search-form {
            display: none;
            align-items: center;
            position: relative;
        }
        .search-wrap.expanded .search-form { display: flex; }

        .search-input {
            background: var(--surface);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-sm);
            padding: 0 2.2rem 0 2.1rem;
            color: var(--text);
            font-size: .82rem;
            width: 280px;
            height: 34px;
            outline: none;
            transition: border-color .15s, background .15s;
        }
        .search-input::placeholder { color: var(--text-4); }
        .search-input:focus,
        .search-input:focus-visible {
            outline: none;
            box-shadow: none;
            border-color: var(--text-3);
            background: var(--surface-2);
        }

        .search-form .search-icon {
            position: absolute;
            left: .7rem;
            color: var(--text-3);
            font-size: .85rem;
            pointer-events: none;
        }

        .search-form .search-close {
            position: absolute;
            right: .35rem;
            background: none;
            border: none;
            color: var(--text-4);
            font-size: .85rem;
            padding: 4px;
            border-radius: 4px;
            display: grid;
            place-items: center;
            transition: color .12s, background .12s;
        }
        .search-form .search-close:hover { color: var(--text); background: var(--surface-2); }

        /* ─────────── ENTRIES ─────────── */
        .entries {
            flex: 1;
            overflow-y: auto;
            padding: 1.1rem 1.25rem 2rem;
        }

        .flash {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .7rem .9rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            font-size: .82rem;
            font-weight: 500;
        }
        .flash.success { background: var(--green-soft); color: var(--green); border: 1px solid rgba(134,239,172,.25); }
        .flash.error   { background: var(--red-soft);   color: var(--red);   border: 1px solid rgba(252,165,165,.25); }

        .result-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .55rem .85rem;
            border-radius: var(--radius-sm);
            background: var(--surface);
            border: 1px solid var(--border);
            margin-bottom: 1rem;
            font-size: .8rem;
            color: var(--text-2);
        }
        .result-bar a {
            color: var(--text-3);
            text-decoration: none;
            font-size: .73rem;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .55rem;
            border-radius: var(--radius-xs);
            border: 1px solid var(--border-2);
            transition: all .12s;
        }
        .result-bar a:hover { color: var(--red); border-color: rgba(252,165,165,.3); background: var(--red-soft); }

        /* ─────────── LOG CARDS ─────────── */
        .log-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: .55rem;
            overflow: hidden;
            transition: border-color .15s, box-shadow .15s;
            position: relative;
        }
        .log-card:hover { border-color: var(--border-2); }
        .log-card.open  { border-color: var(--border-2); box-shadow: 0 4px 16px rgba(0,0,0,.18); }

        /* level color via custom prop so message accent can use it too */
        .log-card.level-error,
        .log-card.level-critical,
        .log-card.level-alert     { --level-color: var(--red);    border-left: 3px solid var(--red); }
        .log-card.level-emergency { --level-color: var(--purple); border-left: 3px solid var(--purple); }
        .log-card.level-warning   { --level-color: var(--yellow); border-left: 3px solid var(--yellow); }
        .log-card.level-info,
        .log-card.level-notice    { --level-color: var(--blue);   border-left: 3px solid var(--blue); }
        .log-card.level-debug     { --level-color: var(--text-4); border-left: 3px solid var(--text-4); }

        .log-header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: .7rem;
            padding: .85rem 1rem;
            cursor: pointer;
            user-select: none;
            align-items: start;
        }

        .log-chevron {
            margin-top: .15rem;
            color: var(--text-4);
            font-size: .75rem;
            transition: transform .2s, color .12s;
        }
        .log-card:hover .log-chevron { color: var(--text-3); }
        .log-card.open .log-chevron  { transform: rotate(90deg); color: var(--accent); }

        .log-header-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: .55rem;
        }

        .log-meta-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .lvl {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: .18rem .55rem .18rem .5rem;
            border-radius: 99px;
            font-family: 'JetBrains Mono', monospace;
        }
        .lvl::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }
        .lvl-error, .lvl-critical, .lvl-alert { background: var(--red-soft);    color: var(--red); }
        .lvl-emergency                        { background: var(--purple-soft); color: var(--purple); }
        .lvl-warning                          { background: var(--yellow-soft); color: var(--yellow); }
        .lvl-info, .lvl-notice                { background: var(--blue-soft);   color: var(--blue); }
        .lvl-debug                            { background: rgba(130,137,151,.13); color: var(--text-3); }

        .log-time {
            font-size: .75rem;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-3);
            font-weight: 500;
        }
        .log-env {
            font-size: .67rem;
            color: var(--text-3);
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-xs);
            padding: 1px 6px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
        }

        /* MESSAGE — clean typography with subtle left accent indent (no full border) */
        .log-message {
            font-size: .9rem;
            color: var(--text);
            line-height: 1.6;
            font-weight: 500;
            word-break: break-word;
            overflow-wrap: anywhere;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            position: relative;
            padding: .15rem 0 .15rem .7rem;
        }
        .log-message::before {
            content: '';
            position: absolute;
            left: 0; top: .35rem; bottom: .35rem;
            width: 2px;
            background: var(--level-color, var(--accent));
            opacity: .55;
            border-radius: 2px;
        }
        .log-card.open .log-message {
            -webkit-line-clamp: unset;
            display: block;
        }

        /* badges — always shown in header */
        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            padding-top: .55rem;
            border-top: 1px dashed var(--border-2);
        }

        .badge-row-divider { display: none; }

        .badge {
            display: inline-flex;
            font-size: .68rem;
            font-family: 'JetBrains Mono', monospace;
            border-radius: var(--radius-xs);
            border: 1px solid var(--border-2);
            background: var(--surface-2);
            overflow: hidden;
            max-width: 100%;
        }
        .badge-key {
            font-weight: 700;
            background: var(--surface-3);
            color: var(--text-2);
            padding: 2px 7px;
            letter-spacing: .3px;
            border-right: 1px solid var(--border-2);
        }
        .badge-val {
            color: var(--text);
            padding: 2px 7px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 280px;
        }
        .badge-tenant       { border-color: rgba(134,239,172,.25); }
        .badge-tenant       .badge-key { background: var(--green-soft); color: var(--green); }
        .badge-userid,
        .badge-user_id,
        .badge-user         { border-color: rgba(125,211,252,.25); }
        .badge-userid       .badge-key,
        .badge-user_id      .badge-key,
        .badge-user         .badge-key { background: var(--blue-soft); color: var(--blue); }
        .badge-url,
        .badge-route        { border-color: rgba(252,211,77,.25); }
        .badge-url          .badge-key,
        .badge-route        .badge-key { background: var(--yellow-soft); color: var(--yellow); }
        .badge-method       { border-color: rgba(253,186,116,.25); }
        .badge-method       .badge-key { background: var(--orange-soft); color: var(--orange); }
        .badge-channel,
        .badge-environment,
        .badge-env          { border-color: rgba(94,234,212,.25); }
        .badge-channel      .badge-key,
        .badge-env          .badge-key,
        .badge-environment  .badge-key { background: var(--cyan-soft); color: var(--cyan); }
        .badge-ip,
        .badge-guard,
        .badge-session_id,
        .badge-sessionid,
        .badge-request_id,
        .badge-requestid    { border-color: rgba(216,180,254,.25); }
        .badge-ip           .badge-key,
        .badge-guard        .badge-key,
        .badge-session_id   .badge-key,
        .badge-sessionid    .badge-key,
        .badge-request_id   .badge-key,
        .badge-requestid    .badge-key { background: var(--purple-soft); color: var(--purple); }

        .log-copy-btn {
            background: transparent;
            border: 1px solid var(--border-2);
            color: var(--text-3);
            font-size: .7rem;
            font-weight: 500;
            padding: .3rem .65rem;
            border-radius: var(--radius-xs);
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            transition: all .12s;
            align-self: start;
        }
        .log-copy-btn:hover  { border-color: var(--text-4); color: var(--text); background: var(--surface-2); }
        .log-copy-btn.copied { border-color: rgba(134,239,172,.3); color: var(--green); background: var(--green-soft); }

        /* ─────────── DETAIL ─────────── */
        .log-detail {
            display: none;
            border-top: 1px solid var(--border);
            background: var(--bg);
        }
        .log-card.open .log-detail { display: block; }

        .detail-section {
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .detail-section:last-child { border-bottom: none; }

        .detail-label {
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-4);
            margin-bottom: .55rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }
        .detail-label > span:first-child {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .detail-label-copy {
            background: transparent;
            border: 1px solid var(--border-2);
            color: var(--text-3);
            font-size: .65rem;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 500;
            letter-spacing: normal;
            text-transform: none;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            transition: all .12s;
            flex-shrink: 0;
        }
        .detail-label-copy:hover  { border-color: var(--text-4); color: var(--text); background: var(--surface); }
        .detail-label-copy.copied { border-color: rgba(134,239,172,.3); color: var(--green); background: var(--green-soft); }

        pre.code-block {
            background: var(--bg-soft);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: .8rem 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: .76rem;
            line-height: 1.65;
            color: var(--text-2);
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
        pre.code-block .trace-exception { color: var(--red); font-weight: 600; }
        pre.code-block .trace-caused    { color: var(--yellow); font-weight: 600; }
        pre.code-block .trace-at        { color: var(--text-4); }
        pre.code-block .trace-frame     { color: var(--text-3); }

        .json-tree {
            background: var(--bg-soft);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: .8rem 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: .76rem;
            line-height: 1.7;
            overflow-x: auto;
            white-space: pre;
        }
        .json-key   { color: var(--accent-2); }
        .json-str   { color: var(--green); }
        .json-num   { color: var(--orange); }
        .json-bool  { color: var(--purple); font-weight: 600; }
        .json-null  { color: var(--text-4); font-style: italic; }
        .json-punct { color: var(--text-4); }

        mark.search-highlight {
            background: rgba(252,211,77,.28);
            color: var(--yellow);
            border-radius: 3px;
            padding: 0 3px;
            font-weight: 600;
        }

        .empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .85rem;
            padding: 5rem 2rem;
            color: var(--text-3);
            text-align: center;
        }
        .empty-icon {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--surface);
            border: 1px solid var(--border-2);
            display: grid; place-items: center;
            font-size: 1.4rem;
            color: var(--text-4);
        }
        .empty p { font-size: .88rem; font-weight: 500; }
        .empty small { font-size: .78rem; color: var(--text-4); }

        .overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 50;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed; top: 0; left: 0;
                z-index: 100; height: 100vh;
                transform: translateX(-100%);
                width: 250px;
                box-shadow: 4px 0 20px rgba(0,0,0,.4);
            }
            .sidebar.open { transform: translateX(0); }
            .overlay.show { display: block; }
            .mobile-menu-btn { display: grid; }

            .file-actions { opacity: 1; }

            .topbar { padding: 0 .75rem; gap: .4rem; }

            /* Mobile search expansion: hide title, fill toolbar */
            .topbar.searching .topbar-title,
            .topbar.searching .download-btn { display: none; }
            .topbar.searching .search-wrap { flex: 1; min-width: 0; }
            .topbar.searching .search-form { width: 100%; }
            .topbar.searching .search-input { width: 100%; }

            .topbar-title strong { font-size: .76rem; }
            .topbar-title small { font-size: .58rem; }

            .entries { padding: .8rem; }

            .log-header {
                grid-template-columns: auto 1fr;
                padding: .7rem .8rem;
                padding-right: 2.6rem;
                gap: .55rem;
            }
            .log-header-main { gap: .5rem; }
            .log-message { font-size: .85rem; padding-left: .65rem; }

            .log-meta-row { gap: .35rem; }
            .lvl { font-size: .6rem; padding: .14rem .45rem .14rem .4rem; }
            .log-time { font-size: .68rem; }

            .badge-val { max-width: 130px; }
            .badge { font-size: .62rem; }
            .badge-key, .badge-val { padding: 1px 6px; }

            .log-copy-btn {
                position: absolute;
                top: .55rem;
                right: .55rem;
                width: 30px;
                height: 30px;
                padding: 0;
                justify-content: center;
                font-size: 0;
            }
            .log-copy-btn i { font-size: .85rem; }
            .log-copy-btn.copied { font-size: 0; }

            .detail-section { padding: .7rem .8rem; }
            pre.code-block, .json-tree { font-size: .72rem; padding: .65rem .75rem; line-height: 1.6; }

            .result-bar { font-size: .75rem; padding: .5rem .7rem; }
        }

        @media (max-width: 480px) {
            .badge-val { max-width: 100px; }
            .topbar-title small { display: none; }
        }
    </style>
</head>

<body>

<div class="overlay" id="overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-terminal-fill"></i></div>
        <div class="brand-text">
            <strong>Log Viewer</strong>
            <small>Snawbar</small>
        </div>
    </div>

    <div class="sidebar-section">
        <span>Files</span>
        <span class="count">{{ count($logFiles) }}</span>
    </div>

    <div class="file-list">
        @forelse($logFiles as $file)
            <a href="?file={{ urlencode($file) }}" class="file-item @if($file === $currentFile) active @endif" title="{{ $file }}">
                <i class="bi bi-file-earmark-text file-item-icon"></i>
                <span class="file-item-name">{{ $file }}</span>
            </a>
        @empty
            <div class="empty" style="padding:2rem 1rem">
                <div class="empty-icon"><i class="bi bi-folder-x"></i></div>
                <p>No log files</p>
            </div>
        @endforelse
    </div>
</aside>

<div class="main">
    <div class="topbar" id="topbar">
        <button class="icon-btn mobile-menu-btn" id="mobileMenuBtn" title="Menu"><i class="bi bi-list"></i></button>

        <div class="topbar-title">
            @if($currentFile)
                <small>Viewing</small>
                <strong>{{ $currentFile }}</strong>
            @else
                <strong style="font-family:'Inter',sans-serif;color:var(--text-3);font-weight:500">Select a log file</strong>
            @endif
        </div>

        @if($currentFile)
        <a href="{{ route('log-viewer.download', ['file' => $currentFile]) }}" class="icon-btn download-btn" title="Download log">
            <i class="bi bi-download"></i>
        </a>

        <form action="{{ route('log-viewer.delete') }}" method="POST" class="delete-form download-btn" data-filename="{{ $currentFile }}" style="display:inline">
            @csrf
            @method('DELETE')
            <input type="hidden" name="file" value="{{ $currentFile }}">
            <button type="submit" class="icon-btn danger-btn" title="Delete log"><i class="bi bi-trash3"></i></button>
        </form>

        <div class="search-wrap @if($searchTerm) expanded @endif" id="searchWrap"
             data-clear-url="{{ route('log-viewer.index', ['file' => $currentFile]) }}">
            <button class="icon-btn search-toggle" id="searchToggle" title="Search">
                <i class="bi bi-search"></i>
            </button>
            <form action="{{ route('log-viewer.index') }}" method="GET" id="searchForm" class="search-form">
                <i class="bi bi-search search-icon"></i>
                <input type="hidden" name="file" value="{{ $currentFile }}">
                <input type="text" name="search" id="searchInput"
                       value="{{ $searchTerm }}"
                       placeholder="Search logs..."
                       class="search-input"
                       autocomplete="off">
                <button type="button" class="search-close" id="searchClose" title="Close"><i class="bi bi-x-lg"></i></button>
            </form>
        </div>
        @endif
    </div>

    <div class="entries" id="entries">

        @if(session('success'))
            <div class="flash success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        @if($searchTerm && $currentFile)
        <div class="result-bar">
            <span>
                @if($logEntries->count() > 0)
                    <strong style="color:var(--text)">{{ $logEntries->count() }}</strong> result{{ $logEntries->count() !== 1 ? 's' : '' }} for <em style="color:var(--accent);font-style:normal;font-family:'JetBrains Mono',monospace">"{{ $searchTerm }}"</em>
                @else
                    No results for <em style="color:var(--red);font-style:normal">"{{ $searchTerm }}"</em>
                @endif
            </span>
            <a href="{{ route('log-viewer.index', ['file' => $currentFile]) }}">
                <i class="bi bi-x-lg"></i> Clear
            </a>
        </div>
        @endif

        @forelse($logEntries as $index => $entry)
        @php
            $lvl = strtolower($entry->level);
            $hasContext = $entry->hasContext();
            $hasDetail = $entry->hasStackTrace() || $entry->hasExtra() || $entry->hasLongContext();
        @endphp
        <div class="log-card level-{{ $lvl }}" id="card-{{ $index }}">

            <div class="log-header" onclick="toggleCard({{ $index }}, {{ $hasDetail ? 'true' : 'false' }})">
                <i class="bi bi-chevron-right log-chevron"
                   style="{{ !$hasDetail ? 'visibility:hidden' : '' }}"></i>

                <div class="log-header-main">
                    <div class="log-meta-row">
                        <span class="lvl lvl-{{ $lvl }}">{{ $entry->level }}</span>
                        <span class="log-time">{{ $entry->timestamp }}</span>
                        @if($entry->environment && $entry->environment !== 'production')
                            <span class="log-env">{{ $entry->environment }}</span>
                        @endif
                    </div>

                    <div class="log-message">{!! $entry->highlightSearchTerm($entry->message, $searchTerm) !!}</div>

                    @if($hasContext)
                    <div class="badge-row">
                        <span class="badge-row-divider" aria-hidden="true"></span>
                        @foreach($entry->getContext() as $key => $value)
                        <span class="badge badge-{{ strtolower($key) }}" title="{{ $key }}: {{ $value }}">
                            <span class="badge-key">{{ strtoupper($key) }}</span>
                            <span class="badge-val">{{ $value }}</span>
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                <button class="log-copy-btn"
                        onclick="event.stopPropagation(); copyLog({{ $index }}, this)"
                        title="Copy log">
                    <i class="bi bi-clipboard"></i> <span>Copy</span>
                </button>
            </div>

            @if($hasDetail)
            <div class="log-detail">

                {{-- 1. Message --}}
                <div class="detail-section">
                    <div class="detail-label">
                        <span><i class="bi bi-chat-left-text"></i>Message</span>
                        <button class="detail-label-copy" onclick="copyText('msg-{{ $index }}', this)">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <pre class="code-block" id="msg-{{ $index }}">{{ $entry->message }}</pre>
                </div>

                {{-- 2. Long shared context (Request Input, Extra arrays, etc.) --}}
                @if($entry->hasLongContext())
                    @foreach($entry->getLongContext() as $ctxKey => $ctxValue)
                    <div class="detail-section">
                        <div class="detail-label">
                            <span><i class="bi bi-braces"></i>{{ $ctxKey }}</span>
                            <button class="detail-label-copy" onclick="copyText('ctx-{{ $index }}-{{ $loop->index }}', this)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                        @if(is_array($ctxValue))
                            <div class="json-tree" id="ctx-{{ $index }}-{{ $loop->index }}">{!! jsonHighlight($ctxValue) !!}</div>
                        @else
                            <pre class="code-block" id="ctx-{{ $index }}-{{ $loop->index }}">{{ $ctxValue }}</pre>
                        @endif
                    </div>
                    @endforeach
                @endif

                {{-- 3. Stack Trace --}}
                @if($entry->hasStackTrace())
                <div class="detail-section">
                    <div class="detail-label">
                        <span><i class="bi bi-bug"></i>Stack Trace</span>
                        <button class="detail-label-copy" onclick="copyText('trace-{{ $index }}', this)">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <pre class="code-block" id="trace-{{ $index }}">{!! formatStackTrace($entry->stackTrace) !!}</pre>
                </div>
                @endif

                {{-- 4. Extra (raw) --}}
                @if($entry->hasExtra())
                <div class="detail-section">
                    <div class="detail-label">
                        <span><i class="bi bi-three-dots"></i>Extra</span>
                        <button class="detail-label-copy" onclick="copyText('extra-{{ $index }}', this)">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <pre class="code-block" id="extra-{{ $index }}">{{ $entry->extra }}</pre>
                </div>
                @endif

            </div>
            @endif

        </div>
        @empty
        <div class="empty">
            <div class="empty-icon">
                @if($searchTerm)<i class="bi bi-search"></i>
                @elseif($currentFile)<i class="bi bi-journal-x"></i>
                @else<i class="bi bi-journals"></i>
                @endif
            </div>
            @if($searchTerm)
                <p>No results match your search</p>
                <small>Try different keywords or clear the filter.</small>
            @elseif($currentFile)
                <p>This log file is empty</p>
                <small>No entries to display.</small>
            @else
                <p>Select a log file from the sidebar</p>
                <small>Pick a file to start viewing log entries.</small>
            @endif
        </div>
        @endforelse
    </div>
</div>

@php
function formatStackTrace(string $trace): string {
    $lines = explode("\n", $trace);
    $out = '';
    foreach ($lines as $line) {
        $esc = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        $trim = ltrim($line);
        if (str_starts_with($trim, 'Caused by:')) {
            $out .= '<span class="trace-caused">' . $esc . '</span>';
        } elseif (preg_match('/^\w[\w\\\\]+(?:Exception|Error)(?:\(code:.+\))?:/', $trim) || str_starts_with($trim, 'Illuminate\\\\') || str_starts_with($trim, 'PDOException')) {
            $out .= '<span class="trace-exception">' . $esc . '</span>';
        } elseif (preg_match('/^\s*at /', $line)) {
            $out .= '<span class="trace-at">' . $esc . '</span>';
        } elseif (preg_match('/^\s+#\d+/', $line)) {
            $out .= '<span class="trace-frame">' . $esc . '</span>';
        } else {
            $out .= $esc;
        }
        $out .= "\n";
    }
    return rtrim($out);
}

function jsonHighlight(mixed $value, int $depth = 0): string {
    $ind = str_repeat('  ', $depth);
    $chi = str_repeat('  ', $depth + 1);
    $p   = fn(string $s) => '<span class="json-punct">' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</span>';

    if (is_array($value)) {
        $list = array_is_list($value);
        if (empty($value)) return $p($list ? '[]' : '{}');
        $items = [];
        foreach ($value as $k => $v) {
            $keyPart = $list ? '' : '<span class="json-key">' . htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') . '</span>' . $p(': ');
            $items[] = $chi . $keyPart . jsonHighlight($v, $depth + 1);
        }
        return $p($list ? '[' : '{') . "\n" . implode($p(',') . "\n", $items) . "\n" . $ind . $p($list ? ']' : '}');
    }
    if (is_string($value)) {
        $trimmed = trim($value);
        if (($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '['))) {
            $inner = json_decode($trimmed, true);
            if (is_array($inner)) {
                return jsonHighlight($inner, $depth);
            }
        }
        return '<span class="json-str">' . htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') . '</span>';
    }
    if (is_int($value) || is_float($value)) {
        return '<span class="json-num">' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    if (is_bool($value)) return '<span class="json-bool">' . ($value ? 'true' : 'false') . '</span>';
    return '<span class="json-null">null</span>';
}
@endphp

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const topbar  = document.getElementById('topbar');

    document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    });
    document.querySelectorAll('.file-item').forEach(a => {
        a.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    });

    function toggleCard(index, hasDetail) {
        if (!hasDetail) return;
        document.getElementById(`card-${index}`).classList.toggle('open');
    }

    function copyText(id, btn) {
        const el = document.getElementById(id);
        if (!el) return;
        navigator.clipboard.writeText(el.innerText || el.textContent).then(() => flashCopied(btn));
    }

    function copyLog(index, btn) {
        const msg   = document.getElementById(`msg-${index}`)?.innerText ?? '';
        const trace = document.getElementById(`trace-${index}`)?.innerText ?? '';
        let text = `MESSAGE:\n${msg}`;
        if (trace) text += `\n\nSTACK TRACE:\n${trace}`;
        navigator.clipboard.writeText(text).then(() => flashCopied(btn));
    }

    function flashCopied(btn) {
        const orig = btn.innerHTML;
        const hasLabel = btn.querySelector('span') !== null;
        btn.innerHTML = hasLabel ? '<i class="bi bi-check-lg"></i> <span>Copied</span>' : '<i class="bi bi-check-lg"></i>';
        btn.classList.add('copied');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('copied'); }, 1500);
    }

    // Search
    const searchWrap   = document.getElementById('searchWrap');
    const searchToggle = document.getElementById('searchToggle');
    const searchClose  = document.getElementById('searchClose');
    const searchInput  = document.getElementById('searchInput');
    const searchForm   = document.getElementById('searchForm');

    function isMobile() { return window.matchMedia('(max-width: 768px)').matches; }

    function expandSearch() {
        searchWrap?.classList.add('expanded');
        if (isMobile()) topbar.classList.add('searching');
        setTimeout(() => searchInput?.focus(), 50);
    }

    function closeSearch() {
        // If a search was active server-side, navigate to clean URL to clear it
        const had = searchInput && searchInput.defaultValue.trim() !== '';
        if (searchInput) searchInput.value = '';
        searchWrap?.classList.remove('expanded');
        topbar.classList.remove('searching');
        if (had && searchWrap?.dataset.clearUrl) {
            window.location.href = searchWrap.dataset.clearUrl;
        }
    }

    searchToggle?.addEventListener('click', expandSearch);
    searchClose?.addEventListener('click', closeSearch);

    if (searchInput) {
        let t;
        searchInput.addEventListener('input', function () {
            clearTimeout(t);
            if (this.value.trim()) t = setTimeout(() => searchForm.submit(), 550);
        });
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(t); searchForm.submit(); }
        });
    }

    // Auto-expand if search active on load (mobile too)
    if (searchInput && searchInput.value.trim()) {
        if (isMobile()) topbar.classList.add('searching');
    }

    // delete confirm
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = this.dataset.filename;
            Swal.fire({
                title: 'Delete log file?',
                html: `<code style="font-size:.85rem;color:#fca5a5">${name}</code>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fca5a5',
                cancelButtonColor: '#505868',
                confirmButtonText: 'Delete',
                background: '#343a47',
                color: '#e4e7ee',
            }).then(r => { if (r.isConfirmed) this.submit(); });
        });
    });
</script>
</body>
</html>