<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
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
                            <img id="profile-preview" class="mx-auto w-full aspect-square object-cover rounded-full cursor-pointer" src="{{ $user->funny_profile_photo_path ? asset('storage/' . $user->funny_profile_photo_path) : asset('assets/images/profile.jpg') }}" alt="Avatar de {{ $user->name }}" @click="showModal = true; modalImage = document.getElementById('profile-preview').src" />
                            <label for="profile-image-input" class="absolute bottom-0 right-0 bg-[#F8B229] text-white text-xs py-1 px-2 rounded-full cursor-pointer hover:bg-amber-400">Cambiar</label>
                            <input type="file" id="profile-image-input" name="funny_profile_photo_path" class="hidden" accept="image/*">
                        </div>
                        @error('funny_profile_photo_path')
                            <p class="text-red-500 text-xs text-center mt-2">{{ $message }}</p>
                        @enderror

                        <h1 class="mt-3 mb-1 text-center text-xl font-bold leading-8 text-gray-900">{{ $user->name }}</h1>
                        <h3 class="font-lg text-semibold text-center leading-6 text-gray-600">DNI: {{ $user->dni }}</h3>
                        <div class="mt-4">
                            <label for="nickname" class="block text-sm font-medium text-gray-700">Nickname</label>
                            <input type="text" id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#F8B229] focus:border-[#F8B229]">
                            @error('nickname')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <ul class="mt-3 divide-y rounded bg-gray-100 py-2 px-3 text-gray-600 shadow-sm hover:text-gray-700 hover:shadow">
                            @foreach($user->giftSuggestions as $suggestion)
                                <li class="flex items-center py-3 text-sm">{{ $suggestion->suggestion }}</li>
                            @endforeach
                        </ul>
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
                    {{-- Formulario para agregar familiares --}}
                    <form method="POST" action="{{ route('admin.users.assign-family', $user) }}" class="mb-6">
                        @csrf
                        <h3 class="text-lg font-semibold mb-2">Agregar Familiar</h3>
                        <select name="family_member_id" class="text-sm border border-gray-300 rounded px-2 py-1 mr-2" required>
                            <option value="">Seleccionar usuario</option>
                            @php
                                $allFamilyMembers = $user->getAllFamilyMembers();
                                $familyIds = $allFamilyMembers->pluck('id')->push($user->id);
                                if ($user->family_id) {
                                    $familyIds = $familyIds->merge($users->where('family_id', $user->family_id)->pluck('id'));
                                }
                            @endphp
                            @foreach($users->whereNotIn('id', $familyIds) as $potentialFamily)
                                <option value="{{ $potentialFamily->id }}">{{ $potentialFamily->name }} ({{ $potentialFamily->dni }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">+</button>
                    </form>

                    {{-- Lista de familiares --}}
                    @if($allFamilyMembers->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Grupo Familiar</h3>
                            <div class="space-y-1">
                                @foreach($allFamilyMembers as $familyMember)
                                    <div class="flex items-center justify-between bg-blue-50 p-2 rounded text-sm">
                                        <div class="flex items-center">
                                            <img class="w-6 h-6 rounded-full object-cover mr-2" src="{{ $familyMember->profile_photo_url }}" alt="Avatar de {{ $familyMember->name }}">
                                            <div>
                                                <div class="font-medium">{{ $familyMember->name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $familyMember->dni }}</div>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.users.remove-family', $user) }}" class="ml-1" onsubmit="return confirm('¿Estás seguro de que quieres remover este familiar del grupo?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="family_member_id" value="{{ $familyMember->id }}">
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-bold">×</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
    </script>
    @endsection
</x-app-layout>