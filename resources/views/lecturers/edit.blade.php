<x-layouts.app :title="'Upravit lektora: ' . $lecturer->full_name">

    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('lecturers.show', $lecturer) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <span>Zpět na profil lektora</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10">
            <div class="pb-6 border-b border-slate-100 mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Úprava lektora</h1>
                    <p class="text-xs text-slate-500 mt-1">Aktualizace údajů a odborného zaměření.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">ID: {{ $lecturer->id }}</span>
            </div>

            <form action="{{ route('lecturers.update', $lecturer) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Jméno <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="first_name" 
                            id="first_name" 
                            value="{{ old('first_name', $lecturer->first_name) }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('first_name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('first_name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Příjmení <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="last_name" 
                            id="last_name" 
                            value="{{ old('last_name', $lecturer->last_name) }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('last_name') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('last_name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Kontaktní e-mail <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $lecturer->email) }}" 
                            class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/30' : 'border-slate-200' }} text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Telefon
                        </label>
                        <input 
                            type="text" 
                            name="phone" 
                            id="phone" 
                            value="{{ old('phone', $lecturer->phone) }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div>
                    <label for="specialization" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Odborná specializace / Garance
                    </label>
                    <input 
                        type="text" 
                        name="specialization" 
                        id="specialization" 
                        value="{{ old('specialization', $lecturer->specialization) }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="bio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Stručný profil / Životopis
                    </label>
                    <textarea 
                        name="bio" 
                        id="bio" 
                        rows="4" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('bio', $lecturer->bio) }}</textarea>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('lecturers.show', $lecturer) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                        Zrušit
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-md shadow-indigo-100 transition-all cursor-pointer">
                        Uložit změny
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
