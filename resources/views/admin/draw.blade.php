<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sorteo de Secret Santa') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, confirmInput: '', error: false, isDrawing: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
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
                            <button @click="showModal = true" :disabled="isDrawing" class="bg-red-500 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-4 px-8 rounded-lg text-xl transition duration-300 ease-in-out transform hover:scale-105 disabled:transform-none">
                                <span x-show="!isDrawing">INICIAR SORTEO 🎄</span>
                                <span x-show="isDrawing" class="animate-pulse">SORTEANDO...</span>
                            </button>
                        </div>

                        <!-- Confirmation Modal -->
                        <div x-show="showModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" x-on:keydown.escape.window="showModal = false">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity" x-on:click="showModal = false">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto shrink-0 flex items-center justify-center size-12 rounded-full bg-red-100 sm:mx-0 sm:size-10">
                                                <svg class="size-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ms-4 sm:text-start">
                                                <h3 class="text-lg font-medium text-gray-900">
                                                    Confirmar Inicio del Sorteo
                                                </h3>
                                                <div class="mt-4 text-sm text-gray-600">
                                                    <p class="mb-4">Para confirmar, escribe <strong>"iniciar sorteo"</strong> en el campo a continuación:</p>
                                                    <input x-model="confirmInput" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                                    <p x-show="error" class="mt-2 text-red-600 text-sm">El texto no coincide. Inténtalo de nuevo.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <form action="{{ route('admin.draw.start') }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" @click="if (confirmInput.toLowerCase() !== 'iniciar sorteo') { error = true; $event.preventDefault(); } else { error = false; showModal = false; isDrawing = true; }" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Iniciar Sorteo
                                            </button>
                                        </form>
                                        <button @click="showModal = false; confirmInput = ''; error = false;" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                                        @php
                                            $familyAssignmentsCount = 0;
                                            foreach($assignments as $assignment) {
                                                if ($assignment->giver->isFamilyWith($assignment->receiver)) {
                                                    $familyAssignmentsCount++;
                                                }
                                            }
                                        @endphp
                                        @if($familyAssignmentsCount > 0)
                                            <li class="text-orange-600">⚠️ Asignaciones entre familiares: {{ $familyAssignmentsCount }}</li>
                                        @else
                                            <li>✅ No hay asignaciones entre familiares</li>
                                        @endif
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
</x-app-layout>