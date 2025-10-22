<x-app-layout>
    <div class="bg-gradient min-h-screen">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-20">
            <div class="container">
                <h1>🎅 Secret Santa 🎄</h1>
                <button class="btn-sortear" onclick="iniciarSorteo()">
                    🎁 Iniciar Sorteo
                </button>
            </div>

            <div class="shuffle-container" id="shuffleContainer"></div>

            <div class="success-message" id="successMessage">
                <div class="checkmark"></div>
                <h2>¡Sorteo Completado!</h2>
                <p>Los amigos secretos han sido asignados.<br>Cada participante puede ver su resultado en su perfil.</p>
            </div>        
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
                color: white;
                font-size: 3rem;
                margin-bottom: 2rem;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }

            .btn-sortear {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                border: none;
                padding: 20px 60px;
                font-size: 1.5rem;
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
            }

            .shuffle-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                pointer-events: none;
                z-index: 5;
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

            .success-message {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) scale(0);
                background: white;
                padding: 50px 80px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.4);
                z-index: 20;
                opacity: 0;
                transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }

            .success-message.show {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }

            .success-message h2 {
                color: #667eea;
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }

            .success-message p {
                color: #666;
                font-size: 1.2rem;
                line-height: 1.6;
            }

            .checkmark {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .checkmark::after {
                content: "✓";
                color: white;
                font-size: 3rem;
                font-weight: bold;
            }
        </style>
    @endsection
    @section('js')
        <script>
            // Array de participantes con imágenes reales
            const participantes = @json($users->map(function($user) {
                return [
                    'id' => $user->id,
                    'imagen' => $user->profile_photo_url
                ];
            }));

            function iniciarSorteo() {
                const btn = document.querySelector('.btn-sortear');
                const container = document.getElementById('shuffleContainer');
                const successMsg = document.getElementById('successMessage');
                
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
                
                // Después de 10.5 segundos, mostrar mensaje de éxito
                setTimeout(() => {
                    // Ocultar imágenes
                    const images = container.querySelectorAll('.participant-image');
                    images.forEach(img => {
                        img.style.opacity = '0';
                    });
                    
                    // Mostrar mensaje de éxito
                    setTimeout(() => {
                        successMsg.classList.add('show');
                        
                        // Habilitar botón nuevamente después de 3 segundos
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.textContent = '🎁 Iniciar Sorteo';
                            successMsg.classList.remove('show');
                        }, 3000);
                    }, 500);
                }, 10500);
            }
        </script>
    @endsection
</x-app-layout>
