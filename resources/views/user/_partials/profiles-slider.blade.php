@if($isRevealed && $secretSanta)
<div class="flex lg:hidden justify-center mt-8">
    <div class="flex flex-col items-center justify-center">
        @include('user._partials.view-profiles-btn')

        <div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/95 backdrop-blur-sm transition-opacity duration-300 opacity-0">
            
            <!-- Botón CERRAR (Esquina superior derecha) -->
            <button onclick="closeModal()" class="absolute top-4 right-4 text-white/60 hover:text-white z-50 p-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- WRAPPER PRINCIPAL (Columna para imagen + puntos) -->
            <div onclick="event.stopPropagation()" class="flex flex-col items-center w-full max-w-sm mx-4">
                
                <!-- TARJETA DE IMAGEN -->
                <div class="relative w-full aspect-[3/4] bg-gray-900 rounded-3xl overflow-hidden shadow-2xl ring-1 ring-white/10">
                    
                    <!-- TRACK -->
                    <div id="carousel-track" class="flex h-full transition-transform duration-500 ease-out touch-pan-y cursor-grab active:cursor-grabbing">
                        
                        @foreach ($familyGroup->users as $user)
                        <div class="w-full h-full flex-shrink-0 relative select-none">
                            @php
                                $profileSrc = asset('assets/images/profile.jpg');
                                if ($user->funny_profile_photo_path) {
                                    $profileSrc = Storage::url($user->funny_profile_photo_path);
                                } elseif ($user->profile_photo_path) {
                                    $profileSrc = Storage::url($user->profile_photo_path);
                                }
                            @endphp
                            <img src="{{ $profileSrc }}"
                                class="w-full h-full object-cover pointer-events-none" alt="Perfil de {{ $user->nickname ?: $user->name }}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 w-full p-8 pb-10 text-white">
                                <h2 class="text-3xl font-bold tracking-tight drop-shadow-md">{{ $user->nickname ?: $user->name }}</h2>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

                <!-- PUNTOS DE NAVEGACIÓN -->
                <div class="mt-6 flex justify-center space-x-1">
                    @foreach ($familyGroup->users as $index => $user)
                        <button onclick="goToSlide({{ $index }})" class="dot w-2 h-2 rounded-full bg-gray-600 transition-all duration-300 hover:bg-gray-400"></button>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        const lightbox = document.getElementById('lightbox');
        const track = document.getElementById('carousel-track');
        const dots = document.querySelectorAll('.dot');
        
        let currentIndex = 0;
        const totalSlides = {{ $familyGroup->users->count() }};

        // --- Lightbox Functions ---
        function openModal() {
            lightbox.classList.remove('hidden');
            setTimeout(() => {
                lightbox.classList.add('flex');
                lightbox.classList.remove('opacity-0');
            }, 10);
            currentIndex = 0;
            updateCarousel();
        }

        function closeModal() {
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.remove('flex');
                lightbox.classList.add('hidden');
            }, 300);
        }

        // Clic fuera del contenido cierra el modal
        lightbox.addEventListener('click', closeModal);

        // --- Carousel Logic ---
        function updateCarousel() {
            if (!track) return;
            track.style.transform = `translateX(-${currentIndex * 100}%)`;

            // Actualizar estilo de los puntos
            dots.forEach((dot, index) => {
                if (index === currentIndex) {
                    dot.classList.remove('bg-gray-600', 'w-2');
                    dot.classList.add('bg-white', 'w-8'); // Activo: Blanco y alargado
                } else {
                    dot.classList.add('bg-gray-600', 'w-2');
                    dot.classList.remove('bg-white', 'w-8'); // Inactivo: Gris oscuro y pequeño
                }
            });
        }

        window.goToSlide = function(index) {
            currentIndex = index;
            updateCarousel();
        }

        // --- Swipe Logic ---
        let touchStartX = 0;
        let touchEndX = 0;
        const minSwipeDistance = 50;

        if (track) {
            track.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, {passive: true});

            track.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleGesture();
            }, {passive: true});

            // Soporte mouse (Desktop)
            track.addEventListener('mousedown', (e) => { touchStartX = e.screenX; });
            track.addEventListener('mouseup', (e) => { 
                touchEndX = e.screenX; 
                handleGesture(); 
            });
        }

        function handleGesture() {
            let distance = touchEndX - touchStartX;
            if (Math.abs(distance) > minSwipeDistance) {
                if (distance < 0) {
                    currentIndex = (currentIndex + 1) % totalSlides;
                } else {
                    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                }
                updateCarousel();
            }
        }
    </script>
@endpush
@endif