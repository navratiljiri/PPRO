<x-layouts.app :title="$lecturer->full_name">

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('lecturers.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Zpět do evidence lektorů</span>
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('lecturers.edit', $lecturer) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span>Upravit lektora</span>
            </a>
            <form action="{{ route('lecturers.destroy', $lecturer) }}" method="POST" onsubmit="return confirm('Opravdu chcete tohoto lektora odstranit?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Smazat</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Hero karta profilu lektora -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 to-indigo-950 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center text-white font-extrabold text-2xl shadow-lg shadow-indigo-900/50">
                    {{ mb_substr($lecturer->first_name, 0, 1) }}{{ mb_substr($lecturer->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-white/10 text-indigo-200">
                            {{ $lecturer->specialization ?: 'Externí lektor' }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white">
                        {{ $lecturer->full_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-slate-300">
                        <span>📧 {{ $lecturer->email }}</span>
                        @if($lecturer->phone)
                            <span>📞 {{ $lecturer->phone }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-6 mt-6 border-t border-white/10 text-sm">
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Celkem odučeno</span>
                    <span class="text-xl font-bold text-emerald-400">{{ $lecturer->total_taught_hours }} vyuč. hodin</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Výukových bloků</span>
                    <span class="text-xl font-bold text-white">{{ $lecturer->lessons->count() }} lekcí</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-wider text-slate-400">Běhů / Termínů</span>
                    <span class="text-xl font-bold text-white">{{ $lecturer->courseTerms->count() }} termínů</span>
                </div>
            </div>
        </div>

        @if($lecturer->bio)
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-2">Profil a odborná praxe</h2>
                <p class="text-sm text-slate-700 leading-relaxed">{{ $lecturer->bio }}</p>
            </div>
        @endif

        <!-- Seznam přiřazených lekcí a termínů -->
        <div class="p-6 sm:p-8">
            <h2 class="text-base font-bold text-slate-900 mb-4">Přiřazené výukové lekce a termíny kurzů</h2>

            @if($lecturer->lessons->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Kurz & Termín</th>
                                <th class="py-3 px-4">Téma výukového bloku</th>
                                <th class="py-3 px-4">Čas konání</th>
                                <th class="py-3 px-4">Délka</th>
                                <th class="py-3 px-4 text-right">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($lecturer->lessons as $lesson)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">
                                            {{ $lesson->courseTerm->course->name ?? 'Kurz' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            Učebna: {{ $lesson->courseTerm->classroom }} – {{ $lesson->courseTerm->formatted_date_range }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-800">
                                        {{ $lesson->topic }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                        {{ $lesson->starts_at->format('d. m. Y H:i') }} – {{ $lesson->ends_at->format('H:i') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                            {{ $lesson->duration_hours }} hod
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('terms.show', $lesson->course_term_id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                            Detail termínu &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-500 text-xs">
                    Lektor zatím nemá naplánované žádné výukové lekce.
                </div>
            @endif
        </div>
    </div>

</x-layouts.app>
