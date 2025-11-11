<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Crear Nueva Familia</h2>
                    <p class="text-gray-600 mt-2">Configure una nueva familia para el sorteo Secret Santa</p>
                </div>

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

                <form action="{{ route('admin.family-groups.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Nombre de la Familia <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Ej: Familia García, Los Rodríguez">
                        <p class="mt-1 text-sm text-gray-500">El nombre descriptivo que verán los participantes</p>
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700">
                            Identificador (Slug) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="slug" id="slug" required
                               value="{{ old('slug') }}"
                               pattern="[a-z0-9-_]+"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="ej: garcia, rodriguez, familia-perez">
                        <p class="mt-1 text-sm text-gray-500">
                            Solo letras minúsculas, números, guiones y guiones bajos. Se usará en la URL: 
                            <code class="bg-gray-100 px-1 rounded">dominio.com/registro?fam=<span class="text-blue-600">tu-slug</span></code>
                        </p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Descripción (Opcional)
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Descripción adicional sobre esta familia">{{ old('description') }}</textarea>
                    </div>

                    <hr class="my-6">

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">📅 Configuración de Fechas</h3>
                        <p class="text-sm text-blue-800 mb-4">Todas las fechas son obligatorias y deben seguir un orden lógico</p>
                    </div>

                    <div>
                        <label for="enable_draw_at" class="block text-sm font-medium text-gray-700">
                            🎲 Fecha de Habilitación del Sorteo <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="enable_draw_at" id="enable_draw_at" required
                               value="{{ old('enable_draw_at') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">A partir de esta fecha, el admin podrá realizar el sorteo para esta familia</p>
                    </div>

                    <div>
                        <label for="reveal_date" class="block text-sm font-medium text-gray-700">
                            🎁 Fecha de Revelación del Amigo Secreto <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="reveal_date" id="reveal_date" required
                               value="{{ old('reveal_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Los usuarios verán a su amigo secreto desde esta fecha. Debe ser posterior a la fecha de sorteo.</p>
                    </div>

                    <div>
                        <label for="profile_edit_end_date" class="block text-sm font-medium text-gray-700">
                            ✏️ Fecha Límite para Editar Perfil <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="profile_edit_end_date" id="profile_edit_end_date" required
                               value="{{ old('profile_edit_end_date') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Después de esta fecha, los usuarios no podrán editar su perfil. Debe ser posterior a la fecha de revelación.</p>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <p class="text-sm text-yellow-800">
                            <strong>⚠️ Importante:</strong> Una vez creada la familia, las fechas solo se podrán editar antes de realizar el sorteo.
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.family-groups.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Crear Familia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>