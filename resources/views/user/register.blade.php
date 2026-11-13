<x-guest-layout>
    @php
        $familyGroup = $familyGroup ?? ($targetFamily ?? null);
        if (!$familyGroup) {
            $targetFamilyId = session('registration_family_group_id');
            if ($targetFamilyId) {
                $familyGroup = \App\Models\FamilyGroup::find($targetFamilyId);
            } elseif (request()->has('fam')) {
                $familyGroup = \App\Models\FamilyGroup::where('slug', request('fam'))->where('is_active', true)->first();
            }
        }
    @endphp
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-6 md:py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-lg w-full space-y-8 p-5 md:p-10 bg-white rounded-xl shadow-lg z-10">
            <div class="grid  gap-8 grid-cols-1">
                <div class="flex flex-col" x-data="{ showModal: false, modalImage: '' }">
                    <div class="flex flex-col sm:flex-row items-center">
                        <h2 class="font-semibold text-lg mr-auto">Regístrate para participar en "El Amigo Secreto {{ !empty($familyGroup?->name) ? ' de ' . $familyGroup->name : '' }}" de esta Navidad {{ date('Y') }}</h2>
                        <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                    </div>
                    <div>
                        <form action="{{ route('user.register') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form">
                                <div class="md:space-y-2">
                                    <div class="flex items-center py-6">
                                        <div class="w-12 h-12 mr-4 flex-none rounded-xl border overflow-hidden cursor-pointer" @click="showModal = true; modalImage = document.getElementById('profile-preview').src">
                                            <img id="profile-preview" class="w-12 h-12 mr-4 object-cover"
                                                src="{{ asset('assets/images/profile.jpg') }}"
                                                alt="Avatar Upload">
                                        </div>
                                        <label class="cursor-pointer">
                                            <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">
                                                Subir Foto
                                            </span>
                                            <input type="file" name="profile_photo_path" id="profile-image-input" class="hidden" accept="image/*">
                                        </label>
                                        <span class="text-xs text-gray-400 ml-2">Opcional</span>
                                        <input type="hidden" name="temp_image_filename" id="temp-image-filename" value="{{ session('temp_profile_image') }}">
                                        <span id="profile-photo-error" class="text-red-500 text-xs ml-2 animate-bounce hidden"></span>
                                    </div>
                                    @error('profile_photo_path')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs">
                                    <div class="space-y-2 w-full text-xs mb-3 md:mb-0">
                                        <label class="font-semibold text-gray-600 py-2">Nombre</label>
                                        <input placeholder="Nombre" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                            type="text" name="name" id="name" value="{{ old('name') }}">
                                        @error('name')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Teléfono <span class="text-red-500">*</span></label>
                                        <input placeholder="Ej: 987654321" required type="tel" name="phone" id="phone" value="{{ old('phone') }}" minlength="9" maxlength="9" pattern="\d{9}" title="Ingresar 9 dígitos de celular"
                                            class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"/>
                                        <input type="hidden" name="migrated_dni" id="migrated_dni" value="{{ old('migrated_dni') }}">

                                        {{-- Campo opcional para vincular DNI de año anterior --}}
                                        <div id="dni-legacy-container" class="hidden mt-2 p-3 bg-amber-50/90 border border-amber-200 rounded-lg space-y-1">
                                            <label class="block text-xs font-semibold text-amber-900">Ingresa tu DNI anterior para recuperar tus datos:</label>
                                            <div class="flex gap-2">
                                                <input type="text" id="legacy-dni-input" maxlength="8" placeholder="8 dígitos de tu DNI"
                                                    class="w-full bg-white text-gray-700 border border-amber-300 rounded-lg h-9 px-3 text-xs focus:ring-1 focus:ring-[#F8B229] focus:outline-none font-mono">
                                                <button type="button" id="btn-search-dni" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap">
                                                    Vincular
                                                </button>
                                            </div>
                                        </div>

                                        <div id="identifier-status" class="mt-1 text-xs"></div>
                                        @error('phone')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                        @error('migrated_dni')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs mt-6">
                                    <div class="space-y-2 w-full text-xs mb-3 md:mb-0">
                                        <label class="font-semibold text-gray-600 py-2">Crear Contraseña</label>
                                        <div class="relative">
                                            <input placeholder="Fácil de recordar" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 pr-10 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                                type="password" name="password" id="password" autocomplete="new-password">
                                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" id="toggle-password" tabindex="-1">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Confirmar Contraseña</label>
                                        <div class="relative">
                                            <input placeholder="Vuelve a escribirla" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 pr-10 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required" type="password"
                                                name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" id="toggle-password-confirmation" tabindex="-1">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                    <a href="{{ route('login') }}" class="my-2 md:mb-0 text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Iniciar Sesión</a>
                                    <button type="submit" id="submit-button" class="mb-2 md:mb-0 bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg hover:bg-green-800 inline-flex items-center justify-center">
                                        <svg id="submit-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span id="submit-text">Registrar mi participación</span>
                                    </button>
                                </div>
                            </div>
                        </form>
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
    </div>

    @section('css')
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
    </style>
    @endsection

    @section('js')
    <script>
        const profileInput = document.getElementById('profile-image-input');
        const profilePreview = document.getElementById('profile-preview');
        const tempImageFilename = document.getElementById('temp-image-filename');

        // Function to update preview
        function updatePreview(dataUrl) {
            profilePreview.src = dataUrl;
        }

        // Load preview from temp image on page load
        document.addEventListener('DOMContentLoaded', function() {
            const tempFilename = tempImageFilename.value;
            if (tempFilename) {
                profilePreview.src = '{{ url("/temp-image") }}/' + tempFilename;
            }
        });

        // Handle file selection with AJAX upload
        profileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Show immediate preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    updatePreview(e.target.result);
                };
                reader.readAsDataURL(file);

                // Upload to temp storage
                const formData = new FormData();
                formData.append('profile_photo_path', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route("user.temp-upload") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.filename) {
                        tempImageFilename.value = data.filename;
                        // Update preview with uploaded image
                        profilePreview.src = '{{ url("/temp-image") }}/' + data.filename;
                    }
                })
                .catch(error => {
                    console.error('Upload failed:', error);
                    // Reset to default if upload fails
                    profilePreview.src = '{{ asset('assets/images/profile.jpg') }}';
                });
            } else {
                // If no file selected, reset to default
                profilePreview.src = '{{ asset('assets/images/profile.jpg') }}';
                tempImageFilename.value = '';
            }
        });

        const form = document.querySelector('form');
        const photoError = document.getElementById('profile-photo-error');
        const submitButton = document.getElementById('submit-button');
        const submitSpinner = document.getElementById('submit-spinner');
        const submitText = document.getElementById('submit-text');
        
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitButton.disabled = true;
            submitSpinner.classList.remove('hidden');
            submitText.textContent = 'Registrando...';
            
            // Clear temp image on successful form submission (assuming no errors)
            if (!@json($errors->any())) {
                tempImageFilename.value = '';
            }
        });
        
        // Clear error when user selects a file
        profileInput.addEventListener('change', function() {
            if (this.files.length > 0 && photoError) {
                photoError.classList.add('hidden');
            }
        });

        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('mousedown', function(e) {
            e.preventDefault();
        });

        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');

            // Guarda la posición del cursor antes de cambiar el tipo
            const start = passwordInput.selectionStart;
            const end = passwordInput.selectionEnd;

            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Mantiene el foco en el input
            passwordInput.focus();

            // Restaura la posición del cursor
            passwordInput.setSelectionRange(start, end);

            const svg = this.querySelector('svg');
            if (type === 'password') {
                svg.outerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
            } else {
                svg.outerHTML = '<svg class="h-5 w-5" viewBox="0 -960 960 960" fill="currentColor"><path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/></svg>';
            }
        });

        document.getElementById('toggle-password-confirmation').addEventListener('mousedown', function(e) {
            e.preventDefault();
        });

        document.getElementById('toggle-password-confirmation').addEventListener('click', function() {
            const passwordConfirmationInput = document.getElementById('password_confirmation');

            // Guarda la posición del cursor antes de cambiar el tipo
            const start = passwordConfirmationInput.selectionStart;
            const end = passwordConfirmationInput.selectionEnd;

            const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmationInput.setAttribute('type', type);

            // Mantiene el foco en el input
            passwordConfirmationInput.focus();

            // Restaura la posición del cursor
            passwordConfirmationInput.setSelectionRange(start, end);

            const svg = this.querySelector('svg');
            if (type === 'password') {
                svg.outerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
            } else {
                svg.outerHTML = '<svg class="h-5 w-5" viewBox="0 -960 960 960" fill="currentColor"><path d="m644-428-58-58q9-47-27-88t-93-32l-58-58q17-8 34.5-12t37.5-4q75 0 127.5 52.5T660-500q0 20-4 37.5T644-428Zm128 126-58-56q38-29 67.5-63.5T832-500q-50-101-143.5-160.5T480-720q-29 0-57 4t-55 12l-62-62q41-17 84-25.5t90-8.5q151 0 269 83.5T920-500q-23 59-60.5 109.5T772-302Zm20 246L624-222q-35 11-70.5 16.5T480-200q-151 0-269-83.5T40-500q21-53 53-98.5t73-81.5L56-792l56-56 736 736-56 56ZM222-624q-29 26-53 57t-41 67q50 101 143.5 160.5T480-280q20 0 39-2.5t39-5.5l-36-38q-11 3-21 4.5t-21 1.5q-75 0-127.5-52.5T300-500q0-11 1.5-21t4.5-21l-84-82Zm319 93Zm-151 75Z"/></svg>';
            }
        });

        // Verificación de Celular y vinculación de DNI en tiempo real
        const phoneInput = document.getElementById('phone');
        const migratedDniInput = document.getElementById('migrated_dni');
        const nameInput = document.getElementById('name');
        const identifierStatus = document.getElementById('identifier-status');
        const registerForm = document.querySelector('form[action="{{ route('user.register') }}"]');
        const submitBtn = registerForm ? registerForm.querySelector('button[type="submit"]') : null;

        const dniLegacyContainer = document.getElementById('dni-legacy-container');
        const legacyDniInput = document.getElementById('legacy-dni-input');
        const btnSearchDni = document.getElementById('btn-search-dni');

        let lastCheckedVal = '';

        async function verifyIdentifier(val, isExplicitDni = false) {
            val = val.trim();
            if (!/^\d{8,9}$/.test(val)) {
                if (identifierStatus && !isExplicitDni) identifierStatus.innerHTML = '';
                return;
            }
            if (val === lastCheckedVal && !isExplicitDni) return;
            lastCheckedVal = val;

            try {
                const response = await fetch(`/check-identifier/${val}`);
                if (!response.ok) return;
                const data = await response.json();

                if (data.exists) {
                    if (data.already_registered_this_year) {
                        identifierStatus.innerHTML = `<span class="text-red-600 font-semibold">⚠️ Ya estás registrado este año en la familia "${data.registered_family_name}".</span>`;
                        if (submitBtn) submitBtn.disabled = true;
                        return;
                    }

                    // Cargar nombre y foto del usuario existente
                    if (data.name && nameInput) {
                        nameInput.value = data.name;
                    }
                    if (data.photo_url && profilePreview) {
                        profilePreview.src = data.photo_url;
                    }

                    if (data.matched_by === 'dni') {
                        // El usuario ingresó o fue encontrado por su DNI anterior
                        if (migratedDniInput) migratedDniInput.value = data.dni;
                        if (dniLegacyContainer) dniLegacyContainer.classList.add('hidden');

                        identifierStatus.innerHTML = `<div class="p-2 bg-green-50 border border-green-200 text-green-700 rounded mt-1 font-medium">✓ ¡Hola ${data.name}! Te reconocimos de la edición anterior con tu DNI (${data.dni}). Ahora ingresa tu número de celular (9 dígitos) para que sea tu nuevo acceso.</div>`;

                        // Si ingresó los 8 dígitos del DNI en el campo del teléfono, limpiamos el campo del teléfono para que ingrese su celular
                        if (phoneInput && phoneInput.value.trim() === data.dni) {
                            phoneInput.value = '';
                            phoneInput.placeholder = 'Ingresa tu celular (9 dígitos)';
                            phoneInput.focus();
                        }
                    } else {
                        // Encontrado por teléfono
                        identifierStatus.innerHTML = `<span class="text-green-600 font-medium">✓ ¡Hola ${data.name}! Reconocimos tu celular de años anteriores. Se cargaron tu nombre y foto. Escribe tu contraseña para participar.</span>`;
                    }

                    if (submitBtn) submitBtn.disabled = false;
                } else {
                    if (isExplicitDni) {
                        identifierStatus.innerHTML = `<span class="text-amber-700">No encontramos ningún participante previo con el DNI ${val}. Puedes registrarte como nuevo participante ingresando tus datos.</span>`;
                    } else if (val.length === 9) {
                        identifierStatus.innerHTML = `<span class="text-gray-500">✓ Número disponible para nuevo registro.</span>`;
                    } else {
                        identifierStatus.innerHTML = '';
                    }
                    if (submitBtn) submitBtn.disabled = false;
                }
            } catch (err) {
                console.error('Error al verificar identificador:', err);
            }
        }

        if (phoneInput) {
            phoneInput.addEventListener('input', () => {
                const val = phoneInput.value.trim();
                if (val.length === 8 || val.length === 9) {
                    verifyIdentifier(val);
                } else {
                    if (identifierStatus && !migratedDniInput?.value) {
                        identifierStatus.innerHTML = '';
                    }
                }
            });

            phoneInput.addEventListener('blur', () => {
                const val = phoneInput.value.trim();
                if (val.length === 8 || val.length === 9) {
                    verifyIdentifier(val);
                }
            });

            if (phoneInput.value && (phoneInput.value.length === 8 || phoneInput.value.length === 9)) {
                verifyIdentifier(phoneInput.value);
            }
        }

        if (btnSearchDni && legacyDniInput) {
            btnSearchDni.addEventListener('click', () => {
                const legacyVal = legacyDniInput.value.trim();
                if (legacyVal.length === 8) {
                    verifyIdentifier(legacyVal, true);
                } else {
                    alert('El DNI debe tener 8 dígitos.');
                }
            });

            legacyDniInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    btnSearchDni.click();
                }
            });
        }

    </script>
    @endsection
</x-guest-layout>