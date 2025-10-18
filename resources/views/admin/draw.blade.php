<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sorteo de Secret Santa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(!$hasAssignments)
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                ¡Es hora de realizar el sorteo!
                            </h3>
                            <p class="text-gray-600 mb-6">
                                Una vez iniciado el sorteo, todos los participantes recibirán su amigo secreto asignado de manera aleatoria.
                            </p>
                            <form action="{{ route('admin.draw.start') }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg text-xl transition duration-300 ease-in-out transform hover:scale-105">
                                    START DRAW 🎄
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Sorteo Completado
                            </h3>
                            <p class="text-gray-600 mb-6">
                                El sorteo ha sido realizado exitosamente. Todos los participantes tienen asignado un amigo secreto.
                            </p>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <h4 class="text-green-800 font-semibold mb-2">Verificación del Sorteo:</h4>
                                <div class="max-w-md mx-auto">
                                    <ul class="text-green-700 text-sm text-left">
                                        <li>✅ Total de asignaciones: {{ $assignments->count() }}</li>
                                        <li>✅ Cada participante tiene exactamente un amigo secreto asignado</li>
                                        <li>✅ Nadie se asignó a sí mismo</li>
                                        <li>✅ Todas las asignaciones son únicas</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Participante</th>
                                            <th scope="col" class="px-6 py-3">Estado de Asignación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments as $assignment)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $assignment->giver->name }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✅ Asignado
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>