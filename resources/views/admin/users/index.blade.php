<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios Registrados') }}
        </h2>
    </x-slot>

    <div class="py-1 sm:py-12" x-data="{ showModal: false, modalImage: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-md sm:rounded-lg">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                @if(config('app.env') === 'local')
                                <th scope="col" class="px-6 py-3">
                                    #
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3">
                                    Nombre
                                </th>
                                @if(config('app.env') === 'local')
                                <th scope="col" class="px-6 py-3">
                                    Amigo Secreto
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3">
                                    Grupo Familiar
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Última Actividad
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Fecha de Registro
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                @if(config('app.env') === 'local')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $loop->count - $loop->iteration + 1 }}
                                </td>
                                @endif
                                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('profile-preview-{{ $user->id }}').src">
                                            <img id="profile-preview-{{ $user->id }}" class="w-full h-full object-cover" src="{{ $user->profile_photo_url }}" alt="Avatar de {{ $user->name }}">
                                        </div>
                                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('funny-preview-{{ $user->id }}').src">
                                            <img id="funny-preview-{{ $user->id }}" class="w-full h-full object-cover" src="{{ $user->funny_profile_photo_path ? Storage::url($user->funny_profile_photo_path) : asset('assets/images/profile.jpg') }}" alt="Foto divertida de {{ $user->name }}">
                                        </div>
                                    </div>
                                    <div class="ps-3">
                                        <div class="text-base font-semibold"><a href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a></div>
                                        <div class="font-normal text-gray-500">{{ $user->dni }}</div>
                                    </div>
                                </th>

                                @if(config('app.env') === 'local')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->secretSantaAssignment)
                                        <div class="flex items-center">
                                            <img class="w-8 h-8 rounded-full object-cover mr-2" src="{{ $user->secretSantaAssignment->receiver->profile_photo_url }}" alt="Avatar de {{ $user->secretSantaAssignment->receiver->name }}">
                                            <div>
                                                <div class="text-sm font-medium">{{ $user->secretSantaAssignment->receiver->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->secretSantaAssignment->receiver->dni }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">No asignado</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4">
                                    @php
                                        $allFamilyMembers = $user->getAllFamilyMembers();
                                    @endphp
                                    @if($allFamilyMembers->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($allFamilyMembers as $familyMember)
                                                <div class="flex items-center justify-between bg-blue-50 p-1 rounded text-xs">
                                                    <div class="flex items-center">
                                                        <img class="w-5 h-5 rounded-full object-cover mr-2" src="{{ $familyMember->profile_photo_url }}" alt="Avatar de {{ $familyMember->name }}">
                                                        <div class="flex space-x-1">
                                                            <div class="font-medium whitespace-nowrap">{{ $familyMember->name }}</div>
                                                            <div class="text-gray-500">{{ $familyMember->dni }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white border-b border-gray-200">
                                <td colspan="{{ config('app.env') === 'local' ? 8 : 7 }}" class="px-6 py-4 text-center text-gray-500">
                                    No hay usuarios registrados aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Image Modal -->
                <div x-show="showModal" @keydown.escape.window="showModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" style="display: none;">
                    <div @click.away="showModal = false" class="relative px-4 rounded-lg shadow-lg max-w-lg w-full">
                        <img x-bind:src="modalImage" alt="Profile image large" class="w-full h-auto rounded-md">
                        <button @click="showModal = false" class="absolute top-0 right-0 mt-2 mr-2 text-white bg-gray-800 rounded-full p-1 hover:bg-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>