"use strict";

// Page Transitions (Fade-in & Fade-out) for SIAM STORIES
document.addEventListener("DOMContentLoaded", () => {
    // Smooth fade-out before navigating to internal pages
    document.addEventListener("click", (e) => {
        const link = e.target.closest("a");
        if (!link) return;

        const href = link.getAttribute("href");
        if (
            !href ||
            href.startsWith("#") ||
            href.startsWith("javascript:") ||
            href.startsWith("tel:") ||
            href.startsWith("mailto:") ||
            link.target === "_blank" ||
            e.ctrlKey ||
            e.metaKey ||
            e.shiftKey
        ) {
            return;
        }

        // Only transition for local HTML pages
        const isInternal = !href.includes("://") || link.origin === window.location.origin;
        if (isInternal) {
            e.preventDefault();
            document.body.classList.add("page-fading-out");
            setTimeout(() => {
                window.location.href = href;
            }, 230);
        }
    });

    // Handle bfcache on browser back/forward buttons
    window.addEventListener("pageshow", () => {
        document.body.classList.remove("page-fading-out");
    });

    // ==========================================
    // Back to Top Floating Button
    // ==========================================
    initBackToTop();

    // ==========================================
    // Sticky / Floating Navigation Bar
    // ==========================================
    initStickyNav();

    // ==========================================
    // Mobile Navigation (Hamburger Menu)
    // ==========================================
    initMobileNav();
});

function initBackToTop() {
    let btn = document.getElementById("backToTop");
    if (!btn) {
        btn = document.createElement("button");
        btn.id = "backToTop";
        btn.className = "back-to-top";
        btn.type = "button";
        btn.setAttribute("aria-label", "กลับสู่ด้านบน");
        btn.setAttribute("title", "กลับสู่ด้านบน");
        btn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="back-to-top-icon">
                <path d="M6 15l6-6 6 6"/>
            </svg>
        `;
        document.body.appendChild(btn);
    }

    let ticking = false;
    const updateVisibility = () => {
        if (window.scrollY > 280) {
            btn.classList.add("visible");
        } else {
            btn.classList.remove("visible");
        }
        ticking = false;
    };

    window.addEventListener("scroll", () => {
        if (!ticking) {
            window.requestAnimationFrame(updateVisibility);
            ticking = true;
        }
    }, { passive: true });

    // Initial check
    updateVisibility();

    btn.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
}

// ==========================================
// Floating / Sticky Navigation Bar
// ==========================================
function initStickyNav() {
    const nav = document.querySelector(".nav");
    if (!nav) return;

    let ticking = false;
    const updateScrolled = () => {
        const scrollY = window.scrollY || window.pageYOffset;
        if (scrollY > 15) {
            nav.classList.add("is-scrolled");
        } else {
            nav.classList.remove("is-scrolled");
        }
        ticking = false;
    };

    window.addEventListener("scroll", () => {
        if (!ticking) {
            window.requestAnimationFrame(updateScrolled);
            ticking = true;
        }
    }, { passive: true });

    // Initial check
    updateScrolled();
}

// ==========================================
// Mobile Navigation (Hamburger Menu Toggle)
// ==========================================
function initMobileNav() {
    const navToggle = document.getElementById("navToggle");
    const navMenu = document.getElementById("mainMenu") || document.querySelector(".nav .menu");
    const nav = document.querySelector(".nav");
    if (!navToggle || !navMenu) return;

    // Prevent attaching multiple times
    if (navToggle.dataset.initialized) return;
    navToggle.dataset.initialized = "true";

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

    document.addEventListener("click", (e) => {
        if (navToggle.classList.contains("active") && nav && !nav.contains(e.target)) {
            toggleMenu(false);
        }
    });

    navMenu.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => toggleMenu(false));
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && navToggle.classList.contains("active")) {
            toggleMenu(false);
            navToggle.focus();
        }
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 880 && navToggle.classList.contains("active")) {
            toggleMenu(false);
        }
    });
}



