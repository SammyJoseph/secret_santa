<x-guest-layout>
    <div class="relative min-h-screen flex items-center justify-center bg-center bg-gray-50 bg-gray-500 bg-no-repeat bg-cover relative items-center"
        style="background-image: url('{{ asset('assets/images/xbg.jpg') }}');">
        <div class="absolute bg-black opacity-60 inset-0 z-0"></div>
        <div class="max-w-5xl w-full shadow-lg z-10 overflow-auto">
            @include('user._partials.validation-messages')

            <div class="p-4 lg:p-10">            
                <div class="grid gap-4 lg:gap-8 grid-cols-1 lg:grid-cols-2" x-data="{ showModal: false, modalImage: '' }">
                    <!-- Left Column: Secret Friend Placeholder -->
                    @include('user._partials.my-secret-santa')                    

                    <!-- Right Column: User Profile Edit -->
                    @include('user._partials.my-profile')
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
            @for($index = 0; $index < 3; $index++)
                const tempGiftFilename{{ $index }} = document.getElementById('temp-gift-image-{{ $index }}').value;
                if (tempGiftFilename{{ $index }}) {
                    document.getElementById('gift-preview-{{ $index }}').src = '{{ url("/temp-image-gift") }}/' + tempGiftFilename{{ $index }};
                    document.getElementById('gift-preview-{{ $index }}').parentElement.classList.remove('hidden');
                }
            @endfor
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
        @for($index = 0; $index < 3; $index++)
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
                        const existingImagePath = '{{ $user->giftSuggestions[$index]->reference_image_path ?? '' }}';
                        giftPreview{{ $index }}.src = existingImagePath ? '{{ asset('storage/') }}/' + existingImagePath : '';
                        if (!giftPreview{{ $index }}.src) {
                            giftPreview{{ $index }}.parentElement.classList.add('hidden');
                        }
                    });
                } else {
                    // If no file selected, reset to default
                    const existingImagePath = '{{ $user->giftSuggestions[$index]->reference_image_path ?? '' }}';
                    giftPreview{{ $index }}.src = existingImagePath ? '{{ asset('storage/') }}/' + existingImagePath : '';
                    tempGiftImage{{ $index }}.value = '';
                    if (!giftPreview{{ $index }}.src) {
                        giftPreview{{ $index }}.parentElement.classList.add('hidden');
                    }
                }
            });
        @endfor

        // Clear temp image on successful form submission (assuming no errors)
        document.querySelector('form').addEventListener('submit', function() {
            if (!@json($errors->any())) {
                tempImageFilename.value = '';
                @for($index = 0; $index < 3; $index++)
                    document.getElementById('temp-gift-image-{{ $index }}').value = '';
                @endfor
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