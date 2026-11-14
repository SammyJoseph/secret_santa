@if($isRevealed && $secretSanta)
<div class="flex flex-col items-center bg-white rounded-lg p-5 lg:p-6">
    <div class="flex flex-col items-center mb-6">
        <h3 class="font-semibold text-lg text-center">Mi Amigo Secreto es <span class="text-[#F8B229]">{{ $secretSanta->nickname ?: $secretSanta->name }}</span></h3>
    </div>
    <div class="w-40 lg:w-60 aspect-square bg-white rounded-full border-2 border-[#F8B229] overflow-hidden mb-3 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('secret-friend-preview').src">
        @php
            $ssProfileSrc = asset('assets/images/no-image.jpg');
            if ($secretSanta->funny_profile_photo_path) {
                $ssProfileSrc = Storage::url($secretSanta->funny_profile_photo_path);
            } elseif ($secretSanta->profile_photo_path) {
                $ssProfileSrc = Storage::url($secretSanta->profile_photo_path);
            }

            $validSuggestions = $secretSanta->giftSuggestions ? $secretSanta->giftSuggestions->filter(function($s) {
                return !empty(trim($s->suggestion ?? '')) || !empty($s->reference_image_path);
            }) : collect();
        @endphp
        <img id="secret-friend-preview" class="w-full h-full object-cover rounded-full" src="{{ $ssProfileSrc }}" alt="Foto de {{ $secretSanta->name }}">
    </div>
    <div class="mb-4 w-full">
        @if($validSuggestions->isNotEmpty())
            <p class="text-sm font-semibold text-gray-600 mb-4 text-center">
                A {{ $secretSanta->nickname ?: $secretSanta->name }} le gustaría recibir {{ $validSuggestions->count() > 1 ? 'cualquiera de estas opciones:' : 'esta opción:' }}
            </p>
            <div class="space-y-3">
                @foreach($validSuggestions as $suggestion)
                    <div class="bg-gray-50 rounded-lg p-4 flex items-center space-x-3">
                        <div class="w-12 h-12 flex-none rounded-lg overflow-hidden cursor-pointer" @click="showModal = true; modalImage = '{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}'">
                            <img class="w-full h-full object-cover rounded-lg" src="{{ $suggestion->reference_image_path ? asset('storage/' . $suggestion->reference_image_path) : asset('assets/images/no-image.jpg') }}" alt="Imagen de referencia">
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-700">{!! $suggestion->formatted_suggestion ?? e($suggestion->suggestion) !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-5 text-center border border-dashed border-gray-200 my-4">
                <span class="text-2xl mb-1 block">🎁</span>
                <p class="text-sm font-medium text-gray-600">No escribió una sugerencia</p>
                <p class="text-xs text-gray-400 mt-1">¡Sorpréndele con un buen detalle navideño!</p>
            </div>
        @endif
    </div>
    <div class="text-center text-[#146B3A] text-sm font-semibold">
        ¡Mucha suerte en tu búsqueda del regalo perfecto! 🎁
    </div>   
    
    <!-- Slider of participants (mobile only) -->
    @include('user._partials.profiles-slider')
</div>
@else
<div class="flex flex-col items-center space-y-6 bg-white rounded-lg p-6">
    <div class="flex flex-col items-center">
        <h3 class="font-semibold text-lg text-center">Mi Amigo Secreto es...</h3>
    </div>
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