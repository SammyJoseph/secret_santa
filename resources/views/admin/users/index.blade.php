<x-app-layout>
    <div class="py-1 sm:py-12" x-data="{ showModal: false, modalImage: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-3 sm:p-6 bg-white shadow-md sm:rounded-lg">
                @if(session('updated'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                        <p class="font-medium">{{ session('updated') }}</p>
                    </div>
                @endif
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm" role="alert">
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Filtros por Año y Familia, y Botón Crear Usuario -->
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                            <label for="year_filter" class="sr-only">Filtrar por Año</label>
                            <select name="year" id="year_filter"
                                    onchange="document.getElementById('family_filter').value = ''; this.form.submit();"
                                    class="rounded-lg border-gray-300 py-1.5 px-3 text-sm font-semibold text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($availableYears as $yr)
                                    <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>
                                        Año {{ $yr }}
                                    </option>
                                @endforeach
                                <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>Todos los años</option>
                            </select>

                            <label for="family_filter" class="sr-only">Filtrar por Familia</label>
                            <select name="family_group_id" id="family_filter"
                                    onchange="this.form.submit()"
                                    class="rounded-lg border-gray-300 py-1.5 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todas las familias</option>
                                @foreach($familyGroups as $group)
                                    <option value="{{ $group->id }}" {{ (string)$familyGroupId === (string)$group->id ? 'selected' : '' }}>
                                        {{ $group->name }} @if($selectedYear === 'all')(Año {{ $group->year }})@endif ({{ $group->users_count }} {{ Str::plural('usuario', $group->users_count) }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                        @if(!empty($familyGroupId))
                            <a href="{{ route('admin.users.index', ['year' => $selectedYear]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Limpiar filtro
                            </a>
                        @endif
                    </div>
                    <a href="{{ route('admin.users.create', array_filter(['family_group_id' => $familyGroupId])) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm text-sm gap-1.5 transition">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear Usuario
                    </a>
                </div>

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
                                    Familia
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Nombre
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Última Actividad
                                </th>                                
                                @if(config('app.env') === 'local')
                                <th scope="col" class="px-6 py-3">
                                    Amigo Secreto
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3">
                                    Grupo Familiar (Interno)
                                </th>                                
                                <th scope="col" class="px-6 py-3">
                                    Fecha de Registro
                                </th>                                
                                <th scope="col" class="px-6 py-3">
                                    Acciones
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->familyGroup)
                                        @php
                                            $hash = abs(crc32($user->familyGroup->id));
                                            $hue = $hash % 360;
                                            $bgColor = "hsl({$hue}, 85%, 90%)";
                                            $textColor = "hsl({$hue}, 85%, 25%)";
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                            style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                            {{ $user->familyGroup->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Sin familia</span>
                                    @endif
                                </td>
                                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        @php
                                            $profileSrc = asset('assets/images/profile.jpg');
                                            if ($user->funny_profile_photo_path) {
                                                $profileSrc = Storage::url($user->funny_profile_photo_path);
                                            } elseif ($user->profile_photo_path) {
                                                $profileSrc = Storage::url($user->profile_photo_path);
                                            }
                                        @endphp
                                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 cursor-pointer" @click="showModal = true; modalImage = document.getElementById('profile-preview-{{ $user->id }}').src">
                                            <img id="profile-preview-{{ $user->id }}" class="w-full h-full object-cover" src="{{ $profileSrc }}" alt="Avatar de {{ $user->nickname ?: $user->name }}">
                                        </div>
                                    </div>
                                    <div class="ps-3">
                                        <div class="text-base font-semibold"><a href="{{ route('admin.users.edit', $user) }}">{{ $user->nickname ?: $user->name }}</a></div>
                                        <div class="font-normal text-gray-500 font-mono text-xs">{{ $user->phone ?? $user->dni }}</div>
                                    </div>
                                </th>

                                <td class="px-6 py-4">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                </td>                                                            

                                @if(config('app.env') === 'local')
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->secretSantaAssignment)
                                        <div class="flex items-center">
                                            <img class="w-8 h-8 rounded-full object-cover mr-2" src="{{ $user->secretSantaAssignment->receiver->profile_photo_url }}" alt="Avatar de {{ $user->secretSantaAssignment->receiver->name }}">
                                            <div>
                                                <div class="text-sm font-medium">{{ $user->secretSantaAssignment->receiver->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $user->secretSantaAssignment->receiver->phone ?? $user->secretSantaAssignment->receiver->dni }}</div>
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
                                                            <div class="text-gray-500 font-mono">{{ $familyMember->phone ?? $familyMember->dni }}</div>
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
                                    <div class="flex space-x-2 items-center">
                                        @if(!$user->secretSantaAssignment)
                                            <a href="{{ route('admin.users.profile', $user) }}" title="Ver perfil" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="#2563eb"><path d="M480-120v-80h280v-560H480v-80h280q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H480Zm-80-160-55-58 102-102H120v-80h327L345-622l55-58 200 200-200 200Z"/></svg>
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs whitespace-nowrap">No disponible</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr class="bg-white border-b border-gray-200">
                                <td colspan="{{ config('app.env') === 'local' ? 8 : 7 }}" class="px-6 py-8 text-center text-gray-500">
                                    <p class="text-base font-medium">
                                        No hay usuarios registrados {{ $selectedYear !== 'all' ? 'para el año ' . $selectedYear : '' }}{{ !empty($familyGroupId) ? ' en esta familia' : '' }}.
                                    </p>
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
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
