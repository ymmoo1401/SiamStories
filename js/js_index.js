
"use strict";

// แบนเนอร์สไลด์อัตโนมัติ
document.addEventListener("DOMContentLoaded", () => {
    const hero = document.getElementById("home-hero");
    if (!hero) return;

    const track = hero.querySelector(".banner-track");
    const dotsContainer = hero.querySelector(".banner-dots");

    if (!track || !dotsContainer) {
        console.error("ไม่พบ banner-track หรือ banner-dots");
        return;
    }

    // ภาพแบนเนอร์สำหรับหน้าแรกและหน้าปฏิทิน
    const customImages = [
        "https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1600&q=85",
        "Img/isan-img/อีสานพื้นหลัง.png",
        "Img/central-img/กลางพื้นหลัง.jpg",
        "Img/east-img/เหนือพื้นหลัง.jpg",
        "Img/south-img/ใต้พื้นหลัง.jpg"
    ];

    function loadImages() {
        return customImages.map(path => `url("${path}")`);
    }

    const images = loadImages();

    if (images.length === 0) {
        console.warn(
            "ไม่พบภาพสำหรับแบนเนอร์ กรุณากำหนดภาพใน CSS หรือ customImages"
        );
        return;
    }

    // เตรียมพื้นที่สไลด์
    hero.style.position = "relative";
    hero.style.overflow = "hidden";
    hero.style.isolation = "isolate";

    // ทำให้ภาพจาก CSS เดิมไม่ทับภาพที่ JavaScript สร้าง
    if (customImages.length > 0) {
        hero.style.backgroundImage = "none";
    }

    Object.assign(track.style, {
        display: "flex",
        position: "absolute",
        inset: "0",
        width: "100%",
        height: "100%",
        zIndex: "0",
        transition: "transform 900ms ease-in-out",
        willChange: "transform"
    });

    track.innerHTML = "";
    dotsContainer.innerHTML = "";

    images.forEach((background, index) => {
        const slide = document.createElement("div");

        slide.className = "banner-slide";

        Object.assign(slide.style, {
            flex: "0 0 100%",
            width: "100%",
            height: "100%",
            backgroundImage: background,
            backgroundPosition: "center",
            backgroundSize: "cover",
            backgroundRepeat: "no-repeat"
        });

        track.appendChild(slide);

        if (images.length > 1) {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "banner-dot";
            dot.setAttribute("aria-label", `ไปยังสไลด์ที่ ${index + 1}`);
            dot.addEventListener("click", () => {
                current = index;
                direction = 1;
                showSlide();
                startAutoSlide();
            });
            dotsContainer.appendChild(dot);
        }
    });

    // รักษาลำดับชั้นของข้อความ
    const shade = hero.querySelector(".banner-shade");
    const content = hero.querySelector(".banner-content");

    if (shade) {
        shade.style.position = "absolute";
        shade.style.inset = "0";
        shade.style.zIndex = "1";
        shade.style.pointerEvents = "none";
    }

    if (content) {
        content.style.position = "relative";
        content.style.zIndex = "2";
    }

    dotsContainer.style.display = images.length > 1 ? "flex" : "none";
    dotsContainer.style.pointerEvents = "auto";

    const dots = dotsContainer.querySelectorAll(".banner-dot");

    let current = 0;
    let direction = 1;
    let timer = null;

    function showSlide() {
        track.style.transform = `translate3d(-${current * 100}%, 0, 0)`;

        dots.forEach((dot, index) => {
            const active = index === current;
            dot.classList.toggle("active", active);
            dot.setAttribute("aria-selected", active ? "true" : "false");
        });
    }

    function nextSlide() {
        if (current >= images.length - 1) {
            direction = -1;
        } else if (current <= 0) {
            direction = 1;
        }

        current += direction;
        showSlide();
    }

    function startAutoSlide() {
        clearInterval(timer);
        timer = null;

        if (images.length < 2 || document.hidden) return;

        timer = setInterval(nextSlide, 4500);
    }

    hero.addEventListener("mouseenter", () => {
        clearInterval(timer);
        timer = null;
    });

    hero.addEventListener("mouseleave", startAutoSlide);

    document.addEventListener("visibilitychange", startAutoSlide);

    window.addEventListener("pagehide", () => {
        clearInterval(timer);
    });

    showSlide();
    startAutoSlide();

    console.log(
        `Banner slider พร้อมทำงาน: ${images.length} ภาพ`
    );
});
