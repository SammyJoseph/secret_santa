<x-app-layout>
    <div class="bg-gradient min-h-screen">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
            <div class="container">
                <h1 class="text-white text-3xl md:text-5xl font-bold mb-6 md:mb-10">🎅 Amigo Secreto 🎄</h1>
                
                <!-- Selector de Año y Familia -->
                <div class="mb-8 bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                    <form method="GET" action="{{ route('admin.draw') }}" class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <div class="flex items-center gap-2">
                            <label for="year_select" class="text-white font-medium text-sm sm:text-base whitespace-nowrap sr-only">
                                Año:
                            </label>
                            <select name="year" id="year_select"
                                    onchange="document.getElementById('family_group_select').value = ''; this.form.submit();"
                                    class="rounded-lg border-white/30 bg-white/90 text-gray-900 font-bold shadow-lg text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400">
                                @foreach($availableYears as $yr)
                                    <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>
                                        Año {{ $yr }}
                                    </option>
                                @endforeach
                                <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>Todos los años</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label for="family_group_select" class="text-white font-medium text-sm sm:text-base whitespace-nowrap sr-only">
                                Familia:
                            </label>
                            <select name="family_group_id" id="family_group_select"
                                    onchange="this.form.submit()"
                                    class="flex-1 sm:min-w-[280px] rounded-lg border-white/30 bg-white/90 text-gray-900 font-medium shadow-lg text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400">
                                @forelse($familyGroups as $group)
                                    <option value="{{ $group->id }}" {{ (string)$selectedFamilyGroupId === (string)$group->id ? 'selected' : '' }}>
                                        {{ $group->name }} @if($selectedYear === 'all')(Año {{ $group->year }})@endif
                                        ({{ $group->users_count }} {{ Str::plural('usuario', $group->users_count) }})
                                        @if($group->hasDrawn()) - ✓ Sorteado @endif
                                    </option>
                                @empty
                                    <option value="">No hay familias en este año</option>
                                @endforelse
                            </select>
                        </div>
                    </form>
                    
                    @if($selectedFamilyGroup)
                    <div class="mt-4 text-white text-sm space-y-1">
                        <p><strong>Familia:</strong> {{ $selectedFamilyGroup->name }} @if($selectedYear === 'all' || $selectedFamilyGroup->year != date('Y'))<span class="text-xs bg-white/20 px-2 py-0.5 rounded-full ml-1 font-mono">Año {{ $selectedFamilyGroup->year }}</span>@endif</p>
                        <p><strong>Estado:</strong>
                            @if($hasAssignments)
                                <span class="text-green-300 font-semibold">✓ Sorteo realizado</span>
                            @elseif($selectedFamilyGroup->canDraw())
                                <span class="text-blue-300 font-semibold">Listo para sortear</span>
                            @else
                                <span class="text-yellow-300 font-semibold">Esperando fecha de sorteo</span>
                            @endif
                        </p>
                        <p><strong>Registro:</strong>
                            @if($hasAssignments)
                                <span class="text-red-300 font-semibold">🔒 Cerrado</span>
                            @else
                                <span class="text-green-300 font-semibold">✓ Abierto</span>
                            @endif
                        </p>
                    </div>
                    @else
                    <div class="mt-4 text-white text-center text-sm py-2">
                        No hay ninguna familia registrada para el año {{ $selectedYear }}.
                    </div>
                    @endif
                </div>

                @if($selectedFamilyGroup)
                    @if($hasAssignments)
                        <div class="text-center">
                            <p class="text-white text-xl mb-8 drop-shadow-md">Todos los participantes de {{ $selectedFamilyGroup->name }} han sido asignados correctamente.</p>
                        </div>
                    @else
                        @php
                            $currentTime = now();
                            $enableDrawTime = $selectedFamilyGroup->enable_draw_at;
                            $isEnabled = $enableDrawTime ? $currentTime->gte($enableDrawTime) : false;
                        @endphp
                        <button class="btn-sortear text-xl px-8 py-5 {{ $isEnabled ? '' : 'disabled' }}"
                                onclick="{{ $isEnabled ? 'iniciarSorteo()' : '' }}"
                                {{ $isEnabled ? '' : 'disabled' }}>
                            🎁 Iniciar Sorteo para {{ $selectedFamilyGroup->name }}
                        </button>
                        
                        @if(!$isEnabled && $enableDrawTime)
                            <p class="text-white text-center mt-4">
                                El sorteo se habilitará el {{ $enableDrawTime->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    @endif

                    <div class="text-center mt-12">
                        <ul class="list-none p-0 grid grid-cols-3 md:grid-cols-6 gap-4 max-w-6xl mx-auto participant-grid">
                            @foreach($users as $user)
                                <li class="flex flex-col items-center bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20 shadow-lg participant-card {{ $hasAssignments ? 'completed' : '' }}">
                                    <img src="{{ $user->profile_photo_url }}" class="w-16 h-16 rounded-full mb-2 object-cover border-2 border-white/30" alt="{{ $user->name }}">
                                    <span class="text-white text-sm font-medium drop-shadow-md text-center">{{ $user->name }}</span>
                                    @if($hasAssignments)
                                        <span class="text-green-400 text-xl font-bold mt-1 animate-pulse success-check">✓</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="shuffle-container" id="shuffleContainer"></div>

        </div>
    </div>

    @section('css')
        <style>
            .bg-gradient {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .container {
                text-align: center;
                position: relative;
                z-index: 10;
            }

            h1 {
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }

            .btn-sortear {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                border: none;
                font-weight: bold;
                border-radius: 50px;
                cursor: pointer;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 2px;
            }

            .btn-sortear:hover {
                transform: translateY(-3px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            }

            .btn-sortear:active {
                transform: translateY(-1px);
            }

            .btn-sortear:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                background: linear-gradient(135deg, #666 0%, #999 100%);
            }

            .btn-sortear.disabled {
                opacity: 0.6;
                cursor: not-allowed;
                background: linear-gradient(135deg, #666 0%, #999 100%);
            }

            .shuffle-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                pointer-events: none;
                z-index: 20;
            }

            .participant-image {
                position: absolute;
                width: 80px;
                height: 80px;
                border-radius: 50%;                
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                object-fit: cover;
                opacity: 0;
            }

            .participant-image.shuffling {
                opacity: 1;
                animation: shuffle 10s ease-in-out;
            }

            @keyframes shuffle {
                0%, 100% {
                    transform: translate(0, 0) rotate(0deg) scale(1);
                }
                10% {
                    transform: translate(var(--x1), var(--y1)) rotate(180deg) scale(1.2);
                }
                20% {
                    transform: translate(var(--x2), var(--y2)) rotate(360deg) scale(0.8);
                }
                30% {
                    transform: translate(var(--x3), var(--y3)) rotate(540deg) scale(1.3);
                }
                40% {
                    transform: translate(var(--x4), var(--y4)) rotate(720deg) scale(1.2);
                }
                50% {
                    transform: translate(var(--x5), var(--y5)) rotate(900deg) scale(0.8);
                }
                60% {
                    transform: translate(var(--x6), var(--y6)) rotate(1080deg) scale(1.3);
                }
                70% {
                    transform: translate(var(--x7), var(--y7)) rotate(1260deg) scale(1.2);
                }
                80% {
                    transform: translate(var(--x8), var(--y8)) rotate(1440deg) scale(0.8);
                }
                90% {
                    transform: translate(var(--x9), var(--y9)) rotate(1620deg) scale(1.3);
                }
            }


        </style>
    @endsection
    @section('js')
        <script>
            // Array de participantes con imágenes reales
            const participantes = @json($users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'imagen' => $user->profile_photo_url
                ];
            }));

            function iniciarSorteo() {
                const btn = document.querySelector('.btn-sortear');
                const container = document.getElementById('shuffleContainer');

                // Deshabilitar botón
                btn.disabled = true;
                btn.textContent = 'Sorteando...';

                // Limpiar contenedor
                container.innerHTML = '';

                // Crear y animar imágenes
                participantes.forEach((participante, index) => {
                    const img = document.createElement('img');
                    img.src = participante.imagen;
                    img.className = 'participant-image';

                    // Posición inicial (centro de la pantalla)
                    const startX = window.innerWidth / 2 - 40;
                    const startY = window.innerHeight / 2 - 40;
                    img.style.left = `${startX}px`;
                    img.style.top = `${startY}px`;

                    // Generar posiciones aleatorias para la animación
                    for (let i = 1; i <= 9; i++) {
                        const x = (Math.random() - 0.5) * window.innerWidth * (0.7 + Math.random() * 0.2);
                        const y = (Math.random() - 0.5) * window.innerHeight * (0.7 + Math.random() * 0.2);
                        img.style.setProperty(`--x${i}`, `${x}px`);
                        img.style.setProperty(`--y${i}`, `${y}px`);
                    }

                    container.appendChild(img);

                    // Iniciar animación con delay escalonado
                    setTimeout(() => {
                        img.classList.add('shuffling');
                    }, index * 50);
                });

                // Después de 10 segundos, hacer la llamada AJAX
                setTimeout(() => {
                    fetch('/admin/draw/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            family_group_id: {{ $selectedFamilyGroupId ?? 'null' }}
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Ocultar imágenes
                            const images = container.querySelectorAll('.participant-image');
                            images.forEach(img => {
                                img.style.opacity = '0';
                            });

                            // Mostrar éxito
                            setTimeout(() => {
                                showSuccess(data.users);
                            }, 500);
                        } else {
                            alert(data.error || 'Error desconocido');
                            btn.disabled = false;
                            btn.textContent = '🎁 Iniciar Sorteo';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al realizar el sorteo: ' + error.message);
                        btn.disabled = false;
                        btn.textContent = '🎁 Iniciar Sorteo';
                    });
                }, 10000);
            }

            function showSuccess(users) {
                const container = document.querySelector('.container');
                const btn = document.querySelector('.btn-sortear');
                const participantsGrid = document.querySelector('.participant-grid');
                const textCenterDiv = document.querySelector('.text-center');

                // Ocultar botón
                if (btn) {
                    btn.style.display = 'none';
                }

                // Crear y mostrar mensaje de éxito
                const messageDiv = document.createElement('div');
                messageDiv.className = 'text-center';
                const messageP = document.createElement('p');
                messageP.textContent = 'Todos los participantes han sido asignados correctamente.';
                messageP.className = 'text-white text-xl mb-8 drop-shadow-md';
                messageDiv.appendChild(messageP);

                // Insertar mensaje antes del div de participantes
                container.insertBefore(messageDiv, textCenterDiv);

                // Agregar checks a los participantes existentes
                const participantCards = participantsGrid.querySelectorAll('.participant-card');
                participantCards.forEach(card => {
                    card.classList.add('completed');
                    const checkIcon = document.createElement('span');
                    checkIcon.textContent = '✓';
                    checkIcon.className = 'text-green-400 text-xl font-bold mt-1 animate-pulse success-check';
                    card.appendChild(checkIcon);
                });
            }
        </script>
    @endsection
</x-app-layout>