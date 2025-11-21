<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Editar Familia: {{ $familyGroup->name }}</h2>
                    <p class="text-gray-600 mt-2">Modifica la configuración de esta familia</p>
                </div>

                @if($familyGroup->hasDrawn())
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Modo de edición restringido</p>
                        <p>Como el sorteo ya se ha realizado, solo puedes modificar la fecha límite para editar perfil.</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Errores de validación:</p>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.family-groups.update', $familyGroup) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre de la Familia <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $familyGroup->name) }}"
                               {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700">
                            Identificador (Slug) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="slug" id="slug" required
                               value="{{ old('slug', $familyGroup->slug) }}"
                               pattern="[a-z0-9-_]+"
                               {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">
                        <p class="mt-1 text-sm text-gray-500">
                            URL actual: <code class="bg-gray-100 px-1 rounded">{{ $familyGroup->registration_url }}</code>
                        </p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Descripción (Opcional)
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">{{ old('description', $familyGroup->description) }}</textarea>
                    </div>

                    <hr class="my-6">

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">📅 Configuración de Fechas</h3>
                    </div>

                    <div>
                        <label for="enable_draw_at" class="block text-sm font-medium text-gray-700">
                            🎲 Fecha de Habilitación del Sorteo <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="enable_draw_at" id="enable_draw_at" required
                               value="{{ old('enable_draw_at', $familyGroup->enable_draw_at ? $familyGroup->enable_draw_at->format('Y-m-d\TH:i') : '') }}"
                               {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">
                    </div>

                    <div>
                        <label for="reveal_date" class="block text-sm font-medium text-gray-700">
                            🎁 Fecha de Revelación del Amigo Secreto <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="reveal_date" id="reveal_date" required
                               value="{{ old('reveal_date', $familyGroup->reveal_date ? $familyGroup->reveal_date->format('Y-m-d\TH:i') : '') }}"
                               {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">
                    </div>

                    <div>
                        <label for="profile_edit_end_date" class="block text-sm font-medium text-gray-700">
                            ✏️ Fecha Límite para Editar Perfil <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="profile_edit_end_date" id="profile_edit_end_date" required
                               value="{{ old('profile_edit_end_date', $familyGroup->profile_edit_end_date ? $familyGroup->profile_edit_end_date->format('Y-m-d\TH:i') : '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $familyGroup->is_active) ? 'checked' : '' }}
                                   {{ $familyGroup->hasDrawn() ? 'disabled' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50">
                            <span class="text-sm font-medium text-gray-700">Familia activa</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Desactivar una familia impedirá nuevos registros incluso si no tiene sorteo</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.family-groups.show', $familyGroup) }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Actualizar Familia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>