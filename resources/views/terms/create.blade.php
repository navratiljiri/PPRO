<x-layouts.app title="Vypsat nový termín">

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('terms.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#78716c] hover:text-[#1c1917] transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Zpět na přehled termínů</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-[#ebe7df] shadow-xs p-5 sm:p-10">
            <div class="pb-6 border-b border-[#f2eee9] mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-extrabold text-[#1c1917] tracking-tight font-heading">Vypsat nový termín kurzu</h1>
                <p class="text-xs text-[#78716c] mt-1">Zadejte časový plán, učebnu a kapacitu pro vzdělávací program.</p>
            </div>

            <form action="{{ route('terms.store') }}" method="POST" class="space-y-5 sm:space-y-6">
                @csrf

                <!-- Výběr kurzu -->
                <div>
                    <label for="course_id" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                        Vzdělávací kurz <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="course_id" 
                        id="course_id" 
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('course_id') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                        required
                    >
                        <option value="">-- Vyberte kurz z katalogu --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ (old('course_id', $selectedCourseId) == $course->id) ? 'selected' : '' }}>
                                [{{ $course->code }}] {{ $course->name }} ({{ $course->duration_hours }} hod)
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Čas od - do -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Zahájení termínu <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            name="start_date" 
                            id="start_date" 
                            value="{{ old('start_date') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('start_date') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('start_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Ukončení termínu <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            name="end_date" 
                            id="end_date" 
                            value="{{ old('end_date') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('end_date') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('end_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Učebna a Kapacita -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="classroom" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Místo konání / Učebna <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="classroom" 
                            id="classroom" 
                            value="{{ old('classroom', 'Učebna A (Centrum Trutnov)') }}" 
                            placeholder="např. Počítačová učebna B" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('classroom') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('classroom')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Kapacita účastníků <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="capacity" 
                            id="capacity" 
                            value="{{ old('capacity', 15) }}" 
                            min="1" 
                            max="500" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('capacity') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        <p class="text-[11px] text-[#78716c] mt-1">Striktní limit učebny – systém nedovolí jeho překročení.</p>
                        @error('capacity')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Výchozí stav -->
                <div>
                    <label for="status" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                        Počáteční stav termínu <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        id="status" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#ebe7df] text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                        required
                    >
                        <option value="enrolling" {{ old('status') === 'enrolling' ? 'selected' : '' }}>Zápis otevřen (přijímat přihlášky)</option>
                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>Probíhá</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Dokončeno</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Zrušeno</option>
                    </select>
                </div>

                <!-- Poznámka pro recepci -->
                <div>
                    <label for="note" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                        Organizační poznámka
                    </label>
                    <textarea 
                        name="note" 
                        id="note" 
                        rows="3" 
                        placeholder="Poznámky pro recepci a koordinátory (klíče, projektor, občerstvení...)" 
                        class="w-full px-4 py-3 rounded-xl border border-[#ebe7df] text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                    >{{ old('note') }}</textarea>
                </div>

                <!-- Odeslání (responzivní) -->
                <div class="pt-6 border-t border-[#f2eee9] flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <a href="{{ route('terms.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-[#78716c] hover:text-[#1c1917] hover:bg-[#f2eee9] transition-colors">
                        Zrušit
                    </a>
                    <button type="submit" class="w-full sm:w-auto justify-center px-6 py-2.5 rounded-xl bg-[#1c1917] hover:bg-[#2e2925] text-white text-xs sm:text-sm font-semibold shadow-xs transition-all cursor-pointer active:scale-[0.98]">
                        Uložit a vypsat termín
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
