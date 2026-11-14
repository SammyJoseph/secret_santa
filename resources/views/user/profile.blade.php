<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-5xl w-full shadow-lg z-10 overflow-auto">
            @include('user._partials.validation-messages')

            <div class="p-4 lg:p-10">
                @if(!empty($isPastYear))
                    <div class="mb-4 p-4 rounded-lg bg-amber-500/90 text-white shadow flex items-center space-x-3 text-xs sm:text-sm">
                        <svg class="h-6 w-6 flex-shrink-0 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Estás viendo los resultados de tu participación en la Navidad <strong>{{ $familyGroup->year }}</strong>. Para participar en la edición <strong>{{ date('Y') }}</strong>, solicita a tu organizador el enlace de invitación de tu familia e ingresa a través de él.</span>
                    </div>
                @endif            
                <div class="grid gap-4 lg:gap-8 grid-cols-1 lg:grid-cols-2" x-data="{ showModal: false, modalImage: '' }">                    
                    <!-- Right Column: User Profile Edit -->
                    @include('user._partials.my-profile')

                    <!-- Left Column: Secret Friend Placeholder -->
                    @include('user._partials.my-secret-santa')                    
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
        const defaultProfileSrc = '{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/images/profile.jpg') }}';

        if (profileInput && profilePreview) {
            profileInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const dataUrl = e.target.result;
                        profilePreview.src = dataUrl;

                        // Normalizar avatar a JPEG estándar con nombre limpio para evitar bloqueos de WAF/ModSecurity
                        const img = new Image();
                        img.onload = function() {
                            try {
                                const canvas = document.createElement('canvas');
                                let width = img.width;
                                let height = img.height;
                                const maxDim = 800;
                                if (width > maxDim || height > maxDim) {
                                    if (width > height) {
                                        height = Math.round((height * maxDim) / width);
                                        width = maxDim;
                                    } else {
                                        width = Math.round((width * maxDim) / height);
                                        height = maxDim;
                                    }
                                }
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.fillStyle = '#FFFFFF';
                                ctx.fillRect(0, 0, width, height);
                                ctx.drawImage(img, 0, 0, width, height);

                                canvas.toBlob(function(blob) {
                                    if (blob && window.DataTransfer && profileInput) {
                                        const cleanFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                                        const dt = new DataTransfer();
                                        dt.items.add(cleanFile);
                                        profileInput.files = dt.files;
                                    }
                                }, 'image/jpeg', 0.90);
                            } catch (err) {
                                console.warn('Normalización de avatar omitida:', err);
                            }
                        };
                        img.src = dataUrl;
                    };
                    reader.readAsDataURL(file);
                } else {
                    profilePreview.src = defaultProfileSrc;
                }
            });
        }

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
                    <p>Se revelará en:</p>
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