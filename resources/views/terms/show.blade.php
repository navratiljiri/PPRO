<x-layouts.app :title="'Termín kurzu: ' . ($term->course->name ?? 'Termín')">

    <!-- Horní lišta s akcemi -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('terms.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#78716c] hover:text-[#1c1917] transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Zpět na přehled termínů</span>
        </a>

        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('terms.edit', $term) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-[#f2eee9] text-[#3d2b17] hover:bg-[#e8dbcc] border border-[#ebe7df] transition-colors">
                <svg class="w-3.5 h-3.5 text-[#78716c]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
                <span>Upravit termín</span>
            </a>
            <form action="{{ route('terms.destroy', $term) }}" method="POST" onsubmit="return confirm('Opravdu chcete tento termín odstranit?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-[#fdf2f2] text-[#991b1b] hover:bg-[#fee2e2] border border-[#fecaca] transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    <span>Smazat</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Karta detailu termínu -->
    <div class="bg-white rounded-3xl border border-[#ebe7df] shadow-xs overflow-hidden mb-8">
        <!-- Hlavička detailu -->
        <div class="p-5 sm:p-8 bg-[#1c1917] text-white">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-white/10 text-white border border-white/20">
                    {{ $term->course->code ?? 'KÓD' }}
                </span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ $term->status_badge_classes }}">
                    {{ $term->status_label }}
                </span>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-white/10 text-[#d6d0c7]">
                    Učebna: {{ $term->classroom }}
                </span>
            </div>

            <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white mb-2 font-heading">
                {{ $term->course->name ?? 'Kurz' }}
            </h1>
            <p class="text-xs sm:text-sm text-[#d6d0c7] mb-4">
                Období konání: <strong class="text-white">{{ $term->formatted_date_range }}</strong>
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-white/10 text-xs sm:text-sm">
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Kapacita učebny</span>
                    <span class="text-base sm:text-lg font-bold text-white">{{ $term->capacity }} osob</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Naplánováno lekcí</span>
                    <span class="text-base sm:text-lg font-bold text-[#f5c056]">{{ $term->lessons->count() }} lekcí</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Odcvičených hodin</span>
                    <span class="text-base sm:text-lg font-bold text-[#f5c056]">{{ $term->total_lesson_hours }} vyuč. hod</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Vyučující lektoři</span>
                    <span class="text-base sm:text-lg font-bold text-white">{{ $term->lecturers->count() }} osob</span>
                </div>
            </div>
        </div>

        @if($term->note)
            <div class="p-5 sm:p-6 border-b border-[#f2eee9] bg-[#faf8f5]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#b45309] mb-1 font-heading">Organizační poznámka recepce</h2>
                <p class="text-xs sm:text-sm text-[#44403c]">{{ $term->note }}</p>
            </div>
        @endif

        <!-- Harmonogram lekcí a střídání lektorů (M:N) -->
        <div class="p-5 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-[#f2eee9]">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-[#1c1917] font-heading">
                        Rozvrh výukových bloků a střídání lektorů
                    </h2>
                    <p class="text-xs text-[#78716c] mt-0.5">
                        Každý výukový blok má přiděleného konkrétního garanta lektora (M:N vazba).
                    </p>
                </div>
            </div>

            @if($term->lessons->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#faf8f5] text-[#78716c] font-semibold border-b border-[#ebe7df]">
                            <tr>
                                <th class="py-3 px-4">Téma výukového bloku</th>
                                <th class="py-3 px-4">Vyučující lektor</th>
                                <th class="py-3 px-4">Čas konání</th>
                                <th class="py-3 px-4">Délka</th>
                                <th class="py-3 px-4 text-right">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f2eee9]">
                            @foreach($term->lessons as $lesson)
                                <tr class="hover:bg-[#faf8f5]/60 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-[#1c1917]">
                                        {{ $lesson->topic }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <a href="{{ route('lecturers.show', $lesson->lecturer) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#f2eee9] text-[#3d2b17] font-semibold hover:bg-[#e8dbcc] transition-colors">
                                            <span>👤 {{ $lesson->lecturer->full_name }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-[#78716c] whitespace-nowrap">
                                        {{ $lesson->starts_at->format('d. m. Y H:i') }} – {{ $lesson->ends_at->format('H:i') }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-[#215a28]">
                                        {{ $lesson->duration_hours }} hod
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <form action="{{ route('lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Opravdu chcete odebrat tuto lekci?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#a8a29e] hover:text-[#991b1b] p-1 rounded hover:bg-rose-50 transition-colors cursor-pointer" title="Odebrat lekci">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center bg-[#faf8f5] rounded-2xl border border-dashed border-[#ebe7df] text-[#78716c] text-xs">
                    V tomto termínu zatím nejsou rozplánovány žádné konkrétní výukové bloky lektorů.
                </div>
            @endif

            <!-- Formulář pro přidání lekce -->
            <div class="mt-8 pt-6 border-t border-[#f2eee9]">
                <h3 class="text-sm font-bold text-[#1c1917] mb-4 font-heading">
                    + Přidat výukový blok do tohoto termínu
                </h3>
                <form action="{{ route('terms.lessons.store', $term) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    @csrf
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-bold text-[#1c1917] uppercase tracking-wider mb-1">Téma / Náplň lekce *</label>
                        <input type="text" name="topic" placeholder="např. Úvod do účetních standardů" required class="w-full px-3 py-2 text-xs rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5] text-[#1c1917]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#1c1917] uppercase tracking-wider mb-1">Lektor *</label>
                        <select name="lecturer_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5] text-[#1c1917]">
                            <option value="">-- Vyberte lektora --</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}">{{ $lecturer->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#1c1917] uppercase tracking-wider mb-1">Začátek *</label>
                        <input type="datetime-local" name="starts_at" required class="w-full px-3 py-2 text-xs rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5] text-[#1c1917]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#1c1917] uppercase tracking-wider mb-1">Konec *</label>
                        <input type="datetime-local" name="ends_at" required class="w-full px-3 py-2 text-xs rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5] text-[#1c1917]">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5 flex justify-end mt-2">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#1c1917] hover:bg-[#2e2925] text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer">
                            Přidat lekci do rozvrhu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.app>
