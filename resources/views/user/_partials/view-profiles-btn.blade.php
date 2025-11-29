@push('css')
<style>
    .button-container {
        position: relative;
    }

    .christmas-button {
        position: relative;
        padding: 22px 55px;
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(145deg, #16a34a 0%, #15803d 50%, #14532d 100%);
        border: none;
        border-radius: 14px;
        cursor: pointer;
        overflow: hidden;
        box-shadow: 
            0 0 30px rgba(34, 197, 94, 0.4),
            0 8px 25px rgba(0, 0, 0, 0.5),
            inset 0 2px 0 rgba(255, 255, 255, 0.2),
            inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    /* Efecto de resplandor pulsante */
    @keyframes glow {
        0%, 100% {
            box-shadow: 
                0 0 30px rgba(34, 197, 94, 0.4),
                0 8px 25px rgba(0, 0, 0, 0.5),
                inset 0 2px 0 rgba(255, 255, 255, 0.2),
                inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
        50% {
            box-shadow: 
                0 0 50px rgba(34, 197, 94, 0.6),
                0 8px 25px rgba(0, 0, 0, 0.5),
                inset 0 2px 0 rgba(255, 255, 255, 0.2),
                inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
    }

    .christmas-button {
        animation: glow 3s ease-in-out infinite;
    }

    /* Contenedor de nieve */
    .snow-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
    }

    /* Asegurar altura mínima del botón para las animaciones */
    .christmas-button {
        min-height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        /* Forzar GPU acceleration para las animaciones */
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
    }

    /* Estilos específicos para el contexto de Blade */
    .button-container {
        position: relative;
        z-index: 1;
    }

    /* Copos de nieve con caracteres */
    .snowflake {
        position: absolute;
        top: -20px;
        color: #ffffff !important;
        font-variant-emoji: text;
        font-family: sans-serif;
        font-weight: normal;
        z-index: 5;
        font-size: 14px;
        animation: fall linear infinite;
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        user-select: none;
        will-change: transform;
    }

    @keyframes fall {
        0% {
            transform: translateY(0) translateX(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(120px) translateX(30px) rotate(360deg);
            opacity: 0;
        }
    }

    .snowflake:nth-child(1) {
        left: 5%;
        animation-duration: 4s;
        animation-delay: 0s;
        font-size: 16px;
    }

    .snowflake:nth-child(2) {
        left: 20%;
        animation-duration: 4.5s;
        animation-delay: 0.8s;
        font-size: 12px;
    }

    .snowflake:nth-child(3) {
        left: 35%;
        animation-duration: 4.2s;
        animation-delay: 1.5s;
        font-size: 14px;
    }

    .snowflake:nth-child(4) {
        left: 50%;
        animation-duration: 4.8s;
        animation-delay: 0.3s;
        font-size: 18px;
    }

    .snowflake:nth-child(5) {
        left: 65%;
        animation-duration: 4.3s;
        animation-delay: 2s;
        font-size: 13px;
    }

    .snowflake:nth-child(6) {
        left: 80%;
        animation-duration: 4.6s;
        animation-delay: 1s;
        font-size: 15px;
    }

    .snowflake:nth-child(7) {
        left: 15%;
        animation-duration: 4.4s;
        animation-delay: 2.5s;
        font-size: 17px;
    }

    .snowflake:nth-child(8) {
        left: 45%;
        animation-duration: 4.7s;
        animation-delay: 1.8s;
        font-size: 11px;
    }

    .snowflake:nth-child(9) {
        left: 75%;
        animation-duration: 4.9s;
        animation-delay: 0.5s;
        font-size: 16px;
    }

    .snowflake:nth-child(10) {
        left: 90%;
        animation-duration: 4.1s;
        animation-delay: 3s;
        font-size: 14px;
    }

    .snowflake:nth-child(11) {
        left: 25%;
        animation-duration: 5s;
        animation-delay: 2.2s;
        font-size: 12px;
    }

    .snowflake:nth-child(12) {
        left: 60%;
        animation-duration: 4.4s;
        animation-delay: 0.7s;
        font-size: 15px;
    }

    /* Luces navideñas brillantes */
    .lights {
        position: absolute;
        top: 5px;
        left: 0;
        width: 100%;
        height: 8px;
        display: flex;
        justify-content: space-around;
        padding: 0 15px;
        pointer-events: none;
    }

    .light {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        animation: twinkle 1.5s ease-in-out infinite;
    }

    .light:nth-child(1) {
        background: #ffd700;
        box-shadow: 0 0 10px #ffd700;
        animation-delay: 0s;
    }

    .light:nth-child(2) {
        background: #ff6b6b;
        box-shadow: 0 0 10px #ff6b6b;
        animation-delay: 0.3s;
    }

    .light:nth-child(3) {
        background: #4ecdc4;
        box-shadow: 0 0 10px #4ecdc4;
        animation-delay: 0.6s;
    }

    .light:nth-child(4) {
        background: #ffd700;
        box-shadow: 0 0 10px #ffd700;
        animation-delay: 0.9s;
    }

    .light:nth-child(5) {
        background: #ff6b6b;
        box-shadow: 0 0 10px #ff6b6b;
        animation-delay: 1.2s;
    }

    @keyframes twinkle {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.3;
            transform: scale(0.8);
        }
    }

    /* Texto del botón */
    .button-text {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        text-shadow: 
            0 3px 6px rgba(0, 0, 0, 0.6),
            0 0 15px rgba(255, 255, 255, 0.3);
    }

    .icon {
        font-size: 24px;
        animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    /* Onda de energía de fondo */
    .wave {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 200%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent 0%, 
            rgba(255, 255, 255, 0.05) 25%, 
            rgba(255, 255, 255, 0.1) 50%, 
            rgba(255, 255, 255, 0.05) 75%, 
            transparent 100%);
        animation: wave 4s linear infinite;
    }

    @keyframes wave {
        0% {
            transform: translateX(-50%);
        }
        100% {
            transform: translateX(0);
        }
    }

    /* Animación al hacer clic */
    .christmas-button:active {
        animation: none;
        transform: scale(0.95);
        box-shadow: 
            0 0 20px rgba(34, 197, 94, 0.3),
            0 4px 15px rgba(0, 0, 0, 0.4),
            inset 0 2px 0 rgba(255, 255, 255, 0.2),
            inset 0 -2px 4px rgba(0, 0, 0, 0.3);
    }

    /* Explosión de confeti al tocar */
    .christmas-button::after {
        content: '🎉';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        font-size: 40px;
        opacity: 0;
        pointer-events: none;
        z-index: 10;
    }

    .christmas-button:active::after {
        animation: confettiBurst 0.7s ease-out;
    }

    @keyframes confettiBurst {
        0% {
            transform: translate(-50%, -50%) scale(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(3) rotate(360deg);
            opacity: 0;
        }
    }

    /* Ribete dorado superior */
    .golden-edge {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #ffd700, transparent);
        animation: shimmer 2s ease-in-out infinite;
    }

    @keyframes shimmer {
        0%, 100% {
            opacity: 0.5;
        }
        50% {
            opacity: 1;
        }
    }
</style>
@endpush
<div class="button-container">
    <button class="christmas-button" onclick="openModal()">
        <div class="golden-edge"></div>
        <div class="wave"></div>
        <div class="lights">
            <div class="light"></div>
            <div class="light"></div>
            <div class="light"></div>
            <div class="light"></div>
            <div class="light"></div>
        </div>
        <div class="snow-container">
            <div class="snowflake">❄</div>
            <div class="snowflake">❅</div>
            <div class="snowflake">❄</div>
            <div class="snowflake">❆</div>
            <div class="snowflake">❄</div>
            <div class="snowflake">❅</div>
            <div class="snowflake">❄</div>
            <div class="snowflake">❆</div>
            <div class="snowflake">❄</div>
            <div class="snowflake">❅</div>
            <div class="snowflake">❄</div>
            <div class="snowflake">❆</div>
        </div>
        <span class="button-text">
            <span>Ver participantes</span>
        </span>
    </button>
</div>
@push('js')
<script>
    // Función para reiniciar animaciones
    function restartAnimations() {
        const snowflakes = document.querySelectorAll('.snowflake');
        snowflakes.forEach((flake, index) => {
            flake.style.animation = 'none';
            // Trigger reflow
            flake.offsetHeight;
            flake.style.animation = null;
        });
    }

    // Prevenir el doble tap zoom en iOS
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = Date.now();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Función openModal mejorada
    function openModal() {
        console.log('Botón navideño clickeado!');
        
        // Reiniciar animaciones para asegurar que funcionen
        setTimeout(() => {
            restartAnimations();
        }, 100);

        // Aquí agregar tu lógica de modal
        // Por ejemplo: mostrar un modal o redirigir
        alert('🎅 ¡Feliz Navidad! 🎄\n\nLas animaciones de nieve deberían estar funcionando ahora.');
    }

    // Reiniciar animaciones cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            restartAnimations();
        }, 500);
    });
</script>
@endpush