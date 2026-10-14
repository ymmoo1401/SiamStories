

        function initContentSliders() {
            document.querySelectorAll("[data-content-slider]").forEach(slider => {
                if (slider.dataset.sliderInitialized === "true") return;
                slider.dataset.sliderInitialized = "true";

                const track = slider.querySelector(".slider-track");
                const prev = slider.querySelector(".slider-prev");
                const next = slider.querySelector(".slider-next");

                if (!track || !prev || !next) return;

                let moving = false;

                function createDuplicate(element) {
                    const duplicate = element.cloneNode(true);
                    duplicate.removeAttribute("id");
                    duplicate.querySelectorAll("[id]").forEach(item => {
                        item.removeAttribute("id");
                    });
                    duplicate.setAttribute("aria-hidden", "true");
                    duplicate.setAttribute("inert", "");
                    return duplicate;
                }

                async function slide(direction) {
                    if (moving || track.children.length < 2) return;
                    moving = true;

                    const first = track.firstElementChild;
                    const last = track.lastElementChild;
                    const computed = getComputedStyle(track);
                    const gap = parseFloat(computed.gap) || parseFloat(computed.columnGap) || 18;
                    const cardWidth = first.getBoundingClientRect().width;
                    const distance = cardWidth + gap;

                    const reducedMotion = window.matchMedia(
                        "(prefers-reduced-motion: reduce)"
                    ).matches;

                    if (reducedMotion) {
                        if (direction === 1) {
                            track.appendChild(first);
                        } else {
                            track.prepend(last);
                        }
                        moving = false;
                        return;
                    }

                    let animation = null;
                    const targetCard = direction === 1 ? first : last;
                    const duplicate = createDuplicate(targetCard);

                    if (direction === 1) {
                        // เลื่อนไปทางขวา (Next): โคลนการ์ดแรกไปต่อท้าย แล้วเลื่อน track ไปทางซ้าย
                        track.appendChild(duplicate);
                    } else {
                        // เลื่อนไปทางซ้าย (Prev): โคลนการ์ดสุดท้ายมาไว้ข้างหน้า แล้วเลื่อนจาก -distance กลับมาที่ 0
                        track.prepend(duplicate);
                    }

                    try {
                        animation = track.animate(
                            direction === 1
                                ? [
                                    { transform: "translateX(0)" },
                                    { transform: `translateX(-${distance}px)` }
                                ]
                                : [
                                    { transform: `translateX(-${distance}px)` },
                                    { transform: "translateX(0)" }
                                ],
                            {
                                duration: 460,
                                easing: "cubic-bezier(0.25, 1, 0.5, 1)",
                                fill: "both"
                            }
                        );

                        await animation.finished;
                    } catch (error) {
                        // Animation interrupted
                    } finally {
                        if (direction === 1) {
                            track.appendChild(first);
                        } else {
                            track.prepend(last);
                        }
                        duplicate.remove();
                        if (animation) {
                            try {
                                animation.cancel();
                            } catch (e) {}
                        }
                        moving = false;
                    }
                }

                prev.addEventListener("click", (e) => {
                    e.preventDefault();
                    slide(-1);
                });

                next.addEventListener("click", (e) => {
                    e.preventDefault();
                    slide(1);
                });

                // รองรับคีย์บอร์ดซ้าย-ขวา เมื่อเลือกโฟกัสที่ slider
                slider.addEventListener("keydown", (e) => {
                    if (e.key === "ArrowLeft") {
                        e.preventDefault();
                        slide(-1);
                    } else if (e.key === "ArrowRight") {
                        e.preventDefault();
                        slide(1);
                    }
                });

                // รองรับการปัด Swipe บนหน้าจอมือถือ/แท็บเล็ต
                let touchStartX = 0;
                let touchStartY = 0;

                slider.addEventListener("touchstart", (e) => {
                    if (e.touches && e.touches.length === 1) {
                        touchStartX = e.touches[0].clientX;
                        touchStartY = e.touches[0].clientY;
                    }
                }, { passive: true });

                slider.addEventListener("touchend", (e) => {
                    if (e.changedTouches && e.changedTouches.length === 1) {
                        const diffX = touchStartX - e.changedTouches[0].clientX;
                        const diffY = touchStartY - e.changedTouches[0].clientY;
                        if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY) * 1.4) {
                            if (diffX > 0) slide(1);
                            else slide(-1);
                        }
                    }
                }, { passive: true });
            });
        }

        window.initTraditionSliders = initContentSliders;

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initContentSliders);
        } else {
            initContentSliders();
        }
   

        (() => {
            const root = document.querySelector("#food .food-showcase");
            if (!root) return;

            const slides = [...root.querySelectorAll(".food-slide")];
            const choices = [...root.querySelectorAll(".food-choice")];
            const counter = root.querySelector(".food-counter");
            const status = root.querySelector("[data-food-status]");
            const motion = window.matchMedia(
                "(prefers-reduced-motion: reduce)"
            );

            let current = 0;
            let moving = false;
            let details = [];

            function reveal(slide) {
                details.forEach(animation => animation.cancel());
                details = [];

                if (motion.matches || !slide.animate) return;

                const image = slide.querySelector("img");

                details.push(image.animate(
                    [
                        { transform: "scale(1.16)" },
                        { transform: "scale(1)" }
                    ],
                    {
                        duration: 1100,
                        easing: "cubic-bezier(.2,.7,.2,1)"
                    }
                ));

                slide.querySelectorAll(".food-copy > *")
                    .forEach((item, index) => {
                        details.push(item.animate(
                            [
                                {
                                    opacity: 0,
                                    transform: "translateY(20px)"
                                },
                                {
                                    opacity: 1,
                                    transform: "translateY(0)"
                                }
                            ],
                            {
                                duration: 550,
                                delay: 80 + index * 100,
                                easing: "cubic-bezier(.2,.7,.2,1)",
                                fill: "backwards"
                            }
                        ));
                    });
            }

            async function showFood(index, direction = 1) {
                const target = (index + slides.length) % slides.length;
                if (moving || target === current) return;

                moving = true;
                const oldSlide = slides[current];
                const newSlide = slides[target];
                const effects = [];

                try {
                    newSlide.hidden = false;
                    newSlide.inert = true;
                    newSlide.setAttribute("aria-hidden", "true");

                    if (!motion.matches && oldSlide.animate) {
                        const outgoing = oldSlide.animate(
                            [
                                {
                                    transform: "rotateY(0deg) scale(1)",
                                    opacity: 1
                                },
                                {
                                    transform:
                                        `rotateY(${-90 * direction}deg) scale(.94)`,
                                    opacity: 0
                                }
                            ],
                            {
                                duration: 360,
                                easing: "ease-in",
                                fill: "both"
                            }
                        );

                        const incoming = newSlide.animate(
                            [
                                {
                                    transform:
                                        `rotateY(${90 * direction}deg) scale(.94)`,
                                    opacity: 0
                                },
                                {
                                    transform: "rotateY(0deg) scale(1)",
                                    opacity: 1
                                }
                            ],
                            {
                                duration: 520,
                                delay: 270,
                                easing: "cubic-bezier(.16,1,.3,1)",
                                fill: "both"
                            }
                        );

                        effects.push(outgoing, incoming);
                        await outgoing.finished;

                        oldSlide.hidden = true;
                        reveal(newSlide);
                        await incoming.finished;
                    } else {
                        oldSlide.hidden = true;
                        reveal(newSlide);
                    }
                } catch (error) {
                    console.warn("Food transition interrupted:", error);
                } finally {
                    effects.forEach(animation => animation.cancel());
                    current = target;

                    slides.forEach((slide, i) => {
                        const active = i === current;
                        slide.hidden = !active;
                        slide.inert = !active;
                        slide.setAttribute("aria-hidden", String(!active));
                    });

                    choices.forEach((button, i) => {
                        button.setAttribute(
                            "aria-pressed",
                            String(i === current)
                        );
                    });

                    counter.textContent =
                        `${String(current + 1).padStart(2, "0")} / ` +
                        `${String(slides.length).padStart(2, "0")}`;

                    const name = slides[current]
                        .querySelector("h3").textContent.trim();

                    status.textContent =
                        `${name} เมนูที่ ${current + 1} จาก ${slides.length}`;

                    moving = false;
                }
            }

            root.querySelector("[data-food-prev]")
                .addEventListener("click", () => showFood(current - 1, -1));

            root.querySelector("[data-food-next]")
                .addEventListener("click", () => showFood(current + 1, 1));

            choices.forEach((button, index) => {
                button.addEventListener("click", () => {
                    showFood(index, index > current ? 1 : -1);
                });
            });

            root.addEventListener("keydown", event => {
                if (
                    event.key !== "ArrowLeft" &&
                    event.key !== "ArrowRight"
                ) return;

                event.preventDefault();
                const direction = event.key === "ArrowRight" ? 1 : -1;
                showFood(current + direction, direction);
            });

            root.querySelectorAll("img").forEach(img => {
                function showImageError() {
                    img.style.display = "none";
                    img.parentElement.querySelector("figcaption")
                        .textContent = `${img.alt} · ไม่สามารถโหลดรูปภาพได้`;
                }

                img.addEventListener("error", showImageError, { once: true });

                if (img.complete && img.naturalWidth === 0) {
                    showImageError();
                }
            });

            if ("IntersectionObserver" in window) {
                const observer = new IntersectionObserver(entries => {
                    if (entries.some(entry => entry.isIntersecting)) {
                        reveal(slides[current]);
                        observer.disconnect();
                    }
                }, { threshold: .15 });

                observer.observe(root);
            } else {
                reveal(slides[current]);
            }
        })();
  
        (() => {
            const cards = [
                ...document.querySelectorAll(
                    "#dress [data-culture-reveal], " +
                    "#wisdom [data-culture-reveal]"
                )
            ];

            const motion = window.matchMedia(
                "(prefers-reduced-motion: reduce)"
            );

            // แสดงข้อความแทนเมื่อยังไม่ได้ใส่รูปหรือรูปโหลดไม่สำเร็จ
            cards.forEach(card => {
                const img = card.querySelector(".culture-photo img");
                if (!img) return;

                function showFallback() {
                    const figure = img.closest(".culture-photo");
                    figure.classList.add("image-missing");
                    figure.dataset.fallback = img.alt;
                    img.hidden = true;
                    img.style.display = "none";
                }

                img.addEventListener("error", showFallback, { once: true });

                if (img.complete && img.naturalWidth === 0) {
                    showFallback();
                }
            });

            if (
                motion.matches ||
                !("IntersectionObserver" in window)
            ) return;

            const timers = new Set();

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    const card = entry.target;
                    const siblings = [...card.parentElement.children];
                    const order = siblings.indexOf(card);

                    const timer = setTimeout(() => {
                        card.classList.remove("culture-pending");
                        timers.delete(timer);
                    }, order * 140);

                    timers.add(timer);
                    observer.unobserve(card);
                });
            }, {
                threshold: .08
            });

            cards.forEach(card => {
                const rect = card.getBoundingClientRect();

                // การ์ดที่อยู่ในหน้าจอแล้วให้แสดงทันที
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    return;
                }

                card.classList.add("culture-pending");
                observer.observe(card);
            });

            motion.addEventListener("change", event => {
                if (!event.matches) return;

                observer.disconnect();
                timers.forEach(timer => clearTimeout(timer));
                timers.clear();

                cards.forEach(card => {
                    card.classList.remove("culture-pending");
                });
            });
        })();
