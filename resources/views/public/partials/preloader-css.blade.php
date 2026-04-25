        #rts__preloader {
            --lap-preloader-surface: #ffffff;
            --lap-preloader-fill: #e41b23;
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--lap-preloader-surface);
            z-index: 9999;
            opacity: 1;
            visibility: visible;
            transition: opacity 1s ease, visibility 1s ease;
        }

        #rts__preloader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        #rts__preloader .main-fader {
            position: absolute;
            width: 100%;
            height: 100vh;
            background-color: var(--lap-preloader-surface);
        }

        #rts__preloader .loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--lap-preloader-fill);
        }

        #rts__preloader .loader svg {
            display: block;
            width: min(300px, 72vw);
            height: auto;
            margin: 0 auto;
        }

        #rts__preloader .loader svg path {
            fill: currentColor;
            color: currentColor;
            animation: lap-preloader-pulse 1s infinite;
        }

        #rts__preloader .loader svg path.path-7 {
            animation-delay: -1s;
        }

        #rts__preloader .loader svg path.path-6 {
            animation-delay: -0.875s;
        }

        #rts__preloader .loader svg path.path-5 {
            animation-delay: -0.75s;
        }

        #rts__preloader .loader svg path.path-4 {
            animation-delay: -0.625s;
        }

        #rts__preloader .loader svg path.path-3 {
            animation-delay: -0.5s;
        }

        #rts__preloader .loader svg path.path-2 {
            animation-delay: -0.375s;
        }

        #rts__preloader .loader svg path.path-1 {
            animation-delay: -0.25s;
        }

        #rts__preloader .loader svg path.path-0 {
            animation-delay: -0.125s;
        }

        @keyframes lap-preloader-pulse {
            0% {
                opacity: 0.1;
            }

            30% {
                opacity: 0.8;
            }

            100% {
                opacity: 0.1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #rts__preloader .loader svg path {
                animation: none !important;
                opacity: 1;
            }
        }
