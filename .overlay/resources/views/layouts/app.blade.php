<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', ($profile ?? null)?->name ?? config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Personal site and blog')">

    <script>
        (function () {
            try {
                var t = localStorage.getItem('sa-theme') || 'light';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Lora:wght@400;600&display=swap">

    <style>
        :root {
            --color-bg: #f3f2f2;
            --color-surface: #eae9e9;
            --color-text: #201f1d;
            --color-accent: #b68235;
            --color-accent-2: #ac803e;
            --color-divider: color-mix(in srgb, #201f1d 16%, transparent);

            --color-neutral-300: #d7d3d3;
            --color-neutral-400: #bab6b6;
            --color-neutral-600: #7d7979;
            --color-neutral-700: #605d5d;

            --color-accent-100: #fff3e4;
            --color-accent-200: #ffe3bf;
            --color-accent-700: #7d5411;

            --font-heading: "Cormorant Garamond", system-ui, sans-serif;
            --font-body: "Lora", system-ui, sans-serif;

            --radius-sm: 2px;
            --radius-md: 4px;
            --radius-lg: 7px;
        }

        :root[data-theme="dark"] {
            --color-bg: #1a1918;
            --color-surface: #232120;
            --color-text: #ece8e2;
            --color-accent: #d7a75f;
            --color-divider: color-mix(in srgb, #ece8e2 20%, transparent);
            --color-neutral-300: #4a4746;
            --color-neutral-400: #6b6766;
            --color-neutral-600: #a09a97;
            --color-neutral-700: #bdb7b3;
            --color-accent-700: #e6bd80;
            --color-accent-100: #2a2420;
            --color-accent-200: #3a3128;
        }

        * , *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--color-bg);
            color: var(--color-text);
            font-family: var(--font-body);
            font-size: 15px;
            line-height: 1.55;
            transition: background .25s ease, color .25s ease;
        }
        h1, h2, h3, h4 { font-family: var(--font-heading); font-weight: 600; margin: 0; }
        a { color: var(--color-accent-700); text-decoration-thickness: 1px; text-underline-offset: 3px; }
        a:hover { color: var(--color-accent); }
        ::selection { background: color-mix(in srgb, var(--color-accent) 30%, transparent); }
        :focus-visible { outline: 2px solid var(--color-accent); outline-offset: 2px; }
        .nav-link { transition: color .15s ease, background .15s ease; }
        .nav-link:hover { color: var(--color-accent) !important; background: var(--color-accent-100) !important; }
        .pill-link:hover { border-color: var(--color-accent) !important; color: var(--color-accent) !important; }
        .row-link:hover { background: var(--color-accent-100) !important; }
        .theme-btn:hover { border-color: var(--color-accent) !important; color: var(--color-accent) !important; }
        .btn-outline:hover { background: var(--color-accent-100) !important; }
        .filter-btn { font-family: var(--font-body); }
    </style>

    @stack('head')
</head>
<body>

<div style="min-height:100vh;background:var(--color-bg);color:var(--color-text);font-family:var(--font-body)">

    <header style="position:sticky;top:0;z-index:10;background:var(--color-bg);border-bottom:1px solid var(--color-divider)">
        <nav style="max-width:720px;margin:0 auto;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
            <a href="{{ route('home') }}" style="font-family:var(--font-heading);font-weight:600;font-size:19px;letter-spacing:.01em;color:var(--color-text);text-decoration:none">
                {{ ($profile ?? null)?->name ?? 'Your Name' }}
            </a>
            <div style="display:flex;align-items:center;gap:4px">
                <a href="{{ route('blog.index') }}" class="nav-link" style="font-size:14px;padding:6px 10px;border-radius:var(--radius-md);color:var(--color-neutral-700);text-decoration:none">Blog</a>
                <a href="{{ route('projects') }}" class="nav-link" style="font-size:14px;padding:6px 10px;border-radius:var(--radius-md);color:var(--color-neutral-700);text-decoration:none">Projects</a>
                <a href="{{ route('contact') }}" class="nav-link" style="font-size:14px;padding:6px 10px;border-radius:var(--radius-md);color:var(--color-neutral-700);text-decoration:none">Contact</a>
                <button type="button" onclick="saToggleTheme()" aria-label="Toggle theme" class="theme-btn" style="margin-left:6px;width:32px;height:32px;display:grid;place-items:center;background:transparent;border:1px solid var(--color-divider);border-radius:var(--radius-md);color:var(--color-neutral-700);cursor:pointer;font-size:13px;font-family:var(--font-body)">
                    <span id="sa-theme-glyph">☀</span>
                </button>
            </div>
        </nav>
    </header>

    <main style="max-width:720px;margin:0 auto;padding:0 20px 96px">
        @yield('content')
    </main>

    <footer style="border-top:1px solid var(--color-divider)">
        <div style="max-width:720px;margin:0 auto;padding:22px 20px;display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between">
            <span style="font-size:12px;color:var(--color-neutral-600)">© {{ date('Y') }} {{ ($profile ?? null)?->name ?? '' }}</span>
            <span style="font-size:12px;color:var(--color-neutral-600)">{{ ($profile ?? null)?->footer_location }}</span>
        </div>
    </footer>
</div>

<script>
    function saApplyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        var glyph = document.getElementById('sa-theme-glyph');
        if (glyph) glyph.textContent = t === 'dark' ? '☾' : '☀';
    }
    function saToggleTheme() {
        var cur = document.documentElement.getAttribute('data-theme') || 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        saApplyTheme(next);
        try { localStorage.setItem('sa-theme', next); } catch (e) {}
    }
    saApplyTheme(document.documentElement.getAttribute('data-theme') || 'light');
</script>

@stack('scripts')
</body>
</html>
