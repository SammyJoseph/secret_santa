<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 py-4 md:py-12 px-4 sm:px-6 lg:px-8 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-4xl w-full bg-white rounded-xl shadow-lg z-10 overflow-auto">
            @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center p-4 border-t-4 border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600"
                role="alert">
                <svg class="shrink-0 w-4 h-4 dark:text-gray-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <div class="ms-3 text-sm font-medium text-gray-800 dark:text-gray-300">
                    {{ session('success') }}
                </div>
                <button @click="show = false" type="button"
                        class="ms-auto -mx-1.5 -my-1.5 bg-gray-50 text-gray-500 rounded-lg focus:ring-2 focus:ring-gray-400 p-1.5 hover:bg-gray-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
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
            <div class="p-10">            
                <div class="grid gap-8 grid-cols-1 md:grid-cols-2" x-data="{ showModal: false, modalImage: '' }">
                    <!-- Left Column: Secret Friend Placeholder -->
                    @if($isRevealed && $secretSanta)
                    <div class="flex flex-col items-center">
                        <h3 class="font-semibold text-lg mb-6">Mi Amigo Secreto es <span class="text-[#F8B229]">{{ $secretSanta->name }}</span></h3>
                        <div class="w-40 md:w-60 aspect-square bg-white rounded-full border-2 border-[#F8B229] overflow-hidden mb-3 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('secret-friend-preview').src">
                            <img id="secret-friend-preview" class="w-full h-full object-cover rounded-full" src="{{ $secretSanta->profile_photo_url }}" alt="Foto de {{ $secretSanta->name }}">
                        </div>
                        {{-- <h4 class="text-xl font-bold text-center text-gray-800 mb-3">{{ $secretSanta->name }}</h4> --}}
                        <div class="mb-3 text-center">
                            <p class="text-sm font-semibold text-gray-600 mb-2">A {{ $secretSanta->name }} le gustaría recibir cualquiera de estas opciones:</p>
                            <ol class="list-decimal list-inside text-sm text-gray-500 space-y-1">
                                @foreach($secretSanta->giftSuggestions as $suggestion)
                                    <li>{{ $suggestion->suggestion }}</li>
                                @endforeach
                            </ol>
                        </div>
                        <div class="text-center text-[#146B3A] text-sm font-semibold">
                            ¡Mucha suerte en tu búsqueda del regalo perfecto! 🎁
                        </div>                        
                    </div>
                    @else
                    <div class="flex flex-col items-center space-y-6">
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
                    <div class="flex flex-col">
                        <div class="flex flex-col sm:flex-row items-center">
                            <h2 class="font-semibold text-lg mr-auto">Editar mi Perfil</h2>
                            <div class="w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0"></div>
                        </div>
                        <form action="{{ route('user.update', $user) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form">
                                <div class="md:space-y-2">
                                    <div class="flex items-center pb-3 md:py-6">
                                        <div class="w-12 h-12 mr-4 flex-none rounded-full border overflow-hidden cursor-pointer" @click="showModal = true; modalImage = document.getElementById('profile-preview').src">
                                            <img id="profile-preview" class="w-12 h-12 object-cover rounded-full"
                                                src="{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : asset('assets/images/profile.jpg') }}"
                                                alt="Avatar Upload">
                                        </div>
                                        <label class="cursor-pointer @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) pointer-events-none opacity-70 @endif">
                                            <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">Cambiar Foto</span>
                                            <input type="file" name="profile_photo_path" id="profile-image-input" class="hidden" accept="image/*">
                                        </label>
                                        <input type="hidden" name="temp_image_filename" id="temp-image-filename" value="{{ session('temp_profile_image') }}">
                                    </div>
                                    @error('profile_photo_path')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:flex flex-row md:space-x-4 w-full text-xs">
                                    <div class="space-y-2 w-full text-xs mb-3 md:mb-0">
                                        <label class="font-semibold text-gray-600 py-2">Nombre</label>
                                        <input placeholder="Nombre" class="appearance-none block w-full @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) bg-gray-50 @endif border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required" @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) disabled @endif
                                            type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}">
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
                                            value="{{ auth()->user()->dni }}"
                                            />
                                    </div>
                                </div>
                                <div class="mt-6">
                                    @foreach(auth()->user()->giftSuggestions ?? [] as $index => $suggestion)
                                    <div class="space-y-2 w-full text-xs mb-3">
                                        <label class=" font-semibold text-gray-600 py-2">Mi sugerencia de regalo {{ $index + 1 }}</label>
                                        <div class="flex flex-wrap items-stretch w-full mb-4 relative">
                                            <div class="flex">
                                                <span
                                                    class="flex items-center leading-normal bg-grey-lighter border-1 rounded-r-none border border-r-0 border-[#BB2528] px-3 whitespace-no-wrap text-grey-dark text-sm w-12 h-10 bg-[#BB2528] justify-center items-center  text-xl rounded-lg text-white">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M160-80v-440H80v-240h208q-5-9-6.5-19t-1.5-21q0-50 35-85t85-35q23 0 43 8.5t37 23.5q17-16 37-24t43-8q50 0 85 35t35 85q0 11-2 20.5t-6 19.5h208v240h-80v440H160Zm400-760q-17 0-28.5 11.5T520-800q0 17 11.5 28.5T560-760q17 0 28.5-11.5T600-800q0-17-11.5-28.5T560-840Zm-200 40q0 17 11.5 28.5T400-760q17 0 28.5-11.5T440-800q0-17-11.5-28.5T400-840q-17 0-28.5 11.5T360-800ZM160-680v80h280v-80H160Zm280 520v-360H240v360h200Zm80 0h200v-360H520v360Zm280-440v-80H520v80h280Z"/></svg>
                                                </span>
                                            </div>
                                            <textarea name="gift_suggestions[{{ $index }}]" required @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) disabled @endif
                                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) bg-gray-50 @endif border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                                placeholder="Me gustaría recibir..." autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.' . $index, $suggestion->suggestion) }}</textarea>
                                        </div>
                                        @error('gift_suggestions.' . $index)
                                            <p class="text-red-500 text-xs">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @endforeach
                                </div>
                                <div class="mt-5 text-right md:space-x-3 md:block flex flex-col-reverse">
                                    <button type="button" onclick="document.getElementById('logout-form').submit();" class="text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Cerrar Sesión</button>
                                    <button type="submit" class="mb-2 md:mb-0 bg-[#146B3A] px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) opacity-70 @else hover:bg-green-800 @endif" @if(now()->gt(\Carbon\Carbon::parse($revealDateJs))) disabled @endif>Guardar Cambios</button>
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
                    profilePreview.src = '{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : asset('assets/images/profile.jpg') }}';
                });
            } else {
                // If no file selected, reset to default
                profilePreview.src = '{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : asset('assets/images/profile.jpg') }}';
                tempImageFilename.value = '';
            }
        });

        // Clear temp image on successful form submission (assuming no errors)
        document.querySelector('form').addEventListener('submit', function() {
            if (!@json($errors->any())) {
                tempImageFilename.value = '';
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