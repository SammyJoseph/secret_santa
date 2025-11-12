<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-5xl w-full shadow-lg z-10 overflow-auto">
            @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center p-4 border-t-4 border-green-300 bg-green-50 dark:bg-green-800 dark:border-green-600" role="alert">
                <svg class="shrink-0 w-4 h-4 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="m424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                </svg>
                <div class="ms-3 text-sm font-medium text-gray-800 dark:text-gray-300">
                    {{ session('success') }}
                </div>
                <button @click="show = false" type="button"
                        class="ms-auto -mx-1.5 -my-1.5 text-gray-500 rounded-lg focus:ring-2 focus:ring-gray-400 p-1.5 hover:bg-gray-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                        aria-label="Close">
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            @endif
            <div class="p-6 lg:p-10">            
                <div class="grid gap-8 grid-cols-1 lg:grid-cols-2" x-data="{ showModal: false, modalImage: '' }">
                    <!-- Left Column: Secret Friend Placeholder -->
                    @if($isRevealed && $secretSanta)
                    <div class="flex flex-col items-center bg-white rounded-lg p-6">
                        <h3 class="font-semibold text-lg text-center mb-6">Mi Amigo Secreto es <span class="text-[#F8B229]">{{ $secretSanta->nickname ?: $secretSanta->name }}</span></h3>
                        <div class="w-40 lg:w-60 aspect-square bg-white rounded-full border-2 border-[#F8B229] overflow-hidden mb-3 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('secret-friend-preview').src">
                            <img id="secret-friend-preview" class="w-full h-full object-cover rounded-full" src="{{ $secretSanta->funny_profile_photo_path ? asset('storage/' . $secretSanta->funny_profile_photo_path) : asset('assets/images/profile.jpg') }}" alt="Foto de {{ $secretSanta->name }}">
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-600 mb-4">A {{ $secretSanta->nickname ?: $secretSanta->name }} le gustaría recibir cualquiera de estas opciones:</p>
                            <div class="space-y-3">
                                @foreach($secretSanta->giftSuggestions as $suggestion)
                                    <div class="bg-gray-50 rounded-lg p-4 flex items-center space-x-3">
                                        <div class="w-12 h-12 flex-none rounded-lg overflow-hidden cursor-pointer" @click="showModal = true; modalImage = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}'">
                                            <img class="w-full h-full object-cover rounded-lg" src="{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}" alt="Imagen de referencia">
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-700">{{ $suggestion->suggestion }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-center text-[#146B3A] text-sm font-semibold">
                            ¡Mucha suerte en tu búsqueda del regalo perfecto! 🎁
                        </div>                        
                    </div>
                    @else
                    <div class="flex flex-col items-center space-y-6 bg-white rounded-lg p-6">
                        <h3 class="font-semibold text-lg">Mi Amigo Secreto es...</h3>
                        <div class="w-3/4 aspect-square bg-gray-200 rounded-full flex items-center justify-center flip-container">
                            <svg class="h-16 w-16 text-gray-500 coin-flip" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div id="countdown" class="text-center text-gray-600 text-sm">
                            <!-- Countdown will be populated by JavaScript -->
                        </div>
                    </div>
                    @endif

                    <!-- Right Column: User Profile Edit -->
                    <div class="flex flex-col bg-white rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3 lg:mb-0">
                            <h2 class="font-semibold text-lg">{{ !$canEditProfile ? 'Mi Perfil' : 'Editar mi Perfil' }}</h2>
                            @if($user->is_admin)
                            <a href="{{ route('admin.users.index') }}" title="Admin">
                                <svg class="w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z"/></svg>
                            </a>
                            @endif
                        </div>
                        <form action="{{ route('user.update', $user) }}" method="POST" enctype="multipart/form-data" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                            @csrf
                            @method('PUT')
                            <div class="form">
                                <div class="lg:space-y-2">
                                    <div class="flex items-center pb-3 lg:py-6">
                                        <div class="w-12 h-12 mr-4 flex-none rounded-full border overflow-hidden cursor-pointer" @click="showModal = true; modalImage = document.getElementById('profile-preview').src">
                                            <img id="profile-preview" class="w-12 h-12 object-cover rounded-full"
                                                src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/images/profile.jpg') }}"
                                                alt="Avatar Upload">
                                        </div>
                                        <label class="cursor-pointer @if(!$canEditProfile) pointer-events-none opacity-70 @endif">
                                            <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">Cambiar Foto</span>
                                            <input type="file" name="profile_photo_path" id="profile-image-input" class="hidden" accept="image/*">
                                        </label>
                                        <input type="hidden" name="temp_image_filename" id="temp-image-filename" value="{{ session('temp_profile_image') }}">
                                    </div>
                                    @error('profile_photo_path')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="lg:flex flex-row lg:space-x-4 w-full text-xs">
                                    <div class="space-y-2 w-full text-xs mb-3 lg:mb-0">
                                        <label class="font-semibold text-gray-600 py-2">Nombre</label>
                                        <input placeholder="Nombre" class="appearance-none block w-full @if(!$canEditProfile) bg-gray-50 @endif border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required" @if(!$canEditProfile) disabled @endif
                                            type="text" name="name" id="name" value="{{ old('name', $user->name) }}">
                                        @error('name')
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2 w-full text-xs">
                                        <label class="font-semibold text-gray-600 py-2">DNI</label>
                                        <input placeholder="87654321"
                                            class="bg-gray-50 appearance-none block w-full border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400"
                                            disabled
                                            type="text"
                                            name="dni"
                                            id="dni"
                                            value="{{ $user->dni }}"
                                            />
                                    </div>
                                </div>
                                <div class="mt-6">
                                    @foreach($user->giftSuggestions ?? [] as $index => $suggestion)
                                    @php
                                    if ($index == 0) {
                                        $themeColor = '#BB2528';
                                    } elseif ($index == 1) {
                                        $themeColor = '#244372';
                                    } else {                                        
                                        $themeColor = '#414f4f';
                                    }
                                    @endphp
                                    <div class="space-y-2 w-full text-xs mb-6">
                                        <label class=" font-semibold text-[{{ $themeColor }}] py-2">Mi sugerencia de regalo {{ $index + 1 }}</label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[{{ $themeColor }}] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[{{ $themeColor }}] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <textarea name="gift_suggestions[{{ $index }}]" required @if(!$canEditProfile) disabled @endif
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 @if(!$canEditProfile) bg-gray-50 @endif border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                                placeholder="Me gustaría recibir..." autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.' . $index, $suggestion->suggestion) }}</textarea>
                                        </div>
                                        <div class="flex items-center mb-4">
                                            <div class="w-12 h-12 flex-none rounded-lg overflow-hidden cursor-pointer mr-2" @click="showModal = true; modalImage = document.getElementById('gift-preview-{{ $index }}').src">
                                                <img id="gift-preview-{{ $index }}" class="w-full h-full object-cover rounded-lg" src="{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}" alt="Imagen de referencia">
                                            </div>
                                            <label class="cursor-pointer @if(!$canEditProfile) pointer-events-none opacity-70 @endif">
                                                <span class="focus:outline-none text-white text-xs py-1 px-3 rounded-full bg-[{{ $themeColor }}] hover:shadow-lg">Subir imagen referencial</span>
                                                <input type="file" name="reference_image_path_{{ $index }}" id="gift-image-input-{{ $index }}" class="hidden" accept="image/*">
                                            </label>
                                            <input type="hidden" name="temp_gift_image_{{ $index }}" id="temp-gift-image-{{ $index }}" value="{{ session('temp_gift_images')[$index] ?? '' }}">
                                        </div>
                                        @error('gift_suggestions.' . $index)
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @endforeach
                                </div>
                                <div class="mt-5 text-right lg:space-x-3 lg:block flex flex-col-reverse">
                                    <button type="button" onclick="document.getElementById('logout-form').submit();" class="text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Cerrar Sesión</button>
                                    <button type="submit" class="mb-2 lg:mb-0 bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg @if(!$canEditProfile) opacity-70 @else hover:bg-green-800 @endif" @if(!$canEditProfile) disabled @endif :disabled="isSubmitting">
                                        <span x-show="!isSubmitting">Guardar Cambios</span>
                                        <span x-show="isSubmitting" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Guardando
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                            @csrf
                        </form>

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
        
    </div>

    @section('css')
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }

        @keyframes coinFlip {
            from { transform: rotateY(0deg); }
            to { transform: rotateY(360deg); }
        }

        .coin-flip {
            animation: coinFlip 2s linear infinite;
            transform-style: preserve-3d;
        }

        .flip-container {
            perspective: 1000px;
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

            // Load gift image previews
            @foreach($user->giftSuggestions ?? [] as $index => $suggestion)
                const tempGiftFilename{{ $index }} = document.getElementById('temp-gift-image-{{ $index }}').value;
                if (tempGiftFilename{{ $index }}) {
                    document.getElementById('gift-preview-{{ $index }}').src = '{{ url("/temp-image-gift") }}/' + tempGiftFilename{{ $index }};
                    document.getElementById('gift-preview-{{ $index }}').parentElement.classList.remove('hidden');
                }
            @endforeach
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
                    profilePreview.src = '{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/images/profile.jpg') }}';
                });
            } else {
                // If no file selected, reset to default
                profilePreview.src = '{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/images/profile.jpg') }}';
                tempImageFilename.value = '';
            }
        });

        // Handle gift image file selection with AJAX upload
        @foreach($user->giftSuggestions ?? [] as $index => $suggestion)
            const giftInput{{ $index }} = document.getElementById('gift-image-input-{{ $index }}');
            const giftPreview{{ $index }} = document.getElementById('gift-preview-{{ $index }}');
            const tempGiftImage{{ $index }} = document.getElementById('temp-gift-image-{{ $index }}');

            giftInput{{ $index }}.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    // Show immediate preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        giftPreview{{ $index }}.src = e.target.result;
                        giftPreview{{ $index }}.parentElement.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);

                    // Upload to temp storage
                    const formData = new FormData();
                    formData.append('reference_image_path', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route("user.temp-upload-gift", $index) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.filename) {
                            tempGiftImage{{ $index }}.value = data.filename;
                            // Update preview with uploaded image
                            giftPreview{{ $index }}.src = '{{ url("/temp-image-gift") }}/' + data.filename;
                        }
                    })
                    .catch(error => {
                        console.error('Upload failed:', error);
                        // Reset to default if upload fails
                        giftPreview{{ $index }}.src = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : '' }}';
                        if (!giftPreview{{ $index }}.src) {
                            giftPreview{{ $index }}.parentElement.classList.add('hidden');
                        }
                    });
                } else {
                    // If no file selected, reset to default
                    giftPreview{{ $index }}.src = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : '' }}';
                    tempGiftImage{{ $index }}.value = '';
                    if (!giftPreview{{ $index }}.src) {
                        giftPreview{{ $index }}.parentElement.classList.add('hidden');
                    }
                }
            });
        @endforeach

        // Clear temp image on successful form submission (assuming no errors)
        document.querySelector('form').addEventListener('submit', function() {
            if (!@json($errors->any())) {
                tempImageFilename.value = '';
                @foreach($user->giftSuggestions ?? [] as $index => $suggestion)
                    document.getElementById('temp-gift-image-{{ $index }}').value = '';
                @endforeach
            }
        });

        // Countdown to reveal date from server
        function updateCountdown() {
            const countdownElement = document.getElementById('countdown');
            if (!countdownElement) return; // Exit if element doesn't exist

            const targetDate = new Date('{{ $revealDateJs }}');
            const now = new Date();
            const diff = targetDate - now;

            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `
                    <p>La información se revelará en:</p>
                    <p class="font-semibold">${days} días, ${hours} horas, ${minutes} minutos, ${seconds} segundos</p>
                `;
            } else {
                countdownElement.innerHTML = '<p>¡El momento ha llegado!</p><p class="mt-2"><a href="#" onclick="location.reload()" class="text-blue-600 hover:text-blue-800 underline">Haz clic aquí para ver tu Amigo Secreto</a></p>';
            }
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call
    </script>
    @endsection
</x-guest-layout>