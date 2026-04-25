(function($) {
    "use strict";
  
    const $documentOn = $(document);
    const $windowOn = $(window);
  
    $documentOn.ready(function() {

        /* ================================
           Mobile Menu Js Start
        ================================ */
        $('#mobile-menu').meanmenu({
            meanMenuContainer: '.mobile-menu',
            meanScreenWidth: "1199",
            meanExpand: ['<i class="far fa-plus"></i>'],
        });
    
        /* ================================
           Sidebar Toggle Js Start
        ================================ */
        $(".offcanvas__close,.offcanvas__overlay").on("click", function() {
            $(".offcanvas__info").removeClass("info-open");
            $(".offcanvas__overlay").removeClass("overlay-open");
        });
        $(".sidebar__toggle").on("click", function() {
            $(".offcanvas__info").addClass("info-open");
            $(".offcanvas__overlay").addClass("overlay-open");
        });
      
        /* ================================
           Body Overlay Js Start
        ================================ */
        $(".body-overlay").on("click", function() {
            $(".offcanvas__area").removeClass("offcanvas-opened");
            $(".df-search-area").removeClass("opened");
            $(".body-overlay").removeClass("opened");
        });
  
        /* ================================
           Sticky Header Js Start
        ================================ */
        $windowOn.on("scroll", function() {
            if ($(this).scrollTop() > 250) {
                $("#header-sticky").addClass("sticky");
            } else {
                $("#header-sticky").removeClass("sticky");
            }
        });      
      
        /* ================================
           Video & Image Popup Js Start
        ================================ */
        $(".img-popup").magnificPopup({
            type: "image",
            gallery: {
                enabled: true,
            },
        });

        $(".video-popup").magnificPopup({
            type: "iframe",
            callbacks: {},
        });
  
        /* ================================
           Counterup Js Start
        ================================ */
        $(".count").counterUp({
            delay: 15,
            time: 4000,
        });
  
        /* ================================
           Wow Animation Js Start
        ================================ */
        new WOW().init();
  
        /* ================================
           Nice Select Js Start
        ================================ */
        if ($('.single-select').length) {
            $('.single-select').niceSelect();
        }

        //>> Hero-1 Slider Start <<//
        const sliderActive2 = ".hero-slider";
        const sliderInit2 = new Swiper(sliderActive2, {
            loop: true,
            slidesPerView: 1,
            effect: "fade",
            speed: 3000,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".gt-product-dot",
                clickable: true,
                renderBullet: function(index, className) {
                    const dotContent = document.querySelectorAll(".gt-product-dot .dot-content");
                    return `
                        <span class="${className}">
                            ${dotContent[index]?.outerHTML || ""}
                        </span>
                    `;
                },
            },
        });

        function animated_swiper(selector, init) {
            const animated = function animated() {
                $(selector + " [data-animation]").each(function() {
                    let anim = $(this).data("animation");
                    let delay = $(this).data("delay");
                    let duration = $(this).data("duration");
                    $(this)
                        .removeClass("anim" + anim)
                        .addClass(anim + " animated")
                        .css({
                            webkitAnimationDelay: delay,
                            animationDelay: delay,
                            webkitAnimationDuration: duration,
                            animationDuration: duration,
                        })
                        .one("animationend", function() {
                            $(this).removeClass(anim + " animated");
                        });
                });
            };
            animated();
            init.on("slideChange", function() {
                $(sliderActive2 + " [data-animation]").removeClass("animated");
            });
            init.on("slideChange", animated);
        }
        animated_swiper(sliderActive2, sliderInit2);

        /* ================================
           Team Slider Js Start
        ================================ */
        if ($('.team-slider').length > 0) {
            const teamSlider = new Swiper(".team-slider", {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                centeredSlides: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    1399: { slidesPerView: 4.3 },
                    1199: { slidesPerView: 3.2 },
                    991: { slidesPerView: 3 },
                    767: { slidesPerView: 2 },
                    575: { slidesPerView: 1.4 },
                    0: { slidesPerView: 1.2 },
                },
            });
        }

        if ($('.team-slider-3').length > 0) {
            const teamSlider3 = new Swiper(".team-slider-3", {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                centeredSlides: true,
                autoplay: {
                    delay: 2200,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    1199: { slidesPerView: 3.9 },
                    991: { slidesPerView: 3 },
                    767: { slidesPerView: 2 },
                    575: { slidesPerView: 1.5 },
                    0: { slidesPerView: 1.3 },
                },
            });
        }

        if ($('.brand-slider-3').length > 0) {
            const brandSlider3 = new Swiper(".brand-slider-3", {
                spaceBetween: 0,
                speed: 1300,
                loop: true,
                autoplay: {
                    delay: 2200,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    1199: { slidesPerView: 6 },
                    991: { slidesPerView: 4 },
                    767: { slidesPerView: 3 },
                    575: { slidesPerView: 2 },
                    0: { slidesPerView: 1.3 },
                },
            });
        }

        //>> Testimonial Slider Start <<//
        if ($('.tetsimonial-slider').length > 0) {
            const tetsimonialSlider = new Swiper(".tetsimonial-slider", {
                spaceBetween: 30,
                speed: 1500,
                loop: true,
                slidesPerView: 1,
                effect: "cube",
                grabCursor: true,
                cubeEffect: {
                    shadow: true,
                    slideShadows: true,
                    shadowOffset: 20,
                    shadowScale: 0.94,
                },
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev",
                    prevEl: ".array-next",
                },
            });
        }

        if ($('.testimonial-slider-3').length > 0) {
            const testimonialSlider3 = new Swiper(".testimonial-slider-3", {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                autoplay: {
                    delay: 2200,
                    disableOnInteraction: false,
                },
            });
        }

        /* ================================
           Match Result Slider Js Start
        ================================ */
        if ($('.match-result-slider').length > 0) {
            const matchResultSlider = new Swiper(".match-result-slider", {
                spaceBetween: 0,
                speed: 1300,
                loop: true,
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    1399: { slidesPerView: 6 },
                    1199: { slidesPerView: 4 },
                    991: { slidesPerView: 3, spaceBetween: 20 },
                    767: { slidesPerView: 3, spaceBetween: 20 },
                    575: { slidesPerView: 2, spaceBetween: 20 },
                    0: { slidesPerView: 1.3, spaceBetween: 20 },
                },
            });
        }

        if ($('.upcoming-match-slider-3').length > 0) {
            const upcomingMatchSlider3 = new Swiper(".upcoming-match-slider-3", {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                centeredSlides: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    1699: { slidesPerView: 3.6 },
                    1399: { slidesPerView: 2.9 },
                    1199: { slidesPerView: 2.4 },
                    991: { slidesPerView: 2.2 },
                    767: { slidesPerView: 1.4 },
                    575: { slidesPerView: 1.2 },
                    0: { slidesPerView: 1 },
                },
            });
        }
      
        /* ================================
           Ticket Box Slider Js Start
        ================================ */
        if ($('.ticket-box-slider').length > 0) {
            const ticketBoxSlider = new Swiper(".ticket-box-slider", {
                spaceBetween: 30,
                speed: 1300,
                loop: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    991: { slidesPerView: 2 },
                    767: { slidesPerView: 1.3 },
                    575: { slidesPerView: 1.1 },
                    0: { slidesPerView: 1 },
                },
            });
        }

        /* ================================
           Upcomming Slider Js Start
        ================================ */
        if ($('.upcomming-match-slider').length > 0) {
            const upcommingMatchSlider = new Swiper(".upcomming-match-slider", {
                direction: "vertical",
                spaceBetween: 24,
                loop: true,
                speed: 800,
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    991: { slidesPerView: 2 },
                    767: { slidesPerView: 2 },
                    575: { slidesPerView: 1 },
                    0: { slidesPerView: 1 },
                },
            });
        }

        /* ================================
           Footer Instagram Slider Js Start
        ================================ */
        if ($('.footer-instagram-slider').length > 0) {
            const footerInstagramSlider = new Swiper(".footer-instagram-slider", {
                spaceBetween: 16,
                speed: 1300,
                loop: true,
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".array-prev2",
                    prevEl: ".array-next2",
                },
                breakpoints: {
                    1199: { slidesPerView: 6 },
                    991: { slidesPerView: 4 },
                    767: { slidesPerView: 3 },
                    575: { slidesPerView: 2 },
                    0: { slidesPerView: 1.2 },
                },
            });
        }

        /* ================================
           Parallaxie Js Start
        ================================ */
        if ($('.parallaxie').length && $(window).width() > 991) {
            if ($(window).width() > 768) {
                $('.parallaxie').parallaxie({
                    speed: 0.55,
                    offset: 0,
                });
            }
        }

        // quantity increment and decrement
        const quantityIncrement = document.querySelectorAll(".quantityIncrement");
        const quantityDecrement = document.querySelectorAll(".quantityDecrement");
        if (quantityIncrement && quantityDecrement) {
            quantityIncrement.forEach((increment) => {
                increment.addEventListener("click", function() {
                    const value = parseInt(
                        increment.parentElement.querySelector("input").value,
                        10
                    );
                    increment.parentElement.querySelector("input").value = value + 1;
                });
            });

            quantityDecrement.forEach((decrement) => {
                decrement.addEventListener("click", function() {
                    const value = parseInt(
                        decrement.parentElement.querySelector("input").value,
                        10
                    );
                    if (value > 1) {
                        decrement.parentElement.querySelector("input").value = value - 1;
                    }
                });
            });
        }

        /* ================================
           Section Title Animation Js Start
        ================================ */
        if ($(".tv_hero_title").length) {
            gsap.registerPlugin(SplitText);

            $(".tv_hero_title").each(function() {
                var $el = $(this);
                var split = new SplitText($el, {
                    type: "lines,words,chars",
                    linesClass: "split-line"
                });

                gsap.set($el, { perspective: 400 });

                if ($el.hasClass("hero_title_1")) {
                    gsap.set(split.chars, { x: 100, opacity: 0 });
                }
                if ($el.hasClass("hero_title_2")) {
                    gsap.set(split.chars, { y: 100, opacity: 0 });
                }
                if ($el.hasClass("hero_title_3")) {
                    gsap.set(split.chars, {
                        y: 100,
                        scaleY: 0,
                        opacity: 0,
                        rotationX: 15
                    });
                }

                gsap.to(split.chars, {
                    scrollTrigger: {
                        trigger: $el,
                        start: "top 90%",
                        toggleActions: "play reverse play reverse",
                        markers: false
                    },
                    x: 0,
                    y: 0,
                    scaleX: 1,
                    scaleY: 1,
                    opacity: 1,
                    duration: 1,
                    stagger: 0.05,
                    rotationX: 15,
                    delay: 0.1,
                    ease: "power3.inOut"
                });
            });
        }

        /* ================================
           Image Move scale Js Start
        ================================ */
        const $section = $('.hero-section1');
        const $target = $('.tilt_scale');

        if ($section.length && $target.length) {
            let requestId;

            $section.on('mousemove', function(e) {
                if (requestId) {
                    cancelAnimationFrame(requestId);
                }

                requestId = requestAnimationFrame(() => {
                    const offset = $section.offset();
                    const width = $section.outerWidth();
                    const height = $section.outerHeight();

                    const x = e.pageX - offset.left;
                    const y = e.pageY - offset.top;

                    const rotateY = ((x / width) - 0.5) * 20;
                    const rotateX = ((y / height) - 0.5) * -20;

                    $target.css({
                        transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`
                    });
                });
            });

            $section.on('mouseleave', function() {
                if (requestId) {
                    cancelAnimationFrame(requestId);
                }
                $target.css({
                    transform: 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)'
                });
            });
        }

        if (window.matchMedia("(min-width: 1200px)").matches) {
            document.querySelectorAll(".tv-desti-content").forEach((section) => {
                let items = section.querySelectorAll(".tv-desti-item");

                gsap.timeline({
                    scrollTrigger: {
                        trigger: section,
                        start: "top 70%",
                        toggleActions: "play reverse play reverse",
                        markers: false,
                    },
                })
                .from(items, {
                    xPercent: 70,
                    opacity: 0,
                    ease: "back.out(2.5)",
                    duration: 1,
                    stagger: -0.2,
                });
            });
        }

        /* ================================
           Mouse Cursor Animation Js Start
        ================================ */
        if ($(".mouseCursor").length > 0) {
            function itCursor() {
                var myCursor = jQuery(".mouseCursor");
                if (myCursor.length) {
                    if ($("body")) {
                        const e = document.querySelector(".cursor-inner"),
                            t = document.querySelector(".cursor-outer");
                        let n, i = 0, o = !1;
                        window.onmousemove = function(s) {
                            if (!o) {
                                t.style.transform = "translate(" + s.clientX + "px, " + s.clientY + "px)";
                            }
                            e.style.transform = "translate(" + s.clientX + "px, " + s.clientY + "px)";
                            n = s.clientY;
                            i = s.clientX;
                        };
                        $("body").on("mouseenter", "button, a, .cursor-pointer", function() {
                            e.classList.add("cursor-hover");
                            t.classList.add("cursor-hover");
                        });
                        $("body").on("mouseleave", "button, a, .cursor-pointer", function() {
                            if (!($(this).is("a", "button") && $(this).closest(".cursor-pointer").length)) {
                                e.classList.remove("cursor-hover");
                                t.classList.remove("cursor-hover");
                            }
                        });
                        e.style.visibility = "visible";
                        t.style.visibility = "visible";
                    }
                }
            }
            itCursor();
        }

        /* ================================
           Back To Top Button Js Start
        ================================ */
        $windowOn.on('scroll', function() {
            var windowScrollTop = $(this).scrollTop();
            var windowHeight = $(window).height();
            var documentHeight = $(document).height();

            if (windowScrollTop + windowHeight >= documentHeight - 10) {
                $("#back-top").addClass("show");
            } else {
                $("#back-top").removeClass("show");
            }
        });

        $documentOn.on('click', '#back-top', function() {
            $('html, body').animate({ scrollTop: 0 }, 800);
            return false;
        });

    }); // End Document Ready Function

    //>> Preloader Start <<//
    function loader() {
        $(window).on('load', function() {
            $(".preloader").addClass('loaded');                    
            $(".preloader").delay(600).fadeOut();                       
        });
    }
    loader();
  
})(jQuery); // End jQuery
