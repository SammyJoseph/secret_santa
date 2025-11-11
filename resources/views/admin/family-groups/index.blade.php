<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Gestión de Familias</h2>
                    <a href="{{ route('admin.family-groups.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        + Nueva Familia
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Familia
                                </th>
                                <th class="px-6 py-3 border-b border-gray-300 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Slug
                                </th>
                                <th class="px-6 py-3 border-b border-gray-300 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Usuarios
                                </th>
                                <th class="px-6 py-3 border-b border-gray-300 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 border-b border-gray-300 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Registro
                                </th>
                                <th class="px-6 py-3 border-b border-gray-300 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($familyGroups as $group)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $group->name }}
                                                    @if($group->isDefault())
                                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Original
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($group->description)
                                                    <div class="text-sm text-gray-500">{{ $group->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <code class="bg-gray-100 px-2 py-1 rounded">{{ $group->slug }}</code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ $group->users_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($group->hasDrawn())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✓ Sorteado
                                            </span>
                                        @elseif($group->canDraw())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Listo
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($group->hasDrawn())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                🔒 Cerrado
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✓ Abierto
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('admin.family-groups.show', $group) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Ver
                                        </a>
                                        @if(!$group->isDefault() && !$group->hasDrawn())
                                            <a href="{{ route('admin.family-groups.edit', $group) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Editar
                                            </a>
                                        @endif
                                        @if(!$group->isDefault() && $group->users_count == 0)
                                            <form action="{{ route('admin.family-groups.destroy', $group) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de eliminar esta familia?')">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No hay familias registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Información importante:</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>Original:</strong> La familia original no puede ser editada ni eliminada</li>
                        <li>• <strong>Sorteado:</strong> Una vez realizado el sorteo, el registro se cierra automáticamente</li>
                        <li>• <strong>Cerrado:</strong> No se pueden registrar más usuarios en familias con sorteo realizado</li>
                        <li>• <strong>Eliminar:</strong> Solo se pueden eliminar familias sin usuarios registrados</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>