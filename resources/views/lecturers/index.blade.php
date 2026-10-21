<x-layouts.app title="Evidence lektorů">

    <!-- Horní sekce s nadpisem a statistikami -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Evidence lektorů</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Správa externích lektorů, jejich specializací a souhrnů odučených hodin Akademie Trutnov.
                </p>
            </div>
            <div>
                <a href="{{ route('lecturers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Přidat lektora</span>
                </a>
            </div>
        </div>

        <!-- Karty statistik lektorů -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Celkem lektorů</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_lecturers'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 mt-1">v externím týmu</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-indigo-100 bg-gradient-to-br from-white to-indigo-50/40 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Aktivní v termínech</div>
                <div class="text-2xl font-bold text-indigo-700 mt-2">{{ $stats['active_in_terms'] ?? 0 }}</div>
                <div class="text-xs text-indigo-600/80 mt-1">aktuálně učí</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Odučené hodiny</div>
                <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $stats['total_hours'] ?? 0 }} <span class="text-sm font-medium text-emerald-600/70">h</span></div>
                <div class="text-xs text-emerald-600/80 mt-1">ze všech lekcí</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Plánované lekce</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_lessons'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 mt-1">výukových bloků</div>
            </div>
        </div>
    </div>

    <!-- Vyhledávací panel -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6">
        <form method="GET" action="{{ route('lecturers.index') }}" class="flex flex-col md:flex-row gap-3 items-center justify-between">
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
                    placeholder="Hledat lektora podle jména, e-mailu či oboru..." 
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50"
                >
            </div>
            @if(!empty($search))
                <a href="{{ route('lecturers.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-700">
                    Zrušit filtr
                </a>
            @endif
        </form>
    </div>

    <!-- Mřížka karet lektorů -->
    @if($lecturers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($lecturers as $lecturer)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md hover:border-slate-300 transition-all flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-base shrink-0 shadow-sm shadow-indigo-100">
                                {{ mb_substr($lecturer->first_name, 0, 1) }}{{ mb_substr($lecturer->last_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-base font-bold text-slate-900 truncate">
                                    <a href="{{ route('lecturers.show', $lecturer) }}" class="hover:text-indigo-600 transition-colors">
                                        {{ $lecturer->full_name }}
                                    </a>
                                </h2>
                                <p class="text-xs text-indigo-600 font-semibold truncate mt-0.5">
                                    {{ $lecturer->specialization ?: 'Všeobecný lektor' }}
                                </p>
                            </div>
                        </div>

                        <!-- Kontakty -->
                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                <span class="truncate">{{ $lecturer->email }}</span>
                            </div>
                            @if($lecturer->phone)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                    </svg>
                                    <span>{{ $lecturer->phone }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Metriky lektora -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-slate-400">Odučené hodiny:</span>
                            <span class="font-bold text-slate-900 px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $lecturer->total_taught_hours }} vyuč. hod
                            </span>
                        </div>
                    </div>

                    <!-- Patička karty -->
                    <div class="px-6 py-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                        <a href="{{ route('lecturers.show', $lecturer) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <span>Profil lektora</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('lecturers.edit', $lecturer) }}" class="text-slate-500 hover:text-slate-800 p-1 rounded hover:bg-slate-200 transition-colors" title="Upravit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <form action="{{ route('lecturers.destroy', $lecturer) }}" method="POST" onsubmit="return confirm('Opravdu chcete smazat lektora {{ $lecturer->full_name }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50 transition-colors cursor-pointer" title="Smazat">
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

        <div class="mt-8">
            {{ $lecturers->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
            <h3 class="text-base font-bold text-slate-800">Nebyli nalezeni žádní lektoři</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                Pro zadaná kritéria neexistuje žádný záznam, nebo v systému zatím nejsou zaevidováni lektoři.
            </p>
            <div class="mt-6">
                <a href="{{ route('lecturers.create') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                    Přidat prvního lektora
                </a>
            </div>
        </div>
    @endif

</x-layouts.app>
