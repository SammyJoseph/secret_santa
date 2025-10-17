<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
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
                                <div class="md:space-y-2 mb-3">
                                    <div class="flex items-center py-6">
                                        <div class="w-12 h-12 mr-4 flex-none rounded-xl border overflow-hidden">
                                            <img id="profile-preview" class="w-12 h-12 mr-4 object-cover"
                                                src="{{ asset('assets/images/profile.jpg') }}"
                                                alt="Avatar Upload">
                                        </div>
                                        <label class="cursor-pointer ">
                                            <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">Cambiar Foto</span>
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
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Nombre</label>
                                        <input placeholder="Nombre" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                            type="text" name="name" id="name" value="{{ old('name') }}">
                                        @error('name')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">DNI</label>
                                        <input placeholder="87654321"
                                            class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"
                                            required
                                            type="text"
                                            name="dni"
                                            id="dni"
                                            value="{{ old('dni') }}"
                                            minlength="8"
                                            maxlength="8"
                                            pattern="\d{8}"
                                            title="Ingresar 8 dígitos"
                                            />
                                        @error('dni')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <div class="space-y-2 w-full text-xs mb-3">
                                        <label class=" font-semibold text-gray-600 py-2">Mi sugerencia de regalo 1</label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[#BB2528] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[#BB2528] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <input type="text" name="gift_suggestions[0]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 h-10 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"
                                                placeholder="Me gustaría recibir..." value="{{ old('gift_suggestions.0') }}">
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
                                            <input type="text" name="gift_suggestions[1]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 h-10 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"
                                                placeholder="Me gustaría recibir..." value="{{ old('gift_suggestions.1') }}">
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
                                            <input type="text" name="gift_suggestions[2]" required
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 border border-l-0 h-10 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"
                                                placeholder="Me gustaría recibir..." value="{{ old('gift_suggestions.2') }}">
                                        </div>
                                        @error('gift_suggestions.2')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs mt-6">
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Contraseña</label>
                                        <input placeholder="Escribe una contraseña fácil" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
                                            type="password" name="password" id="password">
                                        @error('password')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">Confirmar Contraseña</label>
                                        <input placeholder="Vuelve a escribirla" class="appearance-none block w-full bg-grey-lighter text-grey-darker border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required" type="password"
                                            name="password_confirmation" id="password_confirmation">
                                    </div>
                                </div>
                                <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                    <button type="button" onclick="window.history.back()" class="mb-2 md:mb-0 bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Cancelar</button>
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
    </script>
    @endsection
</x-guest-layout>