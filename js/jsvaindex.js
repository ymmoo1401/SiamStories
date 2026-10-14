
(function () {
    "use strict";

    const input = document.getElementById("siteSearchInput");
    const results = document.getElementById("searchResults");
    const wrapper = document.getElementById("siteSearch");

    // หน้าเว็บทั้งหมดที่จะนำมาค้นหา
    const pages = [
        {
            url: "index.html",
            title: "หน้าแรก",
            description: "วัฒนธรรมไทย 4 ภาค ประเพณี อาหาร การแต่งกาย และภูมิปัญญา"
        },
        {
            url: "isan.html",
            title: "ภาคอีสาน",
            description: "บุญบั้งไฟ ผีตาโขน แห่เทียนพรรษา ไหลเรือไฟ บุญผะเหวด ส้มตำ ลาบหมู ไก่ย่าง"
        },
        {
            url: "central.html",
            title: "ภาคกลาง",
            description: "สงกรานต์ ลอยกระทง ตักบาตรเทโว อาหารภาคกลาง เครื่องแต่งกาย และภูมิปัญญา"
        },
        {
            url: "east.html",
            title: "ภาคเหนือ",
            description: "ประเพณีล้านนา ยี่เป็ง ปอยส่างลอง ตานก๋วยสลาก อาหารพื้นเมืองและผ้าทอ"
        },
        {
            url: "south.html",
            title: "ภาคใต้",
            description: "ชักพระ สารทเดือนสิบ แห่ผ้าขึ้นธาตุ มโนราห์ ผ้าบาติก และอาหารภาคใต้"
        },
        {
            url: "calendar.html",
            title: "ปฏิทินประเพณีไทย",
            description: "ค้นหาประเพณีตามภูมิภาค จังหวัด และเดือนที่จัดกิจกรรมตลอดทั้งปี"
        },
        {
            url: "team.html",
            title: "ผู้จัดทำ",
            description: "ข้อมูลผู้จัดทำเว็บไซต์ SIAM STORIES"
        }
    ];

    let searchIndex = pages.map(page => ({
        ...page,
        heading: page.title,
        text: page.description,
        anchor: ""
    }));

    // สร้างดัชนีจากเนื้อหา HTML จริง
    async function buildSearchIndex() {
        const collected = [];

        for (const page of pages) {
            try {
                const response = await fetch(page.url);

                if (!response.ok) {
                    throw new Error("Cannot load page");
                }

                const html = await response.text();

                const doc = new DOMParser().parseFromString(
                    html,
                    "text/html"
                );

                // ตัดส่วนที่ไม่ใช่เนื้อหาออก
                doc.querySelectorAll(
                    "script, style, nav, footer, noscript"
                ).forEach(el => el.remove());

                // เก็บข้อมูลหน้าเว็บ
                collected.push({
                    ...page,
                    heading: page.title,
                    text: (doc.body.textContent || "")
                        .replace(/\s+/g, " ")
                        .trim(),
                    anchor: ""
                });

                // เก็บหัวข้อและการ์ดเนื้อหา
                const sections = doc.querySelectorAll(
                    ".card, .region-card, .panel, " +
                    ".food-slide, .event-card, " +
                    "article, section.content"
                );

                sections.forEach(section => {
                    const heading = section.querySelector(
                        "h2, h3, h4"
                    );

                    if (!heading) return;

                    const content = (
                        section.textContent || ""
                    ).replace(/\s+/g, " ").trim();

                    if (!content) return;

                    const targetId = section.id ||
                        section.closest("[id]")?.id || "";

                    collected.push({
                        url: page.url,
                        title: page.title,
                        heading: heading.textContent.trim(),
                        text: content,
                        description: content.slice(0, 140),
                        anchor: targetId
                    });
                });

            } catch (error) {
                // ใช้ข้อมูลสำรองเมื่อเปิดแบบ file://
                collected.push({
                    ...page,
                    heading: page.title,
                    text: page.description,
                    anchor: ""
                });
            }
        }

        
        // ==========================================
        // เพิ่มข้อมูลค้นหาจาก MySQL ผ่าน PHP API
        // ==========================================

        const regionPages = {
            north: {
                url: "east.html",
                title: "ภาคเหนือ"
            },
            isan: {
                url: "isan.html",
                title: "ภาคอีสาน"
            },
            central: {
                url: "central.html",
                title: "ภาคกลาง"
            },
            south: {
                url: "south.html",
                title: "ภาคใต้"
            }
        };

        async function fetchDatabaseItems(apiUrl, type) {
            try {
                const response = await fetch(apiUrl, {
                    cache: "no-store"
                });

                if (!response.ok) {
                    throw new Error("HTTP " + response.status);
                }

                const payload = await response.json();

                if (
                    payload.success !== true ||
                    !Array.isArray(payload.data)
                ) {
                    throw new Error("Invalid API response");
                }

                payload.data.forEach(item => {
                    const region = regionPages[item.region];

                    if (!region) return;

                    const isTradition = type === "tradition";

                    const name = String(item.name || "");
                    const detail = String(item.detail || "");

                    const province = isTradition
                        ? String(item.province || "")
                        : "";

                    const tags = isTradition
                        ? ""
                        : String(item.tags || "");

                    collected.push({
                        url: region.url,
                        title: region.title,
                        heading: name,
                        text: [
                            name,
                            detail,
                            province,
                            tags
                        ].join(" "),
                        description: detail,
                        anchor: isTradition
                            ? "tradition"
                            : "food"
                    });
                });

            } catch (error) {
                console.warn(
                    "Database search unavailable:",
                    apiUrl,
                    error
                );
            }
        }

        await Promise.all([
            fetchDatabaseItems(
                "api/traditions.php",
                "tradition"
            ),
            fetchDatabaseItems(
                "api/foods.php",
                "food"
            )
        ]);

        searchIndex = collected;
    }

    function normalize(text) {
        return String(text || "")
            .toLocaleLowerCase()
            .replace(/\s+/g, " ")
            .trim();
    }

    function searchWebsite(keyword) {
        const query = normalize(keyword);

        if (!query) return [];

        const words = query.split(" ").filter(Boolean);

        return searchIndex
            .map(item => {
                const title = normalize(item.heading);
                const body = normalize(item.text);

                // ต้องพบทุกคำที่พิมพ์
                const matched = words.every(word =>
                    title.includes(word) ||
                    body.includes(word)
                );

                if (!matched) return null;

                // ให้ผลลัพธ์ที่ชื่อหัวข้อตรงขึ้นก่อน
                let score = 0;

                if (title.includes(query)) score += 20;

                words.forEach(word => {
                    if (title.includes(word)) score += 10;
                    if (body.includes(word)) score += 1;
                });

                return { ...item, score };
            })
            .filter(Boolean)
            .sort((a, b) => b.score - a.score)
            .slice(0, 12);
    }

    function createResult(item) {
        const link = document.createElement("a");

        link.className = "search-result";
        link.href = item.url +
            (item.anchor
                ? "#" + encodeURIComponent(item.anchor)
                : "");

        const title = document.createElement("strong");
        title.textContent = item.heading;

        const description = document.createElement("p");
        description.textContent =
            (item.description || item.text)
            .replace(/\s+/g, " ")
            .slice(0, 115) + "...";

        const pageLabel = document.createElement("small");
        pageLabel.textContent = "ไปยัง: " + item.title;

        link.append(title, description, pageLabel);

        return link;
    }

    function openResults() {
        results.classList.add("show");
        input.setAttribute("aria-expanded", "true");
    }

    function closeResults() {
        results.classList.remove("show");
        input.setAttribute("aria-expanded", "false");
    }

    function renderResults() {
        const keyword = input.value.trim();

        results.replaceChildren();

        if (!keyword) {
            closeResults();
            return;
        }

        const found = searchWebsite(keyword);

        const heading = document.createElement("div");
        heading.className = "search-heading";
        heading.textContent =
            "ผลการค้นหา " + found.length + " รายการ";

        results.appendChild(heading);

        if (found.length === 0) {
            const empty = document.createElement("div");
            empty.className = "search-empty";
            empty.textContent =
                "ไม่พบข้อมูล ลองค้นหาด้วยคำอื่น";
            results.appendChild(empty);
        } else {
            found.forEach(item => {
                results.appendChild(createResult(item));
            });
        }

        openResults();
    }

    input.addEventListener("input", renderResults);

    input.addEventListener("focus", () => {
        if (input.value.trim()) {
            renderResults();
        }
    });

    input.addEventListener("keydown", event => {
        if (event.key === "Escape") {
            closeResults();
            input.blur();
        }

        if (event.key === "Enter") {
            const first = results.querySelector(
                ".search-result"
            );

            if (first) {
                window.location.href = first.href;
            }
        }
    });

    document.addEventListener("click", event => {
        if (!wrapper.contains(event.target)) {
            closeResults();
        }
    });

    // เริ่มโหลดข้อมูลจากทุกหน้าเว็บ
    buildSearchIndex().then(() => {
        if (input.value.trim()) {
            renderResults();
        }
    });

})();
