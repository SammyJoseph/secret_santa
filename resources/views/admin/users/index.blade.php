<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios Registrados') }}
        </h2>
    </x-slot>

    <div class="py-1 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-md sm:rounded-lg">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
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
                                    Fecha de Registro
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr class="bg-white border-b border-gray-200 hover:bg-gray-50">
                                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap">
                                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                        <img class="w-full h-full object-cover" src="{{ $user->profile_photo_url }}" alt="Avatar de {{ $user->name }}">
                                    </div>
                                    <div class="ps-3">
                                        <div class="text-base font-semibold">{{ $user->name }}</div>
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
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-blue-600 hover:underline">Editar</a>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white border-b border-gray-200">
                                <td colspan="{{ config('app.env') === 'local' ? 7 : 6 }}" class="px-6 py-4 text-center text-gray-500">
                                    No hay usuarios registrados aún.
                                </td>
                            </tr>
                            @endforelse                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>