<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuario</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ showModal: false, modalImage: '' }">
                <!-- Columna Izquierda: Perfil -->
                <div class="rounded-lg border bg-white px-4 py-8 shadow-lg">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="relative mx-auto w-36 rounded-full">
                            <img id="profile-preview" class="mx-auto w-full aspect-square object-cover rounded-full cursor-pointer" src="{{ $user->funny_profile_photo_path ? Storage::url($user->funny_profile_photo_path) : ($user->profile_photo_path ? Storage::url($user->profile_photo_path) : asset('assets/images/profile.jpg')) }}" alt="Avatar de {{ $user->name }}" @click="showModal = true; modalImage = document.getElementById('profile-preview').src" />
                            <label for="profile-image-input" class="absolute bottom-0 right-0 bg-[#F8B229] text-white text-xs py-1 px-2 rounded-full cursor-pointer hover:bg-amber-400">Cambiar</label>
                            <input type="file" id="profile-image-input" name="funny_profile_photo_path" class="hidden" accept="image/*">
                        </div>
                        @error('funny_profile_photo_path')
                            <p class="text-red-500 text-xs text-center mt-2">{{ $message }}</p>
                        @enderror

                        <h1 class="mt-3 mb-1 text-center text-xl font-bold leading-8 text-gray-900">{{ $user->name }}</h1>
                        <h3 class="font-lg text-semibold text-center leading-6 text-gray-600">{{ $user->phone ? 'Teléfono: ' . $user->phone : ($user->dni ? 'DNI: ' . $user->dni : '') }}</h3>
                        <div class="mt-4">
                            <label for="nickname" class="block text-sm font-medium text-gray-700">Nickname</label>
                            <input type="text" id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#F8B229] focus:border-[#F8B229]">
                            @error('nickname')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mt-4">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">
                                Sugerencias actuales {{ $user->familyGroup ? '(' . $user->familyGroup->name . ' - ' . $user->familyGroup->year . ')' : '' }}
                            </h4>
                            <div class="space-y-3">
                                @forelse($user->giftSuggestions as $suggestion)
                                    <div class="bg-gray-50 rounded-lg p-4 flex items-center space-x-3">
                                        <div class="w-12 h-12 flex-none rounded-lg overflow-hidden cursor-pointer" @click="showModal = true; modalImage = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}'">
                                            <img class="w-full h-full object-cover rounded-lg" src="{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}" alt="Imagen de referencia">
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-700">{{ $suggestion->suggestion }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic">No ha registrado sugerencias para esta familia aún.</p>
                                @endforelse
                            </div>
                        </div>

                        @php
                            $pastSuggestions = $user->allGiftSuggestions ? $user->allGiftSuggestions->filter(fn($s) => $s->family_group_id != $user->family_group_id) : collect();
                        @endphp
                        @if($pastSuggestions->isNotEmpty())
                            <div class="mt-6 border-t pt-4">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 -960 960 960"><path d="M160-160v-320h160v320H160Zm240 0v-480h160v480H400Zm240 0v-640h160v640H640Z"/></svg>
                                    Recuerdos / Sugerencias de ediciones pasadas
                                </h4>
                                <div class="space-y-3">
                                    @foreach($pastSuggestions->groupBy('family_group_id') as $familyGroupId => $suggestions)
                                        @php
                                            $fg = $suggestions->first()->familyGroup;
                                        @endphp
                                        <div class="border rounded-lg p-3 bg-amber-50/50">
                                            <p class="text-xs font-bold text-gray-700 mb-2">🎄 {{ $fg ? $fg->name . ' (' . $fg->year . ')' : 'Edición anterior' }}</p>
                                            <div class="space-y-2">
                                                @foreach($suggestions as $suggestion)
                                                    <div class="bg-white rounded p-2 flex items-center space-x-2 border border-gray-100">
                                                        <div class="w-10 h-10 flex-none rounded overflow-hidden cursor-pointer" @click="showModal = true; modalImage = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}'">
                                                            <img class="w-full h-full object-cover rounded" src="{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}" alt="Imagen">
                                                        </div>
                                                        <div class="flex-1 text-xs text-gray-600">
                                                            {{ $suggestion->suggestion }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="text-center mt-6">
                            <button type="submit" class="bg-[#146B3A] text-white px-4 py-2 rounded-full hover:bg-green-800">Guardar Cambios</button>
                            <div class="mt-3">
                                <button type="button" id="generate-reset-link" class="flex space-x-1 mx-auto bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600" data-user-id="{{ $user->id }}">
                                    Generar Enlace 
                                    <svg class="w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M420-680q0-33 23.5-56.5T500-760q33 0 56.5 23.5T580-680q0 33-23.5 56.5T500-600q-33 0-56.5-23.5T420-680ZM500 0 320-180l60-80-60-80 60-85v-47q-54-32-87-86.5T260-680q0-100 70-170t170-70q100 0 170 70t70 170q0 67-33 121.5T620-472v352L500 0ZM340-680q0 56 34 98.5t86 56.5v125l-41 58 61 82-55 71 75 75 40-40v-371q52-14 86-56.5t34-98.5q0-66-47-113t-113-47q-66 0-113 47t-47 113Z"/></svg>
                                </button>
                            </div>
                            <div id="reset-link-container" class="hidden mt-4 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-gray-700 mb-2">Enlace generado (válido por 30 minutos):</p>
                                <input type="text" id="reset-link" readonly class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white">
                                <button type="button" id="copy-link" class="mt-2 bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">Copiar Enlace</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Columna Derecha: Gestión de Familiares -->
                <div class="rounded-lg border bg-white px-4 py-8 shadow-lg">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Grupo Familiar Interno</h3>
                    <p class="text-xs text-gray-500 mb-5">
                        Excluye a los miembros seleccionados para que no puedan regalarse entre sí en el sorteo de su familia general.
                    </p>

                    {{-- Formulario para agregar familiares --}}
                    <form method="POST" action="{{ route('admin.users.assign-family', $user) }}" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Agregar Familiar al Grupo</h4>

                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="select-year" class="block text-xs font-semibold text-gray-600 mb-1">1. Año</label>
                                    <select id="select-year" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-[#F8B229] focus:ring-[#F8B229]">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ (int)$defaultYear === (int)$year ? 'selected' : '' }}>
                                                Año {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="select-family" class="block text-xs font-semibold text-gray-600 mb-1">2. Familia General</label>
                                    <select id="select-family" name="family_group_id" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-[#F8B229] focus:ring-[#F8B229]">
                                        <!-- Se puebla dinámicamente con JS -->
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="select-user" class="block text-xs font-semibold text-gray-600 mb-1">3. Participante a vincular</label>
                                <div class="flex space-x-2">
                                    <select id="select-user" name="family_member_id" class="flex-1 text-sm border-gray-300 rounded-md shadow-sm focus:border-[#F8B229] focus:ring-[#F8B229]" required>
                                        <option value="">-- Seleccionar participante --</option>
                                        <!-- Se puebla dinámicamente con JS -->
                                    </select>
                                    <button type="submit" id="btn-add-family" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-semibold text-sm transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                                        + Agregar
                                    </button>
                                </div>
                                <p id="no-candidates-msg" class="text-xs text-gray-400 italic mt-1.5 hidden">
                                    No hay otros participantes disponibles para agregar en esta familia.
                                </p>
                            </div>
                        </div>
                    </form>

                    {{-- Lista de familiares actuales --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Miembros actuales del grupo familiar</h4>
                        @if($allFamilyMembers->count() > 0)
                            <div class="space-y-2">
                                @foreach($allFamilyMembers as $familyMember)
                                    <div class="flex items-center justify-between bg-blue-50/70 border border-blue-100 p-2.5 rounded-lg text-sm">
                                        <div class="flex items-center space-x-3">
                                            <img class="w-8 h-8 rounded-full object-cover" src="{{ $familyMember->profile_photo_url }}" alt="Avatar de {{ $familyMember->name }}">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $familyMember->name }}</div>
                                                <div class="text-gray-500 text-xs font-mono">{{ $familyMember->phone ?? $familyMember->dni }}</div>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.users.remove-family', $user) }}" class="ml-2" onsubmit="return confirm('¿Estás seguro de que quieres remover a {{ $familyMember->name }} de este grupo familiar?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="family_member_id" value="{{ $familyMember->id }}">
                                            <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-md text-sm font-bold transition-colors" title="Remover familiar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic bg-gray-50 p-3 rounded-lg border border-dashed border-gray-200">
                                Este usuario no tiene familiares asignados en un grupo interno aún.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Image Modal -->
                <div x-show="showModal" @keydown.escape.window="showModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" style="display: none;">
                    <div @click.away="showModal = false" class="relative px-4 rounded-lg shadow-lg max-w-lg w-full">
                        <img :src="modalImage" alt="Profile image large" class="w-full h-auto rounded-md">
                        <button @click="showModal = false" class="absolute top-0 right-0 mt-2 mr-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('js')
    <script>
        const profileInput = document.getElementById('profile-image-input');
        const profilePreview = document.getElementById('profile-preview');

        profileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Reset link generation
        const generateBtn = document.getElementById('generate-reset-link');
        const resetContainer = document.getElementById('reset-link-container');
        const resetLinkInput = document.getElementById('reset-link');
        const copyBtn = document.getElementById('copy-link');

        generateBtn.addEventListener('click', async function() {
            const userId = this.getAttribute('data-user-id');
            try {
                const response = await fetch(`/admin/users/${userId}/generate-reset-link`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    resetLinkInput.value = data.reset_url;
                    resetContainer.classList.remove('hidden');
                } else {
                    alert('Error al generar el enlace');
                }
            } catch (error) {
                alert('Error de conexión');
            }
        });

        copyBtn.addEventListener('click', function() {
            resetLinkInput.select();
            document.execCommand('copy');
            this.textContent = 'Copiado!';
            setTimeout(() => {
                this.textContent = 'Copiar Enlace';
            }, 2000);
        });

        // Filtrado dinámico de familias y participantes para grupo familiar interno
        const familyGroupsData = @json($familyGroupsData);
        const existingFamilyIds = @json($existingFamilyIds);
        const currentUserId = {{ $user->id }};
        const defaultFamilyId = {{ $defaultFamilyId ? (int)$defaultFamilyId : 'null' }};

        const yearSelect = document.getElementById('select-year');
        const familySelect = document.getElementById('select-family');
        const userSelect = document.getElementById('select-user');
        const btnAddFamily = document.getElementById('btn-add-family');
        const noCandidatesMsg = document.getElementById('no-candidates-msg');

        function updateFamilies() {
            if (!yearSelect || !familySelect) return;
            const selectedYear = parseInt(yearSelect.value);
            const families = familyGroupsData.filter(fg => parseInt(fg.year) === selectedYear);
            
            familySelect.innerHTML = '';
            families.forEach(fg => {
                const opt = document.createElement('option');
                opt.value = fg.id;
                opt.textContent = fg.name;
                familySelect.appendChild(opt);
            });

            if (defaultFamilyId && families.some(fg => fg.id === defaultFamilyId)) {
                familySelect.value = defaultFamilyId;
            }

            updateUsers();
        }

        function updateUsers() {
            if (!familySelect || !userSelect) return;
            const selectedFamilyId = parseInt(familySelect.value);
            const family = familyGroupsData.find(fg => fg.id === selectedFamilyId);
            
            userSelect.innerHTML = '<option value="">-- Seleccionar participante --</option>';
            
            if (!family || !family.users || family.users.length === 0) {
                userSelect.disabled = true;
                if (btnAddFamily) btnAddFamily.disabled = true;
                if (noCandidatesMsg) noCandidatesMsg.classList.remove('hidden');
                return;
            }

            const candidates = family.users.filter(u => u.id !== currentUserId && !existingFamilyIds.includes(u.id));

            if (candidates.length === 0) {
                userSelect.disabled = true;
                if (btnAddFamily) btnAddFamily.disabled = true;
                if (noCandidatesMsg) noCandidatesMsg.classList.remove('hidden');
            } else {
                userSelect.disabled = false;
                if (noCandidatesMsg) noCandidatesMsg.classList.add('hidden');
                candidates.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    const doc = u.phone || u.dni || 'Sin Tel/DNI';
                    opt.textContent = `${u.name} (${doc})`;
                    userSelect.appendChild(opt);
                });
                if (btnAddFamily) btnAddFamily.disabled = !userSelect.value;
            }
        }

        if (yearSelect && familySelect && userSelect) {
            yearSelect.addEventListener('change', updateFamilies);
            familySelect.addEventListener('change', updateUsers);
            userSelect.addEventListener('change', () => {
                if (btnAddFamily) btnAddFamily.disabled = !userSelect.value;
            });

            updateFamilies();
        }
    </script>
    @endsection
</x-app-layout>