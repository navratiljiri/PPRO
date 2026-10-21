<x-layouts.app title="Termíny kurzů">

    <!-- Horní sekce s nadpisem a statistikami -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#ebe7df]">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1c1917] tracking-tight font-heading">Plánování a termíny kurzů</h1>
                <p class="mt-1 text-xs sm:text-sm text-[#78716c]">
                    Správa vypsaných běhů vzdělávacích programů, kapacit učeben a garancí lektorů Akademie Trutnov.
                </p>
            </div>
            <div class="shrink-0 self-start sm:self-auto">
                <a href="{{ route('terms.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl bg-[#1c1917] text-[#faf8f5] hover:bg-[#2e2925] border border-[#1c1917] shadow-xs transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Vypsat nový termín</span>
                </a>
            </div>
        </div>

        <!-- Karty statistik termínů -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6">
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78716c]">Vypsané termíny</div>
                <div class="text-2xl font-extrabold text-[#1c1917] mt-1 font-heading">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#a8a29e] mt-1">celkem v evidenci</div>
            </div>
            <div class="bg-[#f8faf8] p-4 sm:p-5 rounded-2xl border border-[#cde0cf] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#215a28]">Zápis otevřen</div>
                <div class="text-2xl font-extrabold text-[#215a28] mt-1 font-heading">{{ $stats['enrolling'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#2e7d32]/80 mt-1">přijímají se přihlášky</div>
            </div>
            <div class="bg-[#fdf9f4] p-4 sm:p-5 rounded-2xl border border-[#eddcc8] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#854d0e]">Právě probíhá</div>
                <div class="text-2xl font-extrabold text-[#854d0e] mt-1 font-heading">{{ $stats['in_progress'] ?? 0 }}</div>
                <div class="text-[11px] sm:text-xs text-[#854d0e]/80 mt-1">aktivní výuka</div>
            </div>
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)]">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-[#78716c]">Dostupná kapacita</div>
                <div class="text-2xl font-extrabold text-[#1c1917] mt-1 font-heading">{{ $stats['total_capacity'] ?? 0 }} <span class="text-xs sm:text-sm font-medium text-[#78716c]">míst</span></div>
                <div class="text-[11px] sm:text-xs text-[#a8a29e] mt-1">v aktivních termínech</div>
            </div>
        </div>
    </div>

    <!-- Filtrovací panel -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)] mb-6">
        <form method="GET" action="{{ route('terms.index') }}" class="flex flex-col md:flex-row gap-3 sm:gap-4 items-stretch md:items-center justify-between">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full md:w-auto">
                <label for="course_id" class="text-xs font-bold text-[#1c1917] uppercase tracking-wider">Kurz:</label>
                <select name="course_id" id="course_id" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-1.5 text-xs rounded-xl border border-[#ebe7df] focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5] text-[#1c1917] cursor-pointer">
                    <option value="">Všechny kurzy</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>
                            {{ $c->code }} – {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tlačítka stavu (responzivní s flex-wrap) -->
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 w-full md:w-auto">
                <span class="text-xs font-medium text-[#78716c] hidden sm:inline">Stav:</span>
                <a href="{{ route('terms.index', ['course_id' => $courseId]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ empty($status) ? 'bg-[#1c1917] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Všechny
                </a>
                <a href="{{ route('terms.index', ['status' => 'enrolling', 'course_id' => $courseId]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $status === 'enrolling' ? 'bg-[#215a28] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Zápis otevřen
                </a>
                <a href="{{ route('terms.index', ['status' => 'in_progress', 'course_id' => $courseId]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $status === 'in_progress' ? 'bg-[#854d0e] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Probíhá
                </a>
                <a href="{{ route('terms.index', ['status' => 'completed', 'course_id' => $courseId]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $status === 'completed' ? 'bg-[#57534e] text-white shadow-xs' : 'bg-[#f2eee9] text-[#57534e] hover:bg-[#e8dbcc] hover:text-[#1c1917]' }} transition-colors">
                    Dokončeno
                </a>
                @if(!empty($status) || !empty($courseId))
                    <a href="{{ route('terms.index') }}" class="p-1.5 text-[#a8a29e] hover:text-[#1c1917] text-xs font-medium ml-1">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Výpis termínů -->
    @if($terms->count() > 0)
        <div class="space-y-4">
            @foreach($terms as $term)
                <div class="bg-white rounded-2xl border border-[#ebe7df] shadow-[0_1px_3px_0_rgba(28,25,23,0.03)] hover:shadow-md hover:border-[#d6d0c7] transition-all p-4 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-md bg-[#f2eee9] text-[#57534e] border border-[#e5dfd5]">
                                {{ $term->course->code ?? 'KÓD' }}
                            </span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $term->status_badge_classes }}">
                                {{ $term->status_label }}
                            </span>
                            @if($term->course->is_accredited ?? false)
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#ecf4ed] text-[#215a28] border border-[#cde0cf]">
                                    Akreditováno
                                </span>
                            @endif
                        </div>

                        <h2 class="text-base sm:text-lg font-bold text-[#1c1917] hover:text-[#b45309] transition-colors font-heading">
                            <a href="{{ route('terms.show', $term) }}">
                                {{ $term->course->name ?? 'Kurz' }}
                            </a>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mt-3 text-xs text-[#78716c]">
                            <div>
                                <span class="text-[#a8a29e] block font-medium">Období konání</span>
                                <span class="font-bold text-[#1c1917]">{{ $term->formatted_date_range }}</span>
                            </div>
                            <div>
                                <span class="text-[#a8a29e] block font-medium">Místo a kapacita</span>
                                <span class="font-bold text-[#1c1917]">{{ $term->classroom }} (max {{ $term->capacity }} osob)</span>
                            </div>
                            <div>
                                <span class="text-[#a8a29e] block font-medium">Harmonogram výuky</span>
                                <span class="font-bold text-[#b45309]">{{ $term->lessons_count }} lekcí ({{ $term->total_lesson_hours }} hod)</span>
                            </div>
                        </div>

                        <!-- Přiřazení lektoři (M:N vazba) -->
                        <div class="mt-3 pt-3 border-t border-[#f2eee9] flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                            <span class="text-[#a8a29e] font-medium">Vyučující lektoři:</span>
                            @forelse($term->lecturers as $lecturer)
                                <a href="{{ route('lecturers.show', $lecturer) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#f2eee9] text-[#3d2b17] border border-[#e5dfd5] font-semibold hover:bg-[#e8dbcc] transition-colors">
                                    <span>👤 {{ $lecturer->full_name }}</span>
                                </a>
                            @empty
                                <span class="text-[#a8a29e] italic">Zatím nebyl přiřazen žádný lektor</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tlačítka akcí -->
                    <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end shrink-0 pt-3 lg:pt-0 border-t border-[#f2eee9] lg:border-t-0">
                        <a href="{{ route('terms.show', $term) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-[#1c1917] text-white hover:bg-[#2e2925] shadow-xs transition-colors">
                            <span>Detail a rozvrh</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('terms.edit', $term) }}" class="p-2 text-[#78716c] hover:text-[#1c1917] rounded-lg hover:bg-[#f2eee9] transition-colors" title="Upravit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <form action="{{ route('terms.destroy', $term) }}" method="POST" onsubmit="return confirm('Opravdu chcete tento termín odstranit?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-[#a8a29e] hover:text-[#991b1b] rounded-lg hover:bg-rose-50 transition-colors cursor-pointer" title="Smazat">
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
            {{ $terms->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-[#ebe7df] p-8 sm:p-12 text-center">
            <h3 class="text-base font-bold text-[#1c1917] font-heading">Nebyly nalezeny žádné termíny kurzů</h3>
            <p class="text-xs sm:text-sm text-[#78716c] mt-1 max-w-sm mx-auto">
                Pro vybraný filtr neexistuje žádný termín, nebo zatím nebyly vypsány žádné běhy kurzů.
            </p>
            <div class="mt-6">
                <a href="{{ route('terms.create') }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-[#1c1917] text-white hover:bg-[#2e2925] transition-colors">
                    Vypsat první termín
                </a>
            </div>
        </div>
    @endif

</x-layouts.app>
