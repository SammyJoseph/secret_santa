<div class="flex flex-col bg-white rounded-lg p-5 lg:p-6">
    <div class="flex items-center justify-between mb-3 lg:mb-0">
        <h2 class="font-semibold text-lg">{{ isset($isAdminView) ? 'Perfil de Usuario' : 'Mi Perfil' }}</h2>
        @if($user->is_admin && !isset($isAdminView))
        <a href="{{ route('admin.users.index') }}" title="Admin">
            <svg class="w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor"><path d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z"/></svg>
        </a>
        @elseif(isset($isAdminView))
        <a href="{{ route('admin.users.index') }}" title="Volver al listado de usuarios">
            <svg class="w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
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
                    <label class="cursor-pointer">
                        <span class="focus:outline-none text-white text-sm py-2 px-4 rounded-full bg-[#F8B229] hover:bg-amber-400 hover:shadow-lg">Cambiar Foto</span>
                        <input @disabled(now()->gt($profileEditEndDate)) type="file" name="profile_photo_path" id="profile-image-input" class="hidden" accept="image/*">
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
                    <input @disabled(now()->gt($profileEditEndDate))
                        placeholder="Nombre" class="appearance-none block w-full bg-gray-50 border border-gray-200 rounded-lg h-10 px-4 text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400" required="required"
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
                @for($index = 0; $index < 3; $index++)
                    @php
                        $suggestion = $user->giftSuggestions[$index] ?? null;
                        $suggestionValue = $suggestion ? $suggestion->suggestion : '';
                        $referenceImagePath = $suggestion ? $suggestion->reference_image_path : null;
                        $tempGiftImageValue = session('temp_gift_images')[$index] ?? '';
                        
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
                            <textarea @disabled(now()->gt($profileEditEndDate)) name="gift_suggestions[{{ $index }}]" required
                                class="flex-shrink flex-grow flex-auto leading-normal w-px flex-1 bg-gray-50 border border-l-0 border-gray-200 rounded-lg rounded-l-none px-3 relative focus:border-blue focus:shadow text-sm focus:ring-1 focus:ring-[#F8B229] focus:border-[#F8B229] focus:outline-none placeholder-gray-400 resize-none"
                                placeholder="{{ $index == 0 ? 'Me gustaría recibir...' : 'O también me gustaría recibir...' }}" autocomplete="off" style="field-sizing: content; min-height: 2.5rem;">{{ old('gift_suggestions.' . $index, $suggestionValue) }}</textarea>
                        </div>
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 flex-none rounded-lg overflow-hidden cursor-pointer mr-2" @click="showModal = true; modalImage = document.getElementById('gift-preview-{{ $index }}').src">
                                <img id="gift-preview-{{ $index }}" class="w-full h-full object-cover rounded-lg" src="{{ $referenceImagePath ? asset('storage/' . $referenceImagePath) : asset('assets/images/no-image.jpg') }}" alt="Imagen de referencia">
                            </div>
                            <label class="cursor-pointer">
                                <span class="focus:outline-none text-white text-xs py-1 px-3 rounded-full bg-[{{ $themeColor }}] hover:shadow-lg">Subir imagen referencial</span>
                                <input type="file" name="reference_image_path_{{ $index }}" id="gift-image-input-{{ $index }}" class="hidden" accept="image/*">
                            </label>
                            <input type="hidden" name="temp_gift_image_{{ $index }}" id="temp-gift-image-{{ $index }}" value="{{ $tempGiftImageValue }}">
                        </div>
                        @error('gift_suggestions.' . $index)
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                @endfor
            </div>
            <div class="mt-5 text-right lg:space-x-3 lg:block flex flex-col-reverse">
                <button type="button" onclick="document.getElementById('logout-form').submit();" class="text-center bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-full hover:shadow-lg hover:bg-gray-100">Cerrar Sesión</button>
                <button type="submit"
                    :disabled="isSubmitting || {{ now()->gt($profileEditEndDate) ? 'true' : 'false' }}"
                    class="{{ now()->gt($profileEditEndDate) ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#146B3A] hover:bg-green-800' }} mb-3 lg:mb-0 px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-full hover:shadow-lg">
                    <span x-show="!isSubmitting">Guardar Cambios</span>
                    <span x-show="isSubmitting" class="flex items-center justify-center">
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>