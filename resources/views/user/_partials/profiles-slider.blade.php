@if($isRevealed && $secretSanta)
@push('css')
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

<div class="flex justify-center mt-6 lg:mt-8">
    <div class="flex flex-col items-center justify-center">
        @include('user._partials.view-profiles-btn')

        <div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/95 backdrop-blur-md transition-opacity duration-300 opacity-0 select-none">
            
            <!-- Botón CERRAR (Esquina superior derecha) -->
            <button onclick="closeModal()" type="button" class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/60 hover:text-white hover:bg-white/10 rounded-full z-50 p-2 sm:p-2.5 transition-all cursor-pointer focus:outline-none" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- WRAPPER PRINCIPAL (Columna para imagen + controles + miniaturas) -->
            <div onclick="event.stopPropagation()" class="flex flex-col items-center w-full max-w-sm sm:max-w-md lg:max-w-lg mx-auto px-4 relative">
                
                <!-- CONTENEDOR CON FLECHAS DE NAVEGACIÓN (Desktop/Tablet) -->
                <div class="relative w-full">
                    <!-- Botón Anterior (Desktop) -->
                    <button type="button"
                            onclick="prevSlide()"
                            class="hidden sm:flex absolute -left-12 lg:-left-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 items-center justify-center rounded-full bg-white/10 hover:bg-white/25 active:scale-95 text-white/80 hover:text-white backdrop-blur-md transition-all shadow-lg cursor-pointer focus:outline-none"
                            aria-label="Anterior">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- TARJETA DE IMAGEN PRINCIPAL -->
                    <div class="relative w-full aspect-[3/4] max-h-[60vh] sm:max-h-[65vh] bg-gray-900 rounded-3xl overflow-hidden shadow-2xl ring-1 ring-white/10">
                        
                        <!-- Contador de participantes -->
                        <div class="absolute top-4 left-4 z-20 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-white/90 text-xs font-semibold tracking-wider select-none">
                            <span id="slide-counter">1 / {{ $familyGroup->users->count() }}</span>
                        </div>

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
                                <div class="absolute bottom-0 left-0 w-full p-6 sm:p-8 pb-8 text-white">
                                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight drop-shadow-md">{{ $user->nickname ?: $user->name }}</h2>
                                </div>
                            </div>
                            @endforeach

                        </div>
                    </div>

                    <!-- Botón Siguiente (Desktop) -->
                    <button type="button"
                            onclick="nextSlide()"
                            class="hidden sm:flex absolute -right-12 lg:-right-16 top-1/2 -translate-y-1/2 z-40 w-11 h-11 items-center justify-center rounded-full bg-white/10 hover:bg-white/25 active:scale-95 text-white/80 hover:text-white backdrop-blur-md transition-all shadow-lg cursor-pointer focus:outline-none"
                            aria-label="Siguiente">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- TIRA HORIZONTAL DE MINIATURAS (AVATARES ESTILO WHATSAPP/STORIES) -->
                <div class="mt-4 sm:mt-5 w-full">
                    <div id="thumbs-container" class="flex items-center gap-3 overflow-x-auto py-2 px-2 no-scrollbar scroll-smooth">
                        <div class="flex items-center gap-3 mx-auto min-w-min">
                            @foreach ($familyGroup->users as $index => $u)
                                @php
                                    $thumbSrc = asset('assets/images/profile.jpg');
                                    if ($u->funny_profile_photo_path) {
                                        $thumbSrc = Storage::url($u->funny_profile_photo_path);
                                    } elseif ($u->profile_photo_path) {
                                        $thumbSrc = Storage::url($u->profile_photo_path);
                                    }
                                    $displayName = $u->nickname ?: $u->name;
                                    $shortName = \Illuminate\Support\Str::limit(explode(' ', trim($displayName))[0], 8, '..');
                                @endphp
                                <button type="button"
                                        onclick="goToSlide({{ $index }})"
                                        id="thumb-btn-{{ $index }}"
                                        class="thumb-btn flex-shrink-0 flex flex-col items-center focus:outline-none transition-all duration-300 group cursor-pointer"
                                        title="{{ $displayName }}">
                                    <div class="thumb-avatar relative w-12 h-12 sm:w-14 sm:h-14 rounded-full overflow-hidden transition-all duration-300 ring-2 ring-white/10 opacity-40 group-hover:opacity-85 scale-95">
                                        <img src="{{ $thumbSrc }}"
                                             class="w-full h-full object-cover pointer-events-none"
                                             alt="{{ $displayName }}">
                                    </div>
                                    <span class="thumb-name text-[11px] sm:text-xs text-white/40 font-medium truncate max-w-[58px] sm:max-w-[70px] text-center mt-1.5 transition-colors duration-300">
                                        {{ $shortName }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        const lightbox = document.getElementById('lightbox');
        const track = document.getElementById('carousel-track');
        const thumbBtns = document.querySelectorAll('.thumb-btn');
        const thumbsContainer = document.getElementById('thumbs-container');
        const slideCounter = document.getElementById('slide-counter');
        
        let currentIndex = 0;
        const totalSlides = {{ $familyGroup->users->count() }};

        // --- Lightbox Functions ---
        window.openModal = function() {
            if (!lightbox) return;
            lightbox.classList.remove('hidden');
            setTimeout(() => {
                lightbox.classList.add('flex');
                lightbox.classList.remove('opacity-0');
            }, 10);
            document.body.classList.add('overflow-hidden');
            currentIndex = 0;
            updateCarousel();
        };

        window.closeModal = function() {
            if (!lightbox) return;
            lightbox.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');
            setTimeout(() => {
                lightbox.classList.remove('flex');
                lightbox.classList.add('hidden');
            }, 300);
        };

        // Clic fuera del contenido cierra el modal
        if (lightbox) {
            lightbox.addEventListener('click', window.closeModal);
        }

        // --- Carousel Logic ---
        function updateCarousel() {
            if (!track) return;
            track.style.transform = `translateX(-${currentIndex * 100}%)`;

            // Actualizar contador
            if (slideCounter) {
                slideCounter.textContent = `${currentIndex + 1} / ${totalSlides}`;
            }

            // Actualizar estado de las miniaturas
            thumbBtns.forEach((btn, index) => {
                const avatar = btn.querySelector('.thumb-avatar');
                const name = btn.querySelector('.thumb-name');

                if (index === currentIndex) {
                    if (avatar) {
                        avatar.classList.remove('opacity-40', 'ring-white/10', 'scale-95');
                        avatar.classList.add('ring-[#F8B229]', 'opacity-100', 'scale-110', 'shadow-[0_0_12px_rgba(248,178,41,0.45)]');
                    }
                    if (name) {
                        name.classList.remove('text-white/40', 'font-medium');
                        name.classList.add('text-[#F8B229]', 'font-bold');
                    }
                    // Auto-scroll para centrar la miniatura activa en pantalla
                    btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } else {
                    if (avatar) {
                        avatar.classList.remove('ring-[#F8B229]', 'opacity-100', 'scale-110', 'shadow-[0_0_12px_rgba(248,178,41,0.45)]');
                        avatar.classList.add('ring-white/10', 'opacity-40', 'scale-95');
                    }
                    if (name) {
                        name.classList.remove('text-[#F8B229]', 'font-bold');
                        name.classList.add('text-white/40', 'font-medium');
                    }
                }
            });
        }

        window.goToSlide = function(index) {
            currentIndex = index;
            updateCarousel();
        };

        window.nextSlide = function() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateCarousel();
        };

        window.prevSlide = function() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateCarousel();
        };

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
                    window.nextSlide();
                } else {
                    window.prevSlide();
                }
            }
        }

        // Soporte rueda del ratón en miniaturas para scroll horizontal en desktop
        if (thumbsContainer) {
            thumbsContainer.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    thumbsContainer.scrollLeft += e.deltaY;
                }
            }, { passive: false });
        }

        // Navegación accesible con teclado (Esc para cerrar, Flechas para cambiar)
        document.addEventListener('keydown', (e) => {
            if (!lightbox || lightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') window.closeModal();
            if (e.key === 'ArrowRight') window.nextSlide();
            if (e.key === 'ArrowLeft') window.prevSlide();
        });
    </script>
@endpush
@endif
