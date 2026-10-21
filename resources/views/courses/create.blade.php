<x-layouts.app title="Vytvořit nový kurz">

    <div class="max-w-3xl mx-auto">
        <!-- Zpětný odkaz -->
        <div class="mb-6">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-[#78716c] hover:text-[#1c1917] transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Zpět do katalogu kurzů</span>
            </a>
        </div>

        <!-- Formulářová karta -->
        <div class="bg-white rounded-3xl border border-[#ebe7df] shadow-xs p-5 sm:p-10">
            <div class="pb-6 border-b border-[#f2eee9] mb-6 sm:mb-8">
                <h1 class="text-xl sm:text-2xl font-extrabold text-[#1c1917] tracking-tight font-heading">Vytvořit nový vzdělávací kurz</h1>
                <p class="text-xs text-[#78716c] mt-1">Vyplňte základní údaje o programu Akademie Trutnov.</p>
            </div>

            <form action="{{ route('courses.store') }}" method="POST" class="space-y-5 sm:space-y-6">
                @csrf

                <!-- Kód a akreditace v jednom řádku -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="code" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Kód kurzu <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="code" 
                            id="code" 
                            value="{{ old('code') }}" 
                            placeholder="např. REK-UCET-01" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('code') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('code')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Typ akreditace
                        </label>
                        <div class="mt-2.5">
                            <label class="relative flex items-center gap-3 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="is_accredited" 
                                    value="1" 
                                    {{ old('is_accredited') ? 'checked' : '' }}
                                    class="w-5 h-5 rounded-md text-[#1c1917] border-[#ebe7df] focus:ring-[#e59b10]/30"
                                >
                                <span class="text-xs sm:text-sm font-semibold text-[#1c1917]">Akreditovaný kurz (MŠMT / MPSV)</span>
                            </label>
                            <p class="text-[11px] text-[#78716c] mt-1">U akreditovaných kurzů bude po splnění vystaveno oficiální osvědčení.</p>
                        </div>
                    </div>
                </div>

                <!-- Název kurzu -->
                <div>
                    <label for="name" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                        Název kurzu <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name') }}" 
                        placeholder="např. Účetnictví a daňová evidence pro praxi" 
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parametry: Rozsah hodin a Cena -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="duration_hours" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Hodinová dotace (vyuč. hod.) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="duration_hours" 
                            id="duration_hours" 
                            value="{{ old('duration_hours', 40) }}" 
                            min="1" 
                            max="1000" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('duration_hours') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('duration_hours')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                            Základní cena (Kč) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="price" 
                            id="price" 
                            value="{{ old('price', 12500) }}" 
                            min="0" 
                            step="100" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('price') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917]"
                            required
                        >
                        @error('price')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Anotace kurzu -->
                <div>
                    <label for="annotation" class="block text-xs font-bold text-[#1c1917] uppercase tracking-wider mb-2">
                        Podrobná anotace a osnova kurzu <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="annotation" 
                        id="annotation" 
                        rows="6" 
                        placeholder="Popište cíle kurzu, cílovou skupinu, hlavní témata a požadavky pro absolvování..." 
                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('annotation') ? 'border-rose-400 bg-rose-50/30' : 'border-[#ebe7df]' }} text-sm focus:outline-none focus:ring-2 focus:ring-[#e59b10]/25 focus:border-[#b45309] bg-[#faf8f5]/50 text-[#1c1917] leading-relaxed"
                        required
                    >{{ old('annotation') }}</textarea>
                    @error('annotation')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tlačítka pro odeslání (responzivní) -->
                <div class="pt-6 border-t border-[#f2eee9] flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <a href="{{ route('courses.index') }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-[#78716c] hover:text-[#1c1917] hover:bg-[#f2eee9] transition-colors">
                        Zrušit
                    </a>
                    <button type="submit" class="w-full sm:w-auto justify-center px-6 py-2.5 rounded-xl bg-[#1c1917] hover:bg-[#2e2925] text-white text-xs sm:text-sm font-semibold shadow-xs transition-all cursor-pointer active:scale-[0.98]">
                        Uložit a publikovat kurz
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
