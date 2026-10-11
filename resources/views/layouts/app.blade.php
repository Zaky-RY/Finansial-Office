<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="web_dashboard" name="shell-type"/>
    <title>@yield('title', 'Finance Office — Enterprise Treasury & Ledger')</title>

    <!-- Typography: Hanken Grotesk, JetBrains Mono, & Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,300..900;1,300..900&family=JetBrains+Mono:wght@400;500;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet"/>

    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
            main > :first-child { margin-top: 0 !important; }
            main > :last-child { margin-bottom: 0 !important; }
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c7c4d8; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #777587; }

        /* Legacy helper compatibility classes styled with Stitch tokens */
        .card {
            background-color: #ffffff;
            border: 1px solid rgba(199, 196, 216, 0.5);
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
        }
        .dark .card {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .stat {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0b1c30;
        }
        .dark .stat { color: #ffffff; }
        .muted { color: #777587; font-size: 0.875rem; }
        .dark .muted { color: #94a3b8; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #3525cd;
            color: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(53, 37, 205, 0.2);
        }
        .btn-primary:hover { background-color: #4f46e5; }
        .btn-secondary {
            background-color: #ffffff;
            border-color: #c7c4d8;
            color: #0b1c30;
        }
        .dark .btn-secondary {
            background-color: #1e293b;
            border-color: #475569;
            color: #f8fafc;
        }
        .btn-secondary:hover { background-color: #eff4ff; }
        .btn-danger {
            background-color: #ba1a1a;
            color: #ffffff;
        }
        .btn-danger:hover { background-color: #93000a; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            background-color: #eff4ff;
            color: #005137;
            border: 1px solid #82f5c1;
        }
        .alert-error {
            background-color: #ffdad6;
            color: #93000a;
            border-color: #ba1a1a;
        }
        .grid { display: grid; gap: 1rem; }
        .grid-3 { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    </style>

    <!-- Tailwind CSS with Stitch Executive Ledger Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "secondary": "#006c4a",
            "surface-container-lowest": "#ffffff",
            "primary-container": "#4f46e5",
            "on-secondary-fixed": "#002114",
            "on-primary": "#ffffff",
            "primary-fixed": "#e2dfff",
            "on-secondary-fixed-variant": "#005137",
            "surface-container": "#e5eeff",
            "surface-dim": "#cbdbf5",
            "on-tertiary-fixed-variant": "#920028",
            "surface-container-highest": "#d3e4fe",
            "on-primary-fixed": "#0f0069",
            "background": "#f8f9ff",
            "on-error": "#ffffff",
            "error": "#ba1a1a",
            "on-secondary": "#ffffff",
            "tertiary": "#950029",
            "primary-fixed-dim": "#c3c0ff",
            "surface-variant": "#d3e4fe",
            "inverse-surface": "#213145",
            "primary": "#3525cd",
            "on-tertiary-container": "#ffd0d2",
            "secondary-fixed": "#85f8c4",
            "on-secondary-container": "#00714e",
            "inverse-on-surface": "#eaf1ff",
            "on-error-container": "#93000a",
            "tertiary-fixed-dim": "#ffb3b6",
            "tertiary-container": "#c20038",
            "secondary-container": "#82f5c1",
            "surface-container-high": "#dce9ff",
            "surface": "#f8f9ff",
            "on-background": "#0b1c30",
            "secondary-fixed-dim": "#68dba9",
            "outline": "#777587",
            "on-surface-variant": "#464555",
            "on-surface": "#0b1c30",
            "inverse-primary": "#c3c0ff",
            "on-primary-container": "#dad7ff",
            "on-tertiary": "#ffffff",
            "on-tertiary-fixed": "#40000c",
            "error-container": "#ffdad6",
            "outline-variant": "#c7c4d8",
            "surface-container-low": "#eff4ff",
            "surface-tint": "#4d44e3",
            "surface-bright": "#f8f9ff",
            "tertiary-fixed": "#ffdada",
            "on-primary-fixed-variant": "#3323cc"
          },
          "borderRadius": {
            "DEFAULT": "0.125rem",
            "lg": "0.25rem",
            "xl": "0.5rem",
            "full": "0.75rem"
          },
          "spacing": {
            "gutter": "1rem",
            "space-lg": "1rem",
            "space-xl": "1.5rem",
            "space-xs": "0.25rem",
            "margin": "1rem",
            "margin-desktop": "2rem",
            "space-md": "0.75rem",
            "gutter-desktop": "1.5rem",
            "space-sm": "0.5rem"
          },
          "fontFamily": {
            "headline-sm": ["Hanken Grotesk", "sans-serif"],
            "headline-md": ["Hanken Grotesk", "sans-serif"],
            "body-sm": ["Hanken Grotesk", "sans-serif"],
            "label-data": ["JetBrains Mono", "monospace"],
            "headline-lg": ["Hanken Grotesk", "sans-serif"],
            "display": ["Hanken Grotesk", "sans-serif"],
            "body-lg": ["Hanken Grotesk", "sans-serif"],
            "label-data-sm": ["JetBrains Mono", "monospace"],
            "caption": ["Hanken Grotesk", "sans-serif"],
            "body-md": ["Hanken Grotesk", "sans-serif"],
            "headline-lg-mobile": ["Hanken Grotesk", "sans-serif"]
          },
          "fontSize": {
            "headline-sm": ["1rem", { "lineHeight": "1.5rem", "letterSpacing": "-0.005em", "fontWeight": "600" }],
            "headline-md": ["1.25rem", { "lineHeight": "1.75rem", "letterSpacing": "-0.01em", "fontWeight": "600" }],
            "body-sm": ["0.75rem", { "lineHeight": "1rem", "fontWeight": "400" }],
            "label-data": ["0.875rem", { "lineHeight": "1.25rem", "letterSpacing": "-0.01em", "fontWeight": "500" }],
            "headline-lg": ["1.875rem", { "lineHeight": "2.25rem", "letterSpacing": "-0.02em", "fontWeight": "600" }],
            "display": ["2.25rem", { "lineHeight": "2.5rem", "letterSpacing": "-0.03em", "fontWeight": "700" }],
            "body-lg": ["1rem", { "lineHeight": "1.5rem", "fontWeight": "400" }],
            "label-data-sm": ["0.75rem", { "lineHeight": "1rem", "letterSpacing": "0", "fontWeight": "500" }],
            "caption": ["0.6875rem", { "lineHeight": "0.875rem", "letterSpacing": "0.04em", "fontWeight": "500" }],
            "body-md": ["0.875rem", { "lineHeight": "1.25rem", "fontWeight": "400" }],
            "headline-lg-mobile": ["1.5rem", { "lineHeight": "2rem", "letterSpacing": "-0.015em", "fontWeight": "600" }]
          }
        }
      }
    };
    </script>
</head>
<body class="bg-background dark:bg-slate-900 text-on-surface dark:text-slate-100 font-body-md text-body-md min-h-screen antialiased selection:bg-primary selection:text-on-primary">

    <!-- Mobile Navigation Backdrop Overlay -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- 1. STITCH FIXED TOP HEADER BAR (h-16) -->
    <header class="fixed top-0 left-0 right-0 z-50 h-16 bg-surface-container-lowest/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-outline-variant/30 dark:border-slate-800">
        <div class="w-full h-16 px-space-md sm:px-space-xl flex items-center justify-between gap-space-lg">
            
            <!-- Left Header: Mobile Toggle & Brand Logo -->
            <div class="flex items-center gap-space-md">
                <button aria-label="Toggle Navigation" class="p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container dark:hover:bg-slate-800 md:hidden transition-colors" onclick="toggleSidebar()" type="button">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
                <div class="flex items-center gap-space-sm">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary text-on-primary flex items-center justify-center font-headline-sm font-bold shadow-xs">
                            <span class="material-symbols-outlined text-xl">account_balance</span>
                        </div>
                        <div class="flex items-center gap-space-xs">
                            <span class="font-headline-md text-headline-md text-on-surface dark:text-white tracking-tight">Finance Office</span>
                            <span class="font-caption text-caption uppercase tracking-wider px-space-xs py-0.5 rounded-lg bg-surface-container-high dark:bg-slate-800 text-primary dark:text-primary-fixed font-semibold border border-outline-variant/40 dark:border-slate-700">Enterprise</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Right Header: Global Search, Quick Tools, Theme Toggle, & User Profile -->
            <div class="flex items-center gap-space-sm sm:gap-space-md">
                
                <!-- Search Bar with Keyboard Shortcut -->
                <div class="relative hidden sm:flex items-center">
                    <span class="material-symbols-outlined absolute left-2.5 text-outline text-lg pointer-events-none">search</span>
                    <input id="globalSearchInput" class="w-56 lg:w-64 pl-8 pr-10 py-1.5 bg-surface-container-low dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-body-sm font-body-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cari transaksi, laporan..." type="text"/>
                    <kbd class="absolute right-2 font-label-data-sm text-caption text-outline bg-surface-container dark:bg-slate-700 px-1.5 py-0.5 rounded border border-outline-variant/50 dark:border-slate-600">⌘K</kbd>
                </div>

                <div class="h-6 w-px bg-outline-variant/30 dark:bg-slate-700 hidden sm:block"></div>

                <!-- Notifications Button with Badge Counter -->
                <a href="{{ route('dashboard') }}" aria-label="Notifikasi" class="relative p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-xl">notifications</span>
                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-error text-on-error font-caption text-[10px] font-bold">
                        {{ auth()->user()->unreadNotificationsCount ?? 3 }}
                    </span>
                </a>

                <!-- Dark Mode Toggle Button -->
                <button aria-label="Ubah Tema" class="p-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container dark:hover:bg-slate-800 transition-colors" onclick="document.documentElement.classList.toggle('dark')" type="button">
                    <span class="material-symbols-outlined text-xl">dark_mode</span>
                </button>

                <div class="h-6 w-px bg-outline-variant/30 dark:bg-slate-700"></div>

                <!-- User Profile Pill & Quick Actions -->
                <div class="relative group">
                    <div class="flex items-center gap-space-sm pl-space-xs cursor-pointer">
                        <div class="relative">
                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary dark:bg-primary-fixed/20 dark:text-primary-fixed ring-1 ring-outline-variant/40 flex items-center justify-center font-headline-sm text-body-sm font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-secondary ring-2 ring-surface-container-lowest dark:ring-slate-900" title="Online"></span>
                        </div>
                        <div class="hidden lg:flex flex-col text-left">
                            <span class="font-headline-sm text-body-sm text-on-surface dark:text-slate-100 group-hover:text-primary transition-colors leading-tight">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="font-caption text-caption text-outline dark:text-slate-400 capitalize leading-tight">
                                {{ auth()->user()->role }} @if(auth()->user()->department) &bull; {{ auth()->user()->department }} @endif
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-outline text-base group-hover:text-on-surface transition-colors hidden lg:inline">expand_more</span>
                    </div>

                    <!-- Profile Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-56 bg-surface-container-lowest dark:bg-slate-800 rounded-xl shadow-lg border border-outline-variant/40 dark:border-slate-700 py-1 hidden group-hover:block transition-all z-50">
                        <div class="px-4 py-2 border-b border-outline-variant/20 dark:border-slate-700">
                            <p class="text-body-sm font-semibold text-on-surface dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-caption font-label-data-sm text-outline truncate">{{ auth()->user()->email }}</p>
                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-surface-container-high dark:bg-slate-700 text-primary dark:text-primary-fixed">
                                {{ auth()->user()->role }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="p-1">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-body-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-base">logout</span>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- 2. STITCH FIXED LEFT SIDEBAR (w-64) -->
    <aside id="sidebarNav" class="fixed left-0 top-16 bottom-0 w-64 z-40 bg-surface-container-lowest dark:bg-slate-900 border-r border-outline-variant/30 dark:border-slate-800 flex flex-col justify-between overflow-y-auto transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
        <div class="p-space-md">
            
            <div class="px-space-sm pb-space-sm pt-space-xs">
                <p class="font-caption text-caption uppercase tracking-wider text-outline dark:text-slate-400 font-semibold">Main Navigation</p>
            </div>

            <!-- Navigation Links Matrix -->
            <nav class="flex flex-col gap-1">
                
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                    <div class="flex items-center gap-space-md">
                        <span class="material-symbols-outlined text-xl">grid_view</span>
                        <span>Dashboard</span>
                    </div>
                </a>

                <!-- Transaksi (Finance, Manager, Director, Admin) -->
                @if(auth()->user()->isFinance() || auth()->user()->isDirector() || auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('transactions.index') }}" 
                       class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('transactions.*') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                        <div class="flex items-center gap-space-md">
                            <span class="material-symbols-outlined text-xl">receipt_long</span>
                            <span>Transaksi</span>
                        </div>
                    </a>
                @endif

                <!-- Reimbursement -->
                <a href="{{ route('reimbursements.index') }}" 
                   class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('reimbursements.*') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                    <div class="flex items-center gap-space-md">
                        <span class="material-symbols-outlined text-xl">credit_card</span>
                        <span>Reimbursement</span>
                    </div>
                    @if(auth()->user()->isManager() || auth()->user()->isFinance() || auth()->user()->isDirector())
                        <span class="font-caption text-caption font-bold px-1.5 py-0.5 rounded-full bg-error-container text-on-error-container">
                            {{ \App\Models\Reimbursement::whereIn('status', ['waiting_manager_approval', 'waiting_finance_verification', 'waiting_director_approval'])->count() }}
                        </span>
                    @endif
                </a>

                <!-- Antrean Approval (Manager, Finance, Director) -->
                @if(auth()->user()->isManager() || auth()->user()->isFinance() || auth()->user()->isDirector())
                    <a href="{{ route('approvals.index') }}" 
                       class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('approvals.*') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                        <div class="flex items-center gap-space-md">
                            <span class="material-symbols-outlined text-xl">payments</span>
                            <span>Permintaan Dana</span>
                        </div>
                    </a>
                @endif

                <!-- Anggaran (Finance, Manager, Director, Admin) -->
                @if(auth()->user()->isFinance() || auth()->user()->isDirector() || auth()->user()->isAdmin() || auth()->user()->isManager())
                    <a href="{{ route('budgets.index') }}" 
                       class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('budgets.*') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                        <div class="flex items-center gap-space-md">
                            <span class="material-symbols-outlined text-xl">pie_chart</span>
                            <span>Anggaran</span>
                        </div>
                    </a>

                    <!-- Laporan -->
                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('reports.*') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                        <div class="flex items-center gap-space-md">
                            <span class="material-symbols-outlined text-xl">summarize</span>
                            <span>Laporan</span>
                        </div>
                    </a>
                @endif

                <!-- Master Data / Pengaturan (Admin) -->
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.settings') }}" 
                       class="flex items-center justify-between px-space-md py-2 rounded-lg transition-all {{ request()->routeIs('admin.settings') ? 'bg-primary text-on-primary font-semibold shadow-xs' : 'font-body-md text-body-md text-on-surface-variant dark:text-slate-300 hover:bg-surface-container dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }}">
                        <div class="flex items-center gap-space-md">
                            <span class="material-symbols-outlined text-xl">database</span>
                            <span>Master Data</span>
                        </div>
                    </a>
                @endif

            </nav>
        </div>

        <!-- Sidebar Bottom Footer: System Status & Support -->
        <div class="p-space-md border-t border-outline-variant/30 dark:border-slate-800 flex flex-col gap-space-sm bg-surface-container-low/50 dark:bg-slate-900/50">
            <div class="flex items-center justify-between px-space-sm py-1.5 rounded-lg bg-surface-container dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
                    </span>
                    <span class="font-caption text-caption font-semibold text-on-surface dark:text-slate-200">System Status</span>
                </div>
                <span class="font-caption text-caption text-secondary font-bold uppercase tracking-wider">Online</span>
            </div>
            
            <a href="#" class="flex items-center gap-space-md px-space-sm py-1.5 rounded-lg font-body-sm text-body-sm text-on-surface-variant dark:text-slate-400 hover:text-on-surface dark:hover:text-white hover:bg-surface-container dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined text-lg">support_agent</span>
                <span>Help &amp; Support</span>
            </a>
        </div>
    </aside>

    <!-- 3. STITCH MAIN WORKSPACE CANVAS -->
    <div class="md:pl-64 flex flex-col min-h-screen">
        <main class="relative pt-16 w-full flex-1 bg-background dark:bg-slate-900">
            <div class="p-space-md sm:p-space-lg lg:p-space-xl max-w-[1600px] mx-auto w-full flex flex-col gap-space-lg">
                
                <!-- Flash Notification Banner -->
                @if(session('success'))
                    <div class="p-space-md rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-space-md shadow-xs" role="status">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
                            <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
                        </div>
                        <button type="button" class="text-emerald-600 hover:text-emerald-800" onclick="this.parentElement.remove()">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-space-md rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 flex items-center justify-between gap-space-md shadow-xs" role="alert">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-600 dark:text-rose-400">error</span>
                            <span class="font-body-md text-body-md font-medium">{{ session('error') }}</span>
                        </div>
                        <button type="button" class="text-rose-600 hover:text-rose-800" onclick="this.parentElement.remove()">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-space-md rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-xs" role="alert">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="material-symbols-outlined text-rose-600">warning</span>
                            <span class="font-headline-sm text-body-md font-bold">Terdapat beberapa kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside text-body-sm pl-4">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Dynamic Page Content Insertion -->
                @yield('content')

            </div>
        </main>
    </div>

    <!-- Client-Side Interactive Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarNav');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            }
        }

        // Global Command+K shortcut to focus search
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                const search = document.getElementById('globalSearchInput');
                if (search) search.focus();
            }
        });
    </script>
</body>
</html>
