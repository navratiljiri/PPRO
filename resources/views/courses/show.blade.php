<x-layouts.app :title="$course->name">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#78716c] hover:text-[#1c1917] transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Zpět do katalogu kurzů</span>
        </a>

    </div>

    <!-- Karta detailu kurzu -->
    <div class="bg-white rounded-3xl border border-[#ebe7df] shadow-xs overflow-hidden mb-8">
        <!-- Hlavička detailu -->
        <div class="p-5 sm:p-8 bg-[#1c1917] text-white">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-white/10 text-white border border-white/20">
                    {{ $course->code }}
                </span>
                @if($course->is_accredited)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-[#e59b10]/20 text-[#f5c056] border border-[#e59b10]/30">
                        <svg class="w-3.5 h-3.5 text-[#f5c056]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Akreditováno MŠMT / MPSV
                    </span>
                @else
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-white/10 text-[#d6d0c7]">
                        Odborný firemní kurz
                    </span>
                @endif
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $course->status === 'active' ? 'bg-[#215a28]/40 text-[#a3e635]' : 'bg-[#e59b10]/30 text-[#f5c056]' }}">
                    Stav: {{ $course->status === 'active' ? 'Aktivní' : 'Archivovaný' }}
                </span>
            </div>

            <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white mb-4 font-heading">
                {{ $course->name }}
            </h1>

            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-4 sm:gap-6 pt-4 border-t border-white/10 text-xs sm:text-sm text-[#d6d0c7]">
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Rozsah kurzu</span>
                    <span class="text-base sm:text-lg font-bold text-white">{{ $course->formatted_duration }}</span>
                </div>
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Základní cena</span>
                    <span class="text-base sm:text-lg font-bold text-[#f5c056]">{{ $course->formatted_price }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="block text-[11px] uppercase tracking-wider text-[#a8a29e]">Založeno v systému</span>
                    <span class="text-xs sm:text-sm font-medium text-[#d6d0c7]">{{ $course->created_at->format('d. m. Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Tělo: Anotace a obsah -->
        <div class="p-5 sm:p-8">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#b45309] mb-3 font-heading">
                Podrobná anotace a obsah programu
            </h2>
            <div class="text-[#44403c] leading-relaxed text-xs sm:text-sm whitespace-pre-line bg-[#faf8f5] p-4 sm:p-6 rounded-2xl border border-[#ebe7df]">
                {{ $course->annotation }}
            </div>
        </div>
    </div>

    <!-- Sekce: Termíny kurzu -->
    <div class="bg-white rounded-3xl border border-[#ebe7df] shadow-xs p-5 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#f2eee9]">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-[#1c1917] font-heading">Termíny tohoto kurzu</h3>
                <p class="text-xs text-[#78716c] mt-0.5">Plánované i probíhající běhy kurzu v učebnách Akademie Trutnov.</p>
            </div>
        </div>

        @if($course->terms && $course->terms->count() > 0)
            <div class="mt-6 space-y-3">
                @foreach($course->terms as $term)
                    <div class="p-4 rounded-2xl bg-[#faf8f5] border border-[#ebe7df] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $term->status_badge_classes }}">
                                    {{ $term->status_label }}
                                </span>
                                <span class="text-xs text-[#78716c] font-medium">Učebna: {{ $term->classroom }}</span>
                            </div>
                            <div class="text-sm font-bold text-[#1c1917]">
                                {{ $term->formatted_date_range }}
                            </div>
                            <div class="text-xs text-[#78716c] mt-0.5">
                                Kapacita: {{ $term->capacity }} osob · {{ $term->lessons->count() }} výukových lekcí
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('terms.show', $term) }}" class="px-3.5 py-1.5 rounded-xl bg-white border border-[#ebe7df] text-xs font-semibold text-[#1c1917] hover:bg-[#f2eee9] transition-colors">
                                Detail termínu &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-6 p-6 rounded-2xl bg-[#faf8f5] border border-dashed border-[#ebe7df] text-center">
                <p class="text-xs text-[#78716c]">
                    Tento kurz zatím nemá vypsané žádné termíny výuky.
                </p>
            </div>
        @endif
    </div>

</x-layouts.app>
