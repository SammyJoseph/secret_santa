<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
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
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">{{ $familyGroup->name }}</h2>
                    <a href="{{ route('admin.family-groups.index') }}" class="text-blue-600 hover:text-blue-900">
                        ← Volver a la lista
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Información General</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-xs text-gray-500">Slug:</dt>
                                <dd class="text-sm font-medium"><code class="bg-gray-100 px-2 py-1 rounded">{{ $familyGroup->slug }}</code></dd>
                            </div>
                            @if($familyGroup->description)
                            <div>
                                <dt class="text-xs text-gray-500">Descripción:</dt>
                                <dd class="text-sm">{{ $familyGroup->description }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-xs text-gray-500">Estado:</dt>
                                <dd class="text-sm">
                                    @if($familyGroup->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactiva</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Estadísticas</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-xs text-gray-500">Usuarios Registrados:</dt>
                                <dd class="text-2xl font-bold text-blue-600">{{ $familyGroup->users_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Estado del Sorteo:</dt>
                                <dd class="text-sm">
                                    @if($familyGroup->hasDrawn())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Realizado</span>
                                    @elseif($familyGroup->canDraw())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Listo para sortear</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pendiente</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Registro:</dt>
                                <dd class="text-sm">
                                    @if($familyGroup->hasDrawn())
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">🔒 Cerrado</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Abierto</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-blue-50 p-6 rounded-lg border-2 border-blue-200 mb-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">🔗 Enlace de Registro</h3>
                    <p class="text-sm text-blue-800 mb-3">Comparte este enlace con los participantes de esta familia:</p>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               id="registrationUrl" 
                               value="{{ $familyGroup->registration_url }}" 
                               readonly
                               class="flex-1 px-4 py-2 border border-blue-300 rounded-lg bg-white text-sm font-mono">
                        <button onclick="copyToClipboard()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap">
                            📋 Copiar
                        </button>
                    </div>
                    <p class="text-xs text-blue-700 mt-2" id="copyMessage" style="display: none;">
                        ✓ Enlace copiado al portapapeles
                    </p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Configuración de Fechas</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <span class="text-2xl">🎲</span>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">Habilitar Sorteo</h4>
                                <p class="text-sm text-gray-600">{{ $familyGroup->enable_draw_at ? $familyGroup->enable_draw_at->format('d/m/Y H:i') : 'No configurada' }}</p>
                                @if($familyGroup->enable_draw_at)
                                    <p class="text-xs mt-1">
                                        @if($familyGroup->canDraw())
                                            <span class="text-green-600">✓ Listo para sortear</span>
                                        @else
                                            <span class="text-gray-500">Faltan {{ now()->diffForHumans($familyGroup->enable_draw_at, true) }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <span class="text-2xl">🎁</span>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">Revelar Amigo Secreto</h4>
                                <p class="text-sm text-gray-600">{{ $familyGroup->reveal_date ? $familyGroup->reveal_date->format('d/m/Y H:i') : 'No configurada' }}</p>
                                @if($familyGroup->reveal_date)
                                    <p class="text-xs mt-1">
                                        @if($familyGroup->isRevealed())
                                            <span class="text-green-600">✓ Ya revelado</span>
                                        @else
                                            <span class="text-gray-500">Faltan {{ now()->diffForHumans($familyGroup->reveal_date, true) }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <span class="text-2xl">✏️</span>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">Fecha Límite de Edición</h4>
                                <p class="text-sm text-gray-600">{{ $familyGroup->profile_edit_end_date ? $familyGroup->profile_edit_end_date->format('d/m/Y H:i') : 'No configurada' }}</p>
                                @if($familyGroup->profile_edit_end_date)
                                    <p class="text-xs mt-1">
                                        @if($familyGroup->canEditProfile())
                                            <span class="text-green-600">✓ Edición permitida</span>
                                        @else
                                            <span class="text-red-600">✗ Edición cerrada</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Miembros de esta Familia (Lista tipo toasts de colores - Solo Lectura) --}}
                <div class="bg-gray-50 p-6 rounded-lg mb-6 border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <span>👥</span> Miembros de la Familia
                            <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full">
                                {{ $familyGroup->users->count() }}
                            </span>
                        </h3>
                    </div>

                    @if($familyGroup->users->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($familyGroup->users as $member)
                                @php
                                    $hash = abs(crc32($member->dni . $member->name));
                                    $hue = $hash % 360;
                                    $bgColor = "hsl({$hue}, 85%, 95%)";
                                    $textColor = "hsl({$hue}, 85%, 25%)";
                                    $borderColor = "hsl({$hue}, 60%, 82%)";
                                    $profileSrc = $member->profile_photo_url;
                                @endphp
                                <div class="flex items-center space-x-3 p-2.5 rounded-xl border shadow-sm transition hover:shadow"
                                     style="background-color: {{ $bgColor }}; border-color: {{ $borderColor }};">
                                    <img src="{{ $profileSrc }}" alt="{{ $member->name }}" class="w-10 h-10 rounded-full object-cover border-2 bg-white shadow-sm" style="border-color: {{ $borderColor }};">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold truncate" style="color: {{ $textColor }};">
                                            {{ $member->nickname ?: $member->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 font-mono">
                                            DNI: {{ $member->dni }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500 text-sm">
                            <span class="text-2xl block mb-1">🎁</span>
                            Aún no hay miembros registrados en esta familia.
                        </div>
                    @endif
                </div>

                <div class="flex justify-between">
                    <div class="space-x-2">
                        @if(!$familyGroup->isDefault() && !$familyGroup->hasDrawn())
                            <a href="{{ route('admin.family-groups.edit', $familyGroup) }}" 
                               class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Editar
                            </a>
                        @endif
                        
                        @if($familyGroup->hasDrawn())
                            <form action="{{ route('admin.draw.reset') }}" method="POST" class="inline-block" 
                                  onsubmit="return confirm('⚠️ ¿ESTÁS SEGURO?\n\nEsto eliminará PERMANENTEMENTE el sorteo actual de esta familia.\n\nTodos los participantes perderán su amigo secreto asignado y tendrás que realizar el sorteo nuevamente.\n\n¿Deseas continuar?');">
                                @csrf
                                <input type="hidden" name="family_group_id" value="{{ $familyGroup->id }}">
                                <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded shadow">
                                    ⚠️ Deshacer Sorteo
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.draw', ['family_group_id' => $familyGroup->id]) }}" 
                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Ver Sorteo
                        </a>
                    </div>
                    @if(!$familyGroup->isDefault() && $familyGroup->users_count == 0)
                        <form action="{{ route('admin.family-groups.destroy', $familyGroup) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta familia?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Eliminar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @section('js')
    <script>
        function copyToClipboard() {
            const input = document.getElementById('registrationUrl');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            
            navigator.clipboard.writeText(input.value).then(() => {
                const message = document.getElementById('copyMessage');
                message.style.display = 'block';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            });
        }
    </script>
    @endsection
</x-app-layout>