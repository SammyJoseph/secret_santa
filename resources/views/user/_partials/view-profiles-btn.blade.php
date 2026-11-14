@push('css')
<style>
    .button-container {
        position: relative;
        z-index: 1;
        display: inline-flex;
        justify-content: center;
    }

    .christmas-button {
        position: relative;
        padding: 12px 28px;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(145deg, #16a34a 0%, #15803d 50%, #14532d 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        overflow: hidden;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 
            0 0 18px rgba(34, 197, 94, 0.35),
            0 5px 16px rgba(0, 0, 0, 0.4),
            inset 0 2px 0 rgba(255, 255, 255, 0.25),
            inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
        animation: glow 3s ease-in-out infinite;
    }

    @media (min-width: 640px) {
        .christmas-button {
            padding: 13px 34px;
            font-size: 16px;
            min-height: 50px;
            border-radius: 14px;
        }
    }

    @media (hover: hover) {
        .christmas-button:hover {
            transform: translateY(-2px) translateZ(0);
            box-shadow: 
                0 0 28px rgba(34, 197, 94, 0.55),
                0 7px 20px rgba(0, 0, 0, 0.45),
                inset 0 2px 0 rgba(255, 255, 255, 0.3),
                inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
    }

    /* Efecto de resplandor pulsante */
    @keyframes glow {
        0%, 100% {
            box-shadow: 
                0 0 18px rgba(34, 197, 94, 0.35),
                0 5px 16px rgba(0, 0, 0, 0.4),
                inset 0 2px 0 rgba(255, 255, 255, 0.25),
                inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
        50% {
            box-shadow: 
                0 0 30px rgba(34, 197, 94, 0.55),
                0 5px 16px rgba(0, 0, 0, 0.4),
                inset 0 2px 0 rgba(255, 255, 255, 0.25),
                inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
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

    /* Copos de nieve con caracteres */
    .snowflake {
        position: absolute;
        top: -15px;
        color: #ffffff !important;
        font-variant-emoji: text;
        font-family: sans-serif;
        font-weight: normal;
        z-index: 5;
        font-size: 12px;
        animation: fall linear infinite;
        text-shadow: 0 0 4px rgba(255, 255, 255, 0.8);
        user-select: none;
        will-change: transform;
    }

    @keyframes fall {
        0% {
            transform: translateY(0) translateX(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(75px) translateX(25px) rotate(360deg);
            opacity: 0;
        }
    }

    .snowflake:nth-child(1) { left: 5%; animation-duration: 4s; animation-delay: 0s; font-size: 13px; }
    .snowflake:nth-child(2) { left: 20%; animation-duration: 4.5s; animation-delay: 0.8s; font-size: 10px; }
    .snowflake:nth-child(3) { left: 35%; animation-duration: 4.2s; animation-delay: 1.5s; font-size: 12px; }
    .snowflake:nth-child(4) { left: 50%; animation-duration: 4.8s; animation-delay: 0.3s; font-size: 14px; }
    .snowflake:nth-child(5) { left: 65%; animation-duration: 4.3s; animation-delay: 2s; font-size: 11px; }
    .snowflake:nth-child(6) { left: 80%; animation-duration: 4.6s; animation-delay: 1s; font-size: 13px; }
    .snowflake:nth-child(7) { left: 15%; animation-duration: 4.4s; animation-delay: 2.5s; font-size: 14px; }
    .snowflake:nth-child(8) { left: 45%; animation-duration: 4.7s; animation-delay: 1.8s; font-size: 9px; }
    .snowflake:nth-child(9) { left: 75%; animation-duration: 4.9s; animation-delay: 0.5s; font-size: 13px; }
    .snowflake:nth-child(10) { left: 90%; animation-duration: 4.1s; animation-delay: 3s; font-size: 11px; }
    .snowflake:nth-child(11) { left: 25%; animation-duration: 5s; animation-delay: 2.2s; font-size: 10px; }
    .snowflake:nth-child(12) { left: 60%; animation-duration: 4.4s; animation-delay: 0.7s; font-size: 12px; }

    /* Luces navideñas brillantes */
    .lights {
        position: absolute;
        top: 3px;
        left: 0;
        width: 100%;
        height: 6px;
        display: flex;
        justify-content: space-around;
        padding: 0 16px;
        pointer-events: none;
    }

    .light {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        animation: twinkle 1.5s ease-in-out infinite;
    }

    .light:nth-child(1) {
        background: #ffd700;
        box-shadow: 0 0 6px #ffd700;
        animation-delay: 0s;
    }

    .light:nth-child(2) {
        background: #ff6b6b;
        box-shadow: 0 0 6px #ff6b6b;
        animation-delay: 0.3s;
    }

    .light:nth-child(3) {
        background: #4ecdc4;
        box-shadow: 0 0 6px #4ecdc4;
        animation-delay: 0.6s;
    }

    .light:nth-child(4) {
        background: #ffd700;
        box-shadow: 0 0 6px #ffd700;
        animation-delay: 0.9s;
    }

    .light:nth-child(5) {
        background: #ff6b6b;
        box-shadow: 0 0 6px #ff6b6b;
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
        gap: 8px;
        text-shadow: 
            0 2px 4px rgba(0, 0, 0, 0.6),
            0 0 10px rgba(255, 255, 255, 0.25);
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
        transform: scale(0.96) translateZ(0);
        box-shadow: 
            0 0 15px rgba(34, 197, 94, 0.3),
            0 3px 10px rgba(0, 0, 0, 0.4),
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
        font-size: 28px;
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
        height: 2px;
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
    <button type="button" class="christmas-button" onclick="openModal()">
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
        snowflakes.forEach((flake) => {
            flake.style.animation = 'none';
            flake.offsetHeight;
            flake.style.animation = '';
        });
    }

    // Reiniciar animaciones cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(restartAnimations, 500);
    });
</script>
@endpush
