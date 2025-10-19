<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios Registrados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-md sm:rounded-lg">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input id="checkbox-all-search" type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="checkbox-all-search" class="sr-only">checkbox</label>
                                    </div>
                                </th>
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
                                    Sugerencias de Regalo
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
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="w-4 p-4">
                                    <div class="flex items-center">
                                        <input id="checkbox-table-search-{{ $user->id }}" type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="checkbox-table-search-{{ $user->id }}" class="sr-only">checkbox</label>
                                    </div>
                                </td>
                                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
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
                                                <div class="flex items-center justify-between bg-blue-50 p-2 rounded text-xs">
                                                    <div class="flex items-center">
                                                        <img class="w-5 h-5 rounded-full object-cover mr-2" src="{{ $familyMember->profile_photo_url }}" alt="Avatar de {{ $familyMember->name }}">
                                                        <div class="flex space-x-1">
                                                            <div class="font-medium">{{ $familyMember->name }}</div>
                                                            <div class="text-gray-500">{{ $familyMember->dni }}</div>
                                                        </div>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.users.remove-family', $user) }}" class="ml-1" onsubmit="return confirm('¿Estás seguro de que quieres remover este familiar del grupo?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="family_member_id" value="{{ $familyMember->id }}">
                                                        <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-bold">×</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.assign-family', $user) }}" class="flex items-center mt-2">
                                        @csrf
                                        <select name="family_member_id" class="text-sm border border-gray-300 rounded px-2 py-1 mr-2" required>
                                            <option value="">Agregar al grupo familiar</option>
                                            @php
                                                $familyIds = $allFamilyMembers->pluck('id')->push($user->id);
                                                if ($user->family_id) {
                                                    $familyIds = $familyIds->merge($users->where('family_id', $user->family_id)->pluck('id'));
                                                }
                                            @endphp
                                            @foreach($users->whereNotIn('id', $familyIds) as $potentialFamily)
                                                <option value="{{ $potentialFamily->id }}">{{ $potentialFamily->name }} ({{ $potentialFamily->dni }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">+</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <ul class="list-disc list-inside">
                                        @foreach($user->giftSuggestions as $suggestion)
                                            <li class="whitespace-nowrap">{{ $suggestion->suggestion }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
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
