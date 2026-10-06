<x-layouts.app :title="$course->name">

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Zpět do katalogu kurzů</span>
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('courses.edit', $course) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span>Upravit kurz</span>
            </a>
            <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Opravdu chcete tento kurz odstranit?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Smazat</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Karta detailu kurzu -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <!-- Hlavička detailu -->
        <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 to-indigo-950 text-white">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-white/10 text-white border border-white/20">
                    {{ $course->code }}
                </span>
                @if($course->is_accredited)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Akreditováno MŠMT / MPSV
                    </span>
                @else
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-white/10 text-slate-300">
                        Odborný firemní kurz
                    </span>
                @endif
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $course->status === 'active' ? 'bg-indigo-500/30 text-indigo-200' : 'bg-amber-500/30 text-amber-200' }}">
                    Stav: {{ $course->status === 'active' ? 'Aktivní' : 'Archivovaný' }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white mb-4">
                {{ $course->name }}
            </h1>

            <div class="flex flex-wrap items-center gap-6 pt-4 border-t border-white/10 text-sm text-slate-300">
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Rozsah kurzu</span>
                    <span class="text-lg font-bold text-white">{{ $course->duration_hours }} vyučovacích hodin</span>
                </div>
                <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Základní cena</span>
                    <span class="text-lg font-bold text-emerald-400">{{ $course->formatted_price }}</span>
                </div>
                <div class="w-px h-8 bg-white/10 hidden sm:block"></div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Založeno v systému</span>
                    <span class="text-sm font-medium text-slate-200">{{ $course->created_at->format('d. m. Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Tělo: Anotace a obsah -->
        <div class="p-6 sm:p-8">
            <h2 class="text-base font-bold text-slate-900 mb-3 uppercase tracking-wider text-xs text-indigo-600">
                Podrobná anotace a obsah programu
            </h2>
            <div class="text-slate-700 leading-relaxed text-sm sm:text-base whitespace-pre-line bg-slate-50 p-6 rounded-2xl border border-slate-100">
                {{ $course->annotation }}
            </div>
        </div>
    </div>

    <!-- Náhled sekce: Vypsané termíny (příprava na další fázi PPRO) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Termíny tohoto kurzu</h3>
                <p class="text-xs text-slate-500 mt-0.5">Plánované i probíhající běhy kurzu v učebnách Akademie Trutnov.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">
                Připraveno pro Fázi 2 (Entita Termin)
            </span>
        </div>

        <div class="mt-6 p-6 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center">
            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800">Propojení s termíny kurzu</h4>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Zde klientka uvidí konkrétní data konání, přiřazené lektory, obsazenost učebny a stav pořadníku náhradníků, jakmile propojíme relační entitu <code>Termin</code> (vazba 1:N).
            </p>
        </div>
    </div>

</x-layouts.app>
