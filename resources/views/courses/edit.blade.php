<x-layouts.app :title="'Upravit: ' . $course->name">

    <div class="max-w-3xl mx-auto">
        <!-- Zpětný odkaz -->
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Zpět na detail kurzu</span>
            </a>
            <span class="text-xs font-mono text-slate-400">ID: {{ $course->id }}</span>
        </div>

        <!-- Formulářová karta -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
            <div class="pb-6 border-b border-slate-100 mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Úprava kurzu</h1>
                    <p class="text-xs text-slate-500 mt-1">Aktualizace parametrů kurzu v databázi Akademie Trutnov.</p>
                </div>
            </div>

            <form action="{{ route('courses.update', $course) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Kód a stav v jednom řádku -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Kód kurzu <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="code" 
                            id="code" 
                            value="{{ old('code', $course->code) }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('code') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('code')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Stav kurzu <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="status" 
                            id="status" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                            <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Aktivní (v nabídce centra)</option>
                            <option value="archived" {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>Archivovaný (vyřazen z nabídky)</option>
                        </select>
                    </div>
                </div>

                <!-- Název kurzu -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Název kurzu <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name', $course->name) }}" 
                        class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Parametry: Rozsah hodin a Cena -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="duration_hours" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Hodinová dotace (vyuč. hod.) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="duration_hours" 
                            id="duration_hours" 
                            value="{{ old('duration_hours', $course->duration_hours) }}" 
                            min="1" 
                            max="1000" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('duration_hours') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('duration_hours')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Základní cena (Kč) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="price" 
                            id="price" 
                            value="{{ old('price', (int) $course->price) }}" 
                            min="0" 
                            step="100" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('price') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('price')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Akreditace -->
                <div>
                    <label class="relative flex items-center gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_accredited" 
                            value="1" 
                            {{ old('is_accredited', $course->is_accredited) ? 'checked' : '' }}
                            class="w-5 h-5 rounded-md text-indigo-600 border-slate-300 focus:ring-indigo-500"
                        >
                        <span class="text-sm font-semibold text-slate-800">Akreditovaný kurz (MŠMT / MPSV)</span>
                    </label>
                </div>

                <!-- Anotace kurzu -->
                <div>
                    <label for="annotation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Podrobná anotace a osnova kurzu <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="annotation" 
                        id="annotation" 
                        rows="6" 
                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('annotation') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed"
                        required
                    >{{ old('annotation', $course->annotation) }}</textarea>
                    @error('annotation')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tlačítka pro odeslání -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('courses.show', $course) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                        Zrušit
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-100 transition-all">
                        Uložit změny
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
