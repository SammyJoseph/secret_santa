<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear o Asignar Usuario
        </h2>
    </x-slot>

    <div class="py-12" x-data="userCreateManager()" @user-selected.window="onUserSelected($event.detail)" @user-cleared.window="resetForm(false)">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Nuevo Participante</h2>
                        <p class="text-gray-600 text-sm mt-1">Crear un nuevo usuario y asignarlo a una familia.</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        ← Volver a usuarios
                    </a>
                </div>

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                        <p class="font-bold">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside text-sm mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Buscador / Selector para Jalar Usuario Existente --}}
                <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <label class="block text-sm font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                        Persona existente de la base de datos:
                    </label>
                    
                    <livewire:admin.user-search-select />
                </div>

                {{-- Formulario Principal --}}
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Familia Destino --}}
                    <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-200">
                        <label for="family_group_id" class="block text-sm font-bold text-blue-900 mb-1">
                            Asignar a la Familia <span class="text-red-500">*</span>
                        </label>
                        <select name="family_group_id" id="family_group_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Selecciona la familia --</option>
                            @foreach($familyGroups as $group)
                                <option value="{{ $group->id }}" {{ (old('family_group_id', request('family_group_id')) == $group->id) ? 'selected' : '' }}>
                                    {{ $group->name }} (Año {{ $group->year }}) 
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" x-model="formData.name" required
                                   placeholder="Ej: Juan Pérez"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="dni" class="block text-sm font-medium text-gray-700">
                                DNI <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="dni" id="dni" x-model="formData.dni" @input="onDniInput()" required
                                   maxlength="8" minlength="8" pattern="\d{8}" title="Debe contener exactamente 8 dígitos"
                                   placeholder="87654321"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                            <p class="mt-1 text-xs text-gray-500">Documento de 8 dígitos.</p>
                        </div>

                        <div>
                            <label for="nickname" class="block text-sm font-medium text-gray-700">
                                Apodo
                            </label>
                            <input type="text" name="nickname" id="nickname" x-model="formData.nickname"
                                   placeholder="Ej: Juancito"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Correo Electrónico
                            </label>
                            <input type="email" name="email" id="email" x-model="formData.email"
                                   placeholder="juan@ejemplo.com"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Contraseña <span class="text-red-500" x-show="!isExistingSelected">*</span>
                            </label>
                            <input type="password" name="password" id="password"
                                   :required="!isExistingSelected"
                                   minlength="4"
                                   :placeholder="isExistingSelected ? 'Dejar en blanco para conservar la actual' : 'Mínimo 4 caracteres'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    {{-- Foto de Perfil --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto de Perfil
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-300 bg-gray-100 flex-shrink-0">
                                <img :src="photoPreviewUrl || '{{ asset('assets/images/profile.jpg') }}'" alt="Vista previa" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <label class="cursor-pointer bg-white border border-gray-300 rounded-md py-1.5 px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm">
                                    <span>Imagen de perfil</span>
                                    <input type="file" name="profile_photo_path" @change="onPhotoChange($event)" class="hidden" accept="image/*">
                                </label>
                                <p class="text-xs text-gray-500 mt-2">JPG, PNG o WebP (máx. 10MB).</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('admin.users.index') }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm transition">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm shadow transition">
                            <span x-text="isExistingSelected ? 'Asignar a la Familia' : 'Crear y Asignar Usuario'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('js')
    <script>
        function userCreateManager() {
            const users = @json($existingUsers ?? []);

            return {
                existingUsers: users,
                selectedUserId: '',
                isExistingSelected: false,
                photoPreviewUrl: null,
                formData: {
                    name: '{{ old('name') }}',
                    dni: '{{ old('dni') }}',
                    nickname: '{{ old('nickname') }}',
                    email: '{{ old('email') }}',
                },

                onUserSelected(detail) {
                    const user = detail?.user || detail;
                    if (user) {
                        this.selectedUserId = user.id;
                        this.isExistingSelected = true;
                        this.formData.name = user.name || '';
                        this.formData.dni = user.dni || '';
                        this.formData.nickname = user.nickname || '';
                        this.formData.email = user.email || '';
                        this.photoPreviewUrl = user.profile_photo_url || null;
                    }
                },

                onDniInput() {
                    // Si el admin escribe 8 dígitos, buscar automáticamente si coincide con uno existente
                    if (this.formData.dni && this.formData.dni.length === 8) {
                        const found = this.existingUsers.find(u => u.dni === this.formData.dni);
                        if (found) {
                            this.onUserSelected(found);
                            if (window.Livewire) {
                                window.Livewire.dispatch('select-user-by-dni', { dni: this.formData.dni });
                            }
                        }
                    }
                },

                onPhotoChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.photoPreviewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                resetForm(notifyLivewire = true) {
                    this.selectedUserId = '';
                    this.isExistingSelected = false;
                    this.photoPreviewUrl = null;
                    this.formData = {
                        name: '',
                        dni: '',
                        nickname: '',
                        email: '',
                    };
                    if (notifyLivewire && window.Livewire) {
                        window.Livewire.dispatch('reset-user-search');
                    }
                }
            };
        }
    </script>
    @endsection
</x-app-layout>

