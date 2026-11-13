<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative w-full">
    {{-- Caja Principal / Selector --}}
    @if($selectedUserId)
        {{-- Estado: Usuario Seleccionado --}}
        <div class="flex items-center justify-between w-full px-3 py-2 bg-white border border-amber-300 rounded-md shadow-sm">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="truncate">
                    <span class="text-sm font-semibold text-gray-900">{{ $selectedUserName }}</span>
                    <span class="text-xs text-amber-800 bg-amber-100/70 border border-amber-200 px-1.5 py-0.5 rounded font-mono font-medium ml-1.5">
                        {{ $selectedUserDni }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                <button type="button" 
                        wire:click="clearSelection" 
                        class="p-1 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition"
                        title="Quitar selección">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <button type="button" 
                        @click="open = !open; if(open) { $nextTick(() => $refs.dropdownSearch?.focus()) }" 
                        class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-900 font-semibold px-2.5 py-1 rounded flex items-center gap-1 transition">
                    <span>Cambiar</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>
    @else
        {{-- Estado: Sin Seleccionar (Buscador Livewire directo tipo Select) --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
                <svg wire:loading.remove wire:target="search" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg wire:loading wire:target="search" class="w-4 h-4 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <input 
                type="text" 
                wire:model.live.debounce.250ms="search" 
                @focus="open = true" 
                @click="open = true" 
                placeholder="Buscar por nombre, celular o DNI"
                class="block w-full pl-9 pr-10 py-2 rounded-md border-amber-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm bg-white cursor-pointer"
            >

            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="text-gray-400 hover:text-gray-600 p-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
                <button type="button" @click="open = !open" class="text-gray-400 hover:text-amber-800 p-1">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Lista Desplegable --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 left-0 right-0 mt-1 bg-white rounded-lg shadow-xl border border-amber-200 overflow-hidden"
        style="display: none;"
    >
        {{-- Si ya hay un usuario seleccionado y abrió el menú para cambiarlo, mostramos buscador en la parte superior --}}
        @if($selectedUserId)
            <div class="p-2 border-b border-gray-100 bg-amber-50/60">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-amber-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-ref="dropdownSearch"
                        wire:model.live.debounce.250ms="search" 
                        placeholder="Escribe para buscar por nombre, celular o DNI..." 
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded border-gray-300 focus:border-amber-500 focus:ring-amber-500 bg-white"
                    >
                </div>
            </div>
        @endif

        {{-- Opciones de Usuarios --}}
        <div class="max-h-60 overflow-y-auto divide-y divide-gray-100">
            @forelse($users as $user)
                <div 
                    wire:key="user-item-{{ $user->id }}"
                    wire:click="selectUser({{ $user->id }})"
                    @click="open = false"
                    class="p-2.5 hover:bg-amber-50/80 cursor-pointer transition flex items-center justify-between gap-3 {{ $selectedUserId === $user->id ? 'bg-amber-50' : '' }}"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-gray-200">
                        @else
                            <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-900 font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 text-sm truncate">{{ $user->name }}</span>
                                @if($user->nickname)
                                    <span class="text-xs text-gray-400 truncate">({{ $user->nickname }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                <span class="font-mono bg-gray-100 text-gray-700 px-1.5 py-0.2 rounded font-semibold">
                                    {{ $user->phone ? 'Cel: ' . $user->phone : 'DNI: ' . $user->dni }}
                                </span>
                                @if($user->familyGroup)
                                    <span class="text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded border border-blue-100 truncate text-[11px]">
                                        {{ $user->familyGroup->name }} ({{ $user->familyGroup->year }})
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($selectedUserId === $user->id)
                        <span class="text-amber-600 font-bold text-sm shrink-0">✓</span>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-sm text-gray-500">
                    @if(filled(trim($search)))
                        <p class="font-medium text-gray-700">No se encontraron resultados para "{{ $search }}"</p>
                        <p class="text-xs text-gray-400 mt-1">Puedes registrar al participante completando el formulario.</p>
                    @else
                        <p class="text-gray-500">No hay participantes registrados disponibles.</p>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pie de menú informativo --}}
        <div class="bg-gray-50 px-3 py-1.5 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
            <span>{{ $users->count() }} participante(s) disponible(s)</span>
            <span class="italic text-gray-400">Escribe nombre, celular o DNI para filtrar</span>
        </div>
    </div>
</div>
