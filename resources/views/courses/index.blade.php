<x-layouts.app title="Katalog kurzů">

    <!-- Horní sekce s nadpisem a statistikami -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#ebe7df]">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1c1917] tracking-tight">Katalog a správa kurzů</h1>
                <p class="mt-1 text-xs sm:text-sm text-[#78716c]">
                    Správa vzdělávacích programů, rekvalifikačních a firemních kurzů Akademie Trutnov.
                </p>
            </div>
            <div class="shrink-0 self-start sm:self-auto">
                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl bg-[#1c1917] text-[#faf8f5] hover:bg-[#2e2925] border border-[#1c1917] shadow-xs transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Vytvořit nový kurz</span>
                </a>
            </div>
        </div>

        <!-- Karty statistik -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6">
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78716c]">Aktivní kurzy</div>
                <div class="text-2xl font-extrabold text-[#1c1917] mt-1 font-heading">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#a8a29e] mt-1">v nabídce centra</div>
            </div>
            <div class="bg-[#f8faf8] p-4 sm:p-5 rounded-2xl border border-[#cde0cf] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#215a28]">Akreditované</div>
                <div class="text-2xl font-extrabold text-[#215a28] mt-1 font-heading">{{ $stats['accredited'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#2e7d32]/80 mt-1">s osvědčením MŠMT</div>
            </div>
            <div class="bg-[#fdf9f4] p-4 sm:p-5 rounded-2xl border border-[#eddcc8] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#854d0e]">Firemní & Ostatní</div>
                <div class="text-2xl font-extrabold text-[#854d0e] mt-1 font-heading">{{ $stats['non_accredited'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#854d0e]/80 mt-1">specializovaná školení</div>
            </div>
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78716c]">Průměrná dotace</div>
                <div class="text-2xl font-extrabold text-[#1c1917] mt-1 font-heading">{{ $stats['average_hours'] ?? 0 }} <span class="text-xs sm:text-sm font-medium text-[#78716c]">hod</span></div>
                <div class="text-[11px] sm:text-xs text-[#a8a29e] mt-1">na jeden kurz</div>
            </div>
        </div>
    </div>

    <!-- Filtrovací a vyhledávací panel -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)] mb-6">
        <form method="GET" action="{{ route('courses.index') }}" class="flex flex-col lg:flex-row gap-3 sm:gap-4 items-stretch lg:items-center justify-between">
            <!-- Textové vyhledávání (rozšířené, adaptivní šířka) -->
            <div class="relative flex-1 min-w-0 max-w-2xl">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#a8a29e]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search ?? '' }}" 
                    placeholder="Hledat podle názvu, kódu či anotace..." 
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/60 text-[#1c1917] placeholder:text-[#a8a29e]"
                >
            </div>

            <!-- Tlačítka rychlého filtru (responzivní s flex-wrap) -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 shrink-0">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#78716c] hidden sm:inline mr-1">Filtr:</span>
                <a href="{{ route('courses.index', ['search' => $search]) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ empty($filter) ? 'bg-[#1c1917] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Všechny kurzy
                </a>
                <a href="{{ route('courses.index', ['filter' => 'accredited', 'search' => $search]) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ $filter === 'accredited' ? 'bg-[#215a28] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Pouze akreditované
                </a>
                <a href="{{ route('courses.index', ['filter' => 'non_accredited', 'search' => $search]) }}" 
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold {{ $filter === 'non_accredited' ? 'bg-[#854d0e] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Ostatní / Firemní
                </a>

                @if(!empty($search) || !empty($filter))
                    <a href="{{ route('courses.index') }}" class="px-2.5 py-2 text-[#78716c] hover:text-[#1c1917] hover:bg-[#f2eee9] rounded-xl text-xs font-medium transition-colors ml-1" title="Zrušit filtry">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Výpis kurzů v mřížce karet -->
    @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($courses as $course)
                <div class="group bg-white rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)] hover:shadow-md hover:border-[#d6d0c7] transition-all flex flex-col justify-between overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <!-- Horní meta řádek -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="font-mono text-xs font-bold text-[#57534e] px-2 py-0.5 rounded-md bg-[#f2eee9] border border-[#e5dfd5]">
                                {{ $course->code }}
                            </span>
                            @if($course->is_accredited)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-[#ecf4ed] text-[#215a28] border border-[#cde0cf]">
                                    <svg class="w-3 h-3 text-[#215a28]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Akreditováno
                                </span>
                            @else
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#f2eee9] text-[#78716c] border border-[#ebe7df]">
                                    Odborný kurz
                                </span>
                            @endif
                        </div>

                        <!-- Název a anotace -->
                        <h2 class="text-base sm:text-lg font-bold text-[#1c1917] group-hover:text-[#b45309] transition-colors line-clamp-1 mb-2 font-heading">
                            <a href="{{ route('courses.show', $course) }}">
                                {{ $course->name }}
                            </a>
                        </h2>
                        <p class="text-xs text-[#78716c] line-clamp-3 leading-relaxed mb-4">
                            {{ $course->annotation }}
                        </p>

                        <!-- Klíčové parametry -->
                        <div class="grid grid-cols-2 gap-2 pt-4 border-t border-[#f2eee9] text-xs">
                            <div>
                                <span class="text-[#a8a29e] block font-medium">Hodinová dotace</span>
                                <span class="font-semibold text-[#1c1917]">{{ $course->duration_hours }} vyuč. hodin</span>
                            </div>
                            <div>
                                <span class="text-[#a8a29e] block font-medium">Základní cena</span>
                                <span class="font-bold text-[#1c1917]">{{ $course->formatted_price }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Patička karty s akcemi -->
                    <div class="px-5 sm:px-6 py-3 bg-[#faf8f5] border-t border-[#ebe7df] flex items-center justify-between text-xs font-semibold">
                        <a href="{{ route('courses.show', $course) }}" class="text-[#b45309] hover:text-[#92400e] flex items-center gap-1">
                            <span>Detail kurzu</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('courses.edit', $course) }}" class="text-[#78716c] hover:text-[#1c1917] p-1.5 rounded-lg hover:bg-[#e8dbcc]/60 transition-colors" title="Upravit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Opravdu chcete tento kurz odstranit?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[#a8a29e] hover:text-[#991b1b] p-1.5 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer" title="Smazat">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Stránkování -->
        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    @else
        <!-- Prázdný stav -->
        <div class="bg-white rounded-2xl border border-dashed border-[#ebe7df] p-8 sm:p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-[#f2eee9] text-[#78716c] mx-auto flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-[#1c1917] font-heading">Nebyly nalezeny žádné kurzy</h3>
            <p class="text-xs sm:text-sm text-[#78716c] mt-1 max-w-sm mx-auto">
                Pro zadaná vyhledávací kritéria neexistuje žádný záznam, nebo katalog zatím neobsahuje žádné kurzy.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                @if(!empty($search) || !empty($filter))
                    <a href="{{ route('courses.index') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] transition-colors border border-[#ebe7df]">
                        Zrušit filtry
                    </a>
                @endif
                <a href="{{ route('courses.create') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-[#1c1917] text-white hover:bg-[#2e2925] transition-colors">
                    Vytvořit první kurz
                </a>
            </div>
        </div>
    @endif

</x-layouts.app>
