"use strict";

// SIAM STORIES - Core Interactions & Modern Thai Heritage Homepage
document.addEventListener("DOMContentLoaded", () => {

    // =========================================
    // 1. Mobile Navigation (Hamburger Menu)
    // =========================================
    const navToggle = document.getElementById("navToggle");
    const navMenu = document.getElementById("mainMenu") || document.querySelector(".nav .menu");
    const nav = document.querySelector(".nav");

    if (navToggle && navMenu) {
        const toggleMenu = (open) => {
            const isCurrentlyOpen = navToggle.classList.contains("active");
            const shouldOpen = typeof open === "boolean" ? open : !isCurrentlyOpen;

            navToggle.classList.toggle("active", shouldOpen);
            navMenu.classList.toggle("active", shouldOpen);
            navToggle.setAttribute("aria-expanded", String(shouldOpen));
        };

        navToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        // Close when clicking outside nav or on menu links
        document.addEventListener("click", (e) => {
            if (navToggle.classList.contains("active") && nav && !nav.contains(e.target)) {
                toggleMenu(false);
            }
        });

        navMenu.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => {
                toggleMenu(false);
            });
        });

        // Close menu on ESC key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && navToggle.classList.contains("active")) {
                toggleMenu(false);
                navToggle.focus();
            }
        });

        // Reset state on larger screens
        window.addEventListener("resize", () => {
            if (window.innerWidth > 880 && navToggle.classList.contains("active")) {
                toggleMenu(false);
            }
        });
    }


    // =========================================
    // 2. Modern Hero Image Slider (Homepage)
    // =========================================
    const heroSlider = document.getElementById("homeHero");

    if (heroSlider) {
        const slides = Array.from(heroSlider.querySelectorAll(".hero-slide"));
        const prevBtn = document.getElementById("heroPrev");
        const nextBtn = document.getElementById("heroNext");
        const dotsContainer = document.getElementById("heroDots");

        if (slides.length > 0) {
            let currentIndex = 0;
            let slideTimer = null;
            const SLIDE_DURATION = 5500; // 5.5 seconds

            // Check if user prefers reduced motion
            const prefersReducedMotion = window.matchMedia &&
                window.matchMedia("(prefers-reduced-motion: reduce)").matches;

            // Generate Pagination Dots
            if (dotsContainer) {
                dotsContainer.innerHTML = "";
                // ป้องกันไม่ให้การแตะที่จุดนำทางไปชนกับ gesture swipe ของสไลเดอร์
                dotsContainer.addEventListener("touchstart", (e) => e.stopPropagation(), { passive: true });
                dotsContainer.addEventListener("touchend", (e) => e.stopPropagation(), { passive: true });

                slides.forEach((slide, index) => {
                    const dot = document.createElement("button");
                    dot.type = "button";
                    dot.className = `hero-dot ${index === 0 ? "active" : ""}`;
                    dot.setAttribute("role", "tab");
                    dot.setAttribute("aria-selected", index === 0 ? "true" : "false");
                    const label = slide.getAttribute("aria-label") || `สไลด์ ${index + 1}`;
                    dot.setAttribute("aria-label", label);
                    dot.dataset.index = String(index);

                    const selectThisSlide = (e) => {
                        e.stopPropagation();
                        goToSlide(index);
                        restartAutoPlay();
                    };

                    dot.addEventListener("click", selectThisSlide);

                    dotsContainer.appendChild(dot);
                });
            }

            const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll(".hero-dot")) : [];

            const goToSlide = (newIndex) => {
                if (newIndex < 0) {
                    currentIndex = slides.length - 1;
                } else if (newIndex >= slides.length) {
                    currentIndex = 0;
                } else {
                    currentIndex = newIndex;
                }

                // Update slides
                slides.forEach((slide, idx) => {
                    const isActive = idx === currentIndex;
                    slide.classList.toggle("active", isActive);
                    if (isActive) {
                        slide.removeAttribute("aria-hidden");
                    } else {
                        slide.setAttribute("aria-hidden", "true");
                    }
                });

                // Update dots
                dots.forEach((dot, idx) => {
                    const isActive = idx === currentIndex;
                    dot.classList.toggle("active", isActive);
                    dot.setAttribute("aria-selected", String(isActive));
                    dot.tabIndex = isActive ? 0 : -1;
                });
            };

            const nextSlide = () => goToSlide(currentIndex + 1);
            const prevSlide = () => goToSlide(currentIndex - 1);

            if (nextBtn) {
                nextBtn.addEventListener("click", () => {
                    nextSlide();
                    restartAutoPlay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener("click", () => {
                    prevSlide();
                    restartAutoPlay();
                });
            }

            // Keyboard navigation when focused in hero
            heroSlider.addEventListener("keydown", (e) => {
                if (e.key === "ArrowLeft") {
                    e.preventDefault();
                    prevSlide();
                    restartAutoPlay();
                } else if (e.key === "ArrowRight") {
                    e.preventDefault();
                    nextSlide();
                    restartAutoPlay();
                }
            });

            // Touch Swipe Support
            let touchStartX = 0;
            let touchStartY = 0;
            let touchEndX = 0;
            let touchEndY = 0;

            heroSlider.addEventListener("touchstart", (e) => {
                if (e.changedTouches && e.changedTouches[0]) {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartY = e.changedTouches[0].screenY;
                }
                pauseAutoPlay();
            }, { passive: true });

            heroSlider.addEventListener("touchend", (e) => {
                if (e.changedTouches && e.changedTouches[0]) {
                    touchEndX = e.changedTouches[0].screenX;
                    touchEndY = e.changedTouches[0].screenY;
                    handleSwipe();
                }
                startAutoPlay();
            }, { passive: true });

            const handleSwipe = () => {
                const diffX = touchEndX - touchStartX;
                const diffY = touchEndY - touchStartY;
                const threshold = 45;

                // Make sure horizontal swipe was intentional
                if (Math.abs(diffX) > threshold && Math.abs(diffX) > Math.abs(diffY)) {
                    if (diffX < 0) {
                        nextSlide();
                    } else {
                        prevSlide();
                    }
                }
            };

            // Auto-Play Engine
            const startAutoPlay = () => {
                if (prefersReducedMotion || slides.length <= 1 || document.hidden) return;
                if (!slideTimer) {
                    slideTimer = setInterval(nextSlide, SLIDE_DURATION);
                }
            };

            const pauseAutoPlay = () => {
                if (slideTimer) {
                    clearInterval(slideTimer);
                    slideTimer = null;
                }
            };

            const restartAutoPlay = () => {
                pauseAutoPlay();
                startAutoPlay();
            };

            // Pause on hover & focus
            heroSlider.addEventListener("mouseenter", pauseAutoPlay);
            heroSlider.addEventListener("mouseleave", startAutoPlay);
            heroSlider.addEventListener("focusin", pauseAutoPlay);
            heroSlider.addEventListener("focusout", startAutoPlay);

            // Visibility changes
            document.addEventListener("visibilitychange", () => {
                if (document.hidden) {
                    pauseAutoPlay();
                } else {
                    startAutoPlay();
                }
            });

            // Start auto play
            startAutoPlay();
        }
    }


    // =========================================
    // 3. ระบบ Active Tabs
    // =========================================
    const tabs = document.querySelectorAll(".tab");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(item => {
                item.classList.remove("active");
                item.setAttribute("aria-selected", "false");
            });

            tab.classList.add("active");
            tab.setAttribute("aria-selected", "true");
        });
    });


    // =========================================
    // 4. ระบบ Active Navigation
    // =========================================
    const navLinks = document.querySelectorAll(".menu a");
    const pathname = window.location.pathname;
    const currentPage = pathname.split("/").pop() || "index.html";

    navLinks.forEach(link => {
        const href = link.getAttribute("href");
        if (!href) return;

        const linkPage = href.split("/").pop().split("?")[0].split("#")[0];
        const isActive = linkPage === currentPage;

        link.classList.toggle("active", isActive);

        if (isActive) {
            link.setAttribute("aria-current", "page");
        } else {
            link.removeAttribute("aria-current");
        }
    });


    // =========================================
    // 5. เอฟเฟกต์ตอนเลื่อนหน้าเว็บ (Reveal Animation)
    // =========================================
    const revealElements = document.querySelectorAll(".region-card");

    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1 }
        );

        revealElements.forEach(element => {
            observer.observe(element);
        });
    } else {
        revealElements.forEach(element => {
            element.classList.add("is-visible");
        });
    }


    // =========================================
    // 6. ตรวจสอบการทำงาน
    // =========================================
    console.log("SIAM STORIES JavaScript initialized successfully");
    console.log("Current page:", currentPage);

});
