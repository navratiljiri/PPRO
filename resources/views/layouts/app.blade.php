<!DOCTYPE html>
<html lang="cs" class="h-full bg-[#faf8f5]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Katalog kurzů' }} – Akademie Trutnov</title>
    
    <!-- Google Fonts: Commissioner (Headings) & Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@500;600;700;800;900&family=Inter:ital,opsz,wght@0,14..32,300..700;1,14..32,400..600&display=swap" rel="stylesheet">
    
    <!-- Vite assets (Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #faf8f5;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Commissioner', 'Inter', sans-serif;
        }
        button,
        [type='button'],
        [type='reset'],
        [type='submit'] {
            cursor: pointer;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-[#1c1917] bg-[#faf8f5] antialiased selection:bg-[#ebdfd1] selection:text-[#3d2b17]">

    <!-- Horní oznamovací lišta -->
    <div class="bg-[#e59b10] text-[#2c1a02] text-xs font-semibold py-1.5 px-3 sm:px-4 text-center tracking-wide flex items-center justify-center gap-2">
        <span class="truncate">Akademie Trutnov – Vzdělávací centrum</span>
        <span class="hidden sm:inline opacity-30">/</span>
        <span class="hidden sm:inline font-normal opacity-90">Informační systém pro správu kurzů a osvědčení</span>
    </div>

    <!-- Horní navigační lišta -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-2 sm:gap-4">
                
                <!-- Logo a identita -->
                <a href="{{ route('courses.index') }}" class="flex flex-col shrink min-w-0 cursor-pointer select-none">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="font-extrabold text-[#1c1917] tracking-tight text-sm sm:text-base uppercase truncate">Akademie Trutnov</span>
                        <span class="hidden xs:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-[#f2eee9] text-[#78716c] border border-[#e5dfd5]">ZS 26/27</span>
                    </div>
                    <span class="text-[10px] sm:text-[11px] text-[#78716c] font-medium truncate">Kurzy, termíny a certifikace</span>
                </a>

                <!-- Segmentovaná navigační lišta (Tab UI element - Desktop) -->
                <nav class="hidden md:flex items-center bg-[#f2eee9] p-1 rounded-xl border border-[#ebe7df]">
                    <a href="{{ route('courses.index') }}" 
                       class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all {{ request()->routeIs('courses.*') ? 'bg-white text-[#1c1917] shadow-xs font-bold border border-[#ebe7df]/60' : 'text-[#78716c] hover:text-[#1c1917] hover:bg-white/60' }}">
                        <span>Katalog kurzů</span>
                    </a>
                    <a href="{{ route('terms.index') }}" 
                       class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all {{ request()->routeIs('terms.*') ? 'bg-white text-[#1c1917] shadow-xs font-bold border border-[#ebe7df]/60' : 'text-[#78716c] hover:text-[#1c1917] hover:bg-white/60' }}">
                        <span>Termíny kurzů</span>
                    </a>
                    <a href="{{ route('lecturers.index') }}" 
                       class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition-all {{ request()->routeIs('lecturers.*') ? 'bg-white text-[#1c1917] shadow-xs font-bold border border-[#ebe7df]/60' : 'text-[#78716c] hover:text-[#1c1917] hover:bg-white/60' }}">
                        <span>Lektoři</span>
                    </a>
                    <span class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#a8a29e] cursor-not-allowed select-none opacity-60" title="Bude implementováno v další fázi (Fáze 4)">
                        <span>Osvědčení</span>
                        <span class="text-[9px] font-mono uppercase bg-[#e5dfd5] text-[#78716c] px-1.5 py-0.5 rounded">Fáze 4</span>
                    </span>
                </nav>

                <!-- Pravá sekce: Tlačítko akce a Hamburger -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('courses.create') }}" 
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-4 sm:py-2 rounded-xl bg-[#e8dbcc] hover:bg-[#decaba] text-[#3d2b17] border border-[#d8c5b0] text-xs font-semibold tracking-wide cursor-pointer transition-all shadow-xs active:scale-[0.98]">
                        <svg class="w-3.5 h-3.5 text-[#3d2b17]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="hidden sm:inline">Nový kurz</span>
                        <span class="sm:hidden text-[11px]">Nový</span>
                    </a>

                    <!-- Tlačítko mobilního menu -->
                    <button type="button" 
                            id="mobile-nav-toggle"
                            onclick="document.getElementById('mobile-nav-menu').classList.toggle('hidden')"
                            class="md:hidden p-2 rounded-xl border border-[#ebe7df] text-[#78716c] hover:text-[#1c1917] hover:bg-[#f2eee9] cursor-pointer"
                            aria-label="Otevřít menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobilní rozbalovací menu -->
        <div id="mobile-nav-menu" class="hidden md:hidden border-t border-[#ebe7df] bg-[#faf8f5] px-4 py-3 space-y-1 shadow-md">
            <a href="{{ route('courses.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer {{ request()->routeIs('courses.*') ? 'bg-white text-[#1c1917] border border-[#ebe7df] font-bold shadow-xs' : 'text-[#78716c] hover:bg-white/60' }}">
                <span>Katalog kurzů</span>
            </a>
            <a href="{{ route('terms.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer {{ request()->routeIs('terms.*') ? 'bg-white text-[#1c1917] border border-[#ebe7df] font-bold shadow-xs' : 'text-[#78716c] hover:bg-white/60' }}">
                <span>Termíny kurzů</span>
            </a>
            <a href="{{ route('lecturers.index') }}" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer {{ request()->routeIs('lecturers.*') ? 'bg-white text-[#1c1917] border border-[#ebe7df] font-bold shadow-xs' : 'text-[#78716c] hover:bg-white/60' }}">
                <span>Lektoři</span>
            </a>
            <div class="flex items-center justify-between px-3.5 py-2 text-sm font-medium text-[#a8a29e] select-none opacity-60">
                <span>Osvědčení</span>
                <span class="text-[10px] font-mono uppercase bg-[#e5dfd5] text-[#78716c] px-1.5 py-0.5 rounded">Fáze 4</span>
            </div>
            <div class="pt-2 border-t border-[#ebe7df] mt-2">
                <a href="{{ route('courses.create') }}" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-[#e8dbcc] text-[#3d2b17] border border-[#d8c5b0] text-xs font-semibold">
                    <svg class="w-3.5 h-3.5 text-[#3d2b17]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Vytvořit nový kurz</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Flash zprávy -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold">Ve formuláři se vyskytly chyby:</h3>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 pl-11 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Hlavní obsah stránky -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        {{ $slot }}
    </main>

    <!-- Patička -->
    <footer class="mt-auto border-t border-[#ebe7df] bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#78716c]">
            <div>
                <strong class="text-[#1c1917]">Akademie Trutnov</strong> – Semestrální projekt Pokročilé programování (PPRO) 2026/2027
            </div>
            <div class="flex items-center gap-4 text-[#a8a29e]">
                <span>Klient: Ing. Lucie Bartošová</span>
                <span class="opacity-30">/</span>
                <span>Třívrstvá architektura (PPRO Minimum)</span>
            </div>
        </div>
    </footer>

</body>
</html>
