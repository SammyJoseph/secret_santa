<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-6 md:py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-lg w-full space-y-8 p-5 md:p-10 bg-white rounded-xl shadow-lg z-10">
            <div class="grid  gap-8 grid-cols-1">
                <div class="flex flex-col ">
                    <div class="flex flex-col sm:flex-row items-center">
                        <h2 class="font-semibold text-lg mr-auto">Regístrate para participar en "El Amigo Secreto Familiar" de esta Navidad {{ date('Y') }}</h2>
                        <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                    </div>
                    <div>
                        <form action="{{ route('user.register') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form">
                                <div class="md:space-y-2">
                                    <div class="flex items-center py-6">
                                        <div class="w-12 h-12 mr-4 flex-none rounded-xl border overflow-hidden">
                                            <img id="profile-preview" class="w-12 h-12 mr-4 object-cover"
                                                src="{{ asset('assets/images/profile.jpg') }}"
                                                alt="Avatar Upload">
                                        </div>
                                        <label class="cursor-pointer ">
                                            <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">
                                                Subir Foto
                                            </span>
                                            <input type="file" name="profile_photo_path" id="profile-image-input" class="hidden" accept="image/*">
                                        </label>
                                        <input type="hidden" name="temp_image_filename" id="temp-image-filename" value="{{ session('temp_profile_image') }}">
                                        <span class="text-gray-400 text-xs ml-2">Opcional</span>
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
                                        <div class="flex items-center space-x-2">
                                            <label class="font-semibold text-gray-600">DNI</label>
                                            <div class="tooltip">
                                              <svg class="w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="#4b5563"><path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>
                                              <span class="tooltiptext">Para evitar registros duplicados.</span>
                                            </div>
                                        </div>
                                        <input placeholder="87654321" required type="text" name="dni" id="dni" value="{{ old('dni') }}" minlength="8" maxlength="8" pattern="\d{8}" title="Ingresar 8 dígitos"
                                            class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"/>
                                        @error('dni')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <div class="space-y-2 w-full text-xs mb-3">
                                        <label class=" font-semibold text-gray-600 py-2">Mi sugerencia de regalo 1. <span class="text-gray-400">Sé lo más específico posible.</span></label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[#BB2528] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[#BB2528] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <textarea name="gift_suggestions[0]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                                placeholder="Me gustaría recibir..." autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.0') }}</textarea>
                                        </div>
                                        @error('gift_suggestions.0')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs mb-3">
                                        <label class=" font-semibold text-gray-600 py-2">Mi sugerencia de regalo 2</label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[#BB2528] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[#BB2528] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <textarea name="gift_suggestions[1]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                                placeholder="O también me gustaría recibir..." autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.1') }}</textarea>
                                        </div>
                                        @error('gift_suggestions.1')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs mb-3">
                                        <label class=" font-semibold text-gray-600 py-2">Mi sugerencia de regalo 3</label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[#BB2528] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[#BB2528] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <textarea name="gift_suggestions[2]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                                placeholder="O también me gustaría recibir..." autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.2') }}</textarea>
                                        </div>
                                        @error('gift_suggestions.2')
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
                                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" id="toggle-password">
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
                                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" id="toggle-password-confirmation">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                    {{-- <a href="{{ route('login') }}" class="mb-2 md:mb-0 text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Iniciar Sesión</a> --}}
                                    <button type="submit" class="mb-2 md:mb-0 bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg hover:bg-green-800">Registrar mi participación</button>
                                </div>
                            </div>
                        </form>
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

        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 100%;
            margin-left: 5px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 50%;
            left: -5px;
            margin-top: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent #555 transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
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

        // Clear temp image on successful form submission (assuming no errors)
        document.querySelector('form').addEventListener('submit', function() {
            if (!@json($errors->any())) {
                tempImageFilename.value = '';
            }
        });

        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('svg').innerHTML = type === 'password' ?
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>' :
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
        });

        document.getElementById('toggle-password-confirmation').addEventListener('click', function() {
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmationInput.setAttribute('type', type);
            this.querySelector('svg').innerHTML = type === 'password' ?
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>' :
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>';
        });

        // Tooltip for mobile (click to show/hide)
        const tooltip = document.querySelector('.tooltip');
        const tooltipText = document.querySelector('.tooltiptext');

        if (tooltip && tooltipText) {
            tooltip.addEventListener('click', function() {
                if (window.innerWidth <= 768) { // Assuming mobile breakpoint
                    tooltipText.style.visibility = tooltipText.style.visibility === 'visible' ? 'hidden' : 'visible';
                    tooltipText.style.opacity = tooltipText.style.opacity === '1' ? '0' : '1';
                }
            });
        }
    </script>
    @endsection
</x-guest-layout>