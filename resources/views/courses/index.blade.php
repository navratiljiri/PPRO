<x-layouts.app title="Katalog kurzů">

    <!-- Horní sekce s nadpisem a statistikami -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Katalog a správa kurzů</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Správa vzdělávacích programů, rekvalifikačních a firemních kurzů Akademie Trutnov.
                </p>
            </div>
            <div>
                <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Vytvořit nový kurz</span>
                </a>
            </div>
        </div>

        <!-- Karty statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aktivní kurzy</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 mt-1">v nabídce centra</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Akreditované</div>
                <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $stats['accredited'] ?? 0 }}</div>
                <div class="text-xs text-emerald-600/80 mt-1">s osvědčením MŠMT</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50/40 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-blue-600">Firemní & Ostatní</div>
                <div class="text-2xl font-bold text-blue-700 mt-2">{{ $stats['non_accredited'] ?? 0 }}</div>
                <div class="text-xs text-blue-600/80 mt-1">specializovaná školení</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Průměrná dotace</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['average_hours'] ?? 0 }} <span class="text-sm font-medium text-slate-500">hod</span></div>
                <div class="text-xs text-slate-500 mt-1">na jeden kurz</div>
            </div>
        </div>
    </div>

    <!-- Filtrovací a vyhledávací panel -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6">
        <form method="GET" action="{{ route('courses.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
            <!-- Textové vyhledávání -->
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search ?? '' }}" 
                    placeholder="Hledat podle názvu, kódu či anotace..." 
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50"
                >
            </div>

            <!-- Tlačítka rychlého filtru -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <span class="text-xs font-medium text-slate-500 hidden sm:inline">Filtr:</span>
                <a href="{{ route('courses.index', ['search' => $search]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ empty($filter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition-colors">
                    Všechny kurzy
                </a>
                <a href="{{ route('courses.index', ['filter' => 'accredited', 'search' => $search]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'accredited' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition-colors">
                    Pouze akreditované
                </a>
                <a href="{{ route('courses.index', ['filter' => 'non_accredited', 'search' => $search]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $filter === 'non_accredited' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }} transition-colors">
                    Ostatní / Firemní
                </a>

                @if(!empty($search) || !empty($filter))
                    <a href="{{ route('courses.index') }}" class="p-1.5 text-slate-400 hover:text-slate-600 text-xs font-medium ml-1" title="Zrušit filtry">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Výpis kurzů v mřížce karet -->
    @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="group bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md hover:border-slate-300 transition-all flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <!-- Horní meta řádek -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="font-mono text-xs font-bold text-slate-500 px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">
                                {{ $course->code }}
                            </span>
                            @if($course->is_accredited)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Akreditováno
                                </span>
                            @else
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    Odborný kurz
                                </span>
                            @endif
                        </div>

                        <!-- Název a anotace -->
                        <h2 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1 mb-2">
                            <a href="{{ route('courses.show', $course) }}">
                                {{ $course->name }}
                            </a>
                        </h2>
                        <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed mb-4">
                            {{ $course->annotation }}
                        </p>

                        <!-- Klíčové parametry -->
                        <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block font-medium">Hodinová dotace</span>
                                <span class="font-semibold text-slate-800">{{ $course->duration_hours }} vyuč. hodin</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Základní cena</span>
                                <span class="font-bold text-slate-900">{{ $course->formatted_price }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Patička karty s akcemi -->
                    <div class="px-6 py-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <a href="{{ route('courses.show', $course) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <span>Detail kurzu</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('courses.edit', $course) }}" class="text-slate-500 hover:text-slate-800 p-1 rounded hover:bg-slate-200 transition-colors" title="Upravit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Opravdu chcete tento kurz odstranit?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition-colors" title="Smazat">
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
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Nebyly nalezeny žádné kurzy</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                Pro zadaná vyhledávací kritéria neexistuje žádný záznam, nebo katalog zatím neobsahuje žádné kurzy.
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                @if(!empty($search) || !empty($filter))
                    <a href="{{ route('courses.index') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                        Zrušit filtry
                    </a>
                @endif
                <a href="{{ route('courses.create') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                    Vytvořit první kurz
                </a>
            </div>
        </div>
    @endif

</x-layouts.app>
