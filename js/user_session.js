/**
 * SIAM STORIES - Persistent User Session & AI Gravity-style History System
 * จัดการ:
 * 1. ค้างสถานะผู้ใช้ (Persistent Session) บนหน้าบ้านทั้งหมด
 * 2. แสดงข้อมูลผู้ใช้ โปรไฟล์ และสิทธิ์ (Admin vs Member)
 * 3. บันทึกประวัติการค้นหาและกิจกรรม (User Activity & Search History)
 * 4. หน้าต่าง Drawer ประวัติการใช้งานสไตล์ AI Gravity / Antigravity Workspace
 */

(function () {
    "use strict";

    // ป้องกันการโหลดซ้ำ
    if (window.SiamStoriesUser) return;

    const API_BASE = "api";

    // สถานะส่วนกลาง
    const state = {
        isLoggedIn: false,
        user: null,
        historyItems: [],
        historyStats: { total: 0, searches: 0, views: 0, visits: 0, filters: 0 },
        activeFilter: "all",
        searchQuery: "",
        isLoadingHistory: false
    };

    /**
     * ดึงตัวอักษรแรกสำหรับ Avatar
     */
    function getAvatarLetter(name) {
        if (!name) return "U";
        const trimmed = name.trim();
        return trimmed.charAt(0).toUpperCase();
    }

    /**
     * กำหนดชื่อหน้าเว็บปัจจุบันสำหรับบันทึกการเข้าชม
     */
    function getCurrentPageInfo() {
        const path = window.location.pathname.toLowerCase();
        let title = "หน้าแรก SIAM STORIES";
        let pageUrl = "index.html";

        if (path.includes("isan.html")) {
            title = "ภาคอีสาน (ประเพณีและอาหาร)";
            pageUrl = "isan.html";
        } else if (path.includes("central.html")) {
            title = "ภาคกลาง (ประเพณีและอาหาร)";
            pageUrl = "central.html";
        } else if (path.includes("east.html")) {
            title = "ภาคเหนือ (ประเพณีและอาหาร)";
            pageUrl = "east.html";
        } else if (path.includes("south.html")) {
            title = "ภาคใต้ (ประเพณีและอาหาร)";
            pageUrl = "south.html";
        } else if (path.includes("calendar.html")) {
            title = "ปฏิทินประเพณีไทย 12 เดือน";
            pageUrl = "calendar.html";
        } else if (path.includes("team.html")) {
            title = "คณะผู้จัดทำเว็บไซต์";
            pageUrl = "team.html";
        }

        return { title, url: pageUrl };
    }

    /**
     * ส่งคำขอบันทึกกิจกรรม (Log Activity API)
     */
    async function logActivity(actionType, queryText, targetTitle, targetUrl) {
        if (!state.isLoggedIn) return;

        try {
            await fetch(`${API_BASE}/log_activity.php`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    action_type: actionType || "search",
                    query_text: queryText || "",
                    target_title: targetTitle || "",
                    target_url: targetUrl || window.location.pathname
                })
            });

            // อัปเดตตัวนับประวัติเมื่อมีการบันทึกใหม่
            state.historyStats.total++;
            if (actionType === "search") state.historyStats.searches++;
            else if (actionType === "view") state.historyStats.views++;
            else if (actionType === "page_visit") state.historyStats.visits++;

            updateStatsInDrawer();
        } catch (err) {
            console.warn("ไม่สามารถบันทึกกิจกรรมได้:", err);
        }
    }

    /**
     * บันทึกการเข้าชมหน้าเว็บปัจจุบันแบบกันการบันทึกซ้ำ (Debounce ใน Session)
     */
    function logCurrentPageVisit() {
        if (!state.isLoggedIn) return;

        const pageInfo = getCurrentPageInfo();
        const sessionKey = `last_visit_${pageInfo.url}`;
        const lastVisitTime = parseInt(sessionStorage.getItem(sessionKey) || "0", 10);
        const now = Date.now();

        // บันทึกซ้ำเมื่อผ่านไปเกิน 3 นาทีในการเปิดหน้านั้นๆ
        if (now - lastVisitTime > 180000) {
            sessionStorage.setItem(sessionKey, now.toString());
            logActivity("page_visit", pageInfo.title, pageInfo.title, pageInfo.url);
        }
    }

    /**
     * ตรวจสอบสถานะการล็อกอินกับเซิร์ฟเวอร์
     */
    async function checkAuthStatus() {
        try {
            const res = await fetch(`${API_BASE}/auth_status.php`, {
                headers: { "Cache-Control": "no-cache" }
            });
            if (!res.ok) throw new Error("Network error");
            const data = await res.json();

            if (data.success && data.logged_in && data.user) {
                state.isLoggedIn = true;
                state.user = data.user;
                state.historyStats.total = data.user.history_count || 0;
            } else {
                state.isLoggedIn = false;
                state.user = null;
            }
        } catch (e) {
            state.isLoggedIn = false;
            state.user = null;
        }

        renderNavAuth();
        if (state.isLoggedIn) {
            buildHistoryDrawer();
            logCurrentPageVisit();
            attachContentClickListeners();
        }
    }

    /**
     * วาดปุ่มสถานะเข้าสู่ระบบ / โปรไฟล์ผู้ใช้บนแถบเมนู (Navbar)
     */
    function renderNavAuth() {
        const navMenu = document.querySelector(".nav .menu");
        if (!navMenu) return;

        // ลบปุ่มล็อกอินเดิมหรือคอนเทนเนอร์เดิม
        const existingLogin = navMenu.querySelector(".admin-login-link");
        if (existingLogin) existingLogin.remove();

        let authWrapper = navMenu.querySelector(".nav-auth-wrapper");
        if (!authWrapper) {
            authWrapper = document.createElement("div");
            authWrapper.className = "nav-auth-wrapper";
            navMenu.appendChild(authWrapper);
        }

        if (!state.isLoggedIn) {
            // กรณีเป็นแขก (Guest) แสดงไอคอนวงกลมพร้อม Tooltip สีแดงเมื่อ Hover (ตรงตามรูปตัวอย่าง)
            authWrapper.innerHTML = `
                <a href="admin/login.php" class="user-circle-btn" aria-label="บัญชีผู้ใช้งาน" title="">
                    <svg class="user-icon-svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span class="user-red-tooltip">บัญชีผู้ใช้งาน</span>
                </a>
            `;
            return;
        }

        // กรณีเข้าสู่ระบบแล้ว (Persistent Session)
        const user = state.user;
        const avatarLetter = getAvatarLetter(user.display_name || user.username);
        const roleLabel = user.is_admin ? "ผู้ดูแล" : "สมาชิก";
        const roleClass = user.is_admin ? "role-admin" : "role-member";

        authWrapper.innerHTML = `
            <button type="button" class="user-circle-btn is-logged-in" id="userCircleBtn" aria-expanded="false" aria-label="บัญชีผู้ใช้งาน">
                <span class="user-avatar-initial">${avatarLetter}</span>
                <span class="online-status-dot"></span>
                <span class="user-red-tooltip">บัญชีผู้ใช้งาน</span>
            </button>
            <div class="user-nav-dropdown" id="userNavDropdown">
                <div class="user-dropdown-header">
                    <div class="user-dropdown-avatar">${avatarLetter}</div>
                    <div class="user-dropdown-info">
                        <div class="user-dropdown-name">${escapeHtml(user.display_name)}</div>
                        <div class="user-dropdown-sub">@${escapeHtml(user.username)}</div>
                        <span class="user-role-badge-small ${roleClass}" style="margin-top: 4px; display: inline-block;">
                            ${user.is_admin ? "🛡️ สิทธิ์ผู้ดูแลระบบ" : "👤 บัญชีสมาชิก"}
                        </span>
                    </div>
                </div>
                <ul class="user-dropdown-menu-list">
                    <li>
                        <button type="button" id="btnOpenHistoryDrawer">
                            <span class="item-icon">⚡</span>
                            <span style="font-weight: 600;">ประวัติ</span>
                            <span class="badge-counter" id="userHistoryCounterBadge">${state.historyStats.total}</span>
                        </button>
                    </li>
                    ${user.is_admin ? `
                    <li>
                        <a href="admin/index.php" target="_blank" title="เข้าสู่แผงจัดการผู้ดูแล">
                            <span class="item-icon">⚙️</span>
                            <span>แผงควบคุมผู้ดูแล (Admin)</span>
                        </a>
                    </li>
                    ` : ""}
                </ul>
                <div class="user-dropdown-footer">
                    <button type="button" class="user-dropdown-logout-btn" id="btnLogoutUser">
                        <span>🚪</span> ออกจากระบบ
                    </button>
                </div>
            </div>
        `;

        // จัดการคลิก Toggle Dropdown
        const circleBtn = authWrapper.querySelector("#userCircleBtn");
        const historyDrawerBtn = authWrapper.querySelector("#btnOpenHistoryDrawer");
        const logoutBtn = authWrapper.querySelector("#btnLogoutUser");

        if (circleBtn) {
            circleBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                authWrapper.classList.toggle("open");
                const isOpen = authWrapper.classList.contains("open");
                circleBtn.setAttribute("aria-expanded", isOpen ? "true" : "false");
            });
        }

        if (historyDrawerBtn) {
            historyDrawerBtn.addEventListener("click", () => {
                authWrapper.classList.remove("open");
                openHistoryDrawer();
            });
        }

        if (logoutBtn) {
            logoutBtn.addEventListener("click", handleLogout);
        }

        // ปิด Dropdown เมื่อคลิกที่อื่น
        document.addEventListener("click", (e) => {
            if (!authWrapper.contains(e.target)) {
                authWrapper.classList.remove("open");
                if (circleBtn) circleBtn.setAttribute("aria-expanded", "false");
            }
        });
    }

    /**
     * ออกจากระบบพร้อมยืนยัน
     */
    function handleLogout() {
        if (confirm("ต้องการออกจากระบบใช่หรือไม่?")) {
            window.location.href = "admin/logout.php?ref=home";
        }
    }

    /**
     * สร้างหน้าต่าง Drawer สำหรับประวัติการใช้งานสไตล์ AI Gravity
     */
    function buildHistoryDrawer() {
        if (document.getElementById("aiHistoryDrawer")) return;

        const user = state.user;
        const avatarLetter = getAvatarLetter(user.display_name || user.username);
        const roleLabel = user.is_admin ? "ผู้ดูแลระบบ (Admin)" : "สมาชิก SIAM STORIES";
        const roleBadgeClass = user.is_admin ? "admin" : "";

        // Backdrop
        const backdrop = document.createElement("div");
        backdrop.id = "aiHistoryBackdrop";
        backdrop.className = "ai-history-backdrop";
        backdrop.addEventListener("click", closeHistoryDrawer);
        document.body.appendChild(backdrop);

        // Drawer
        const drawer = document.createElement("aside");
        drawer.id = "aiHistoryDrawer";
        drawer.className = "ai-history-drawer";
        drawer.setAttribute("role", "dialog");
        drawer.setAttribute("aria-label", "ประวัติ");

        drawer.innerHTML = `
            <!-- Header -->
            <div class="drawer-header">
                <div class="drawer-header-content">
                    <h3>
                        <span class="sparkle">⚡</span>
                        ประวัติ
                    </h3>
                    <p>บันทึกประวัติการสืบค้น วัฒนธรรม และหน้าที่คุณเคยสำรวจ</p>
                </div>
                <button type="button" class="drawer-close-btn" id="btnCloseDrawer" title="ปิดหน้าต่าง">✕</button>
            </div>

            <!-- User Summary Card (เหมือน User Profile บน AI Gravity) -->
            <div class="drawer-user-summary">
                <div class="drawer-user-top-row">
                    <div class="drawer-user-avatar">${avatarLetter}</div>
                    <div class="drawer-user-details">
                        <div class="drawer-user-name">${escapeHtml(user.display_name)}</div>
                        <div class="drawer-user-badges">
                            <span class="drawer-badge ${roleBadgeClass}">● ${roleLabel}</span>
                            <span class="drawer-badge">@${escapeHtml(user.username)}</span>
                        </div>
                    </div>
                </div>
                <div class="drawer-user-stats-row">
                    <div class="stat-pill">
                        <span class="stat-num" id="statSearchesNum">0</span>
                        <span class="stat-lbl">🔍 ค้นหา</span>
                    </div>
                    <div class="stat-pill">
                        <span class="stat-num" id="statViewsNum">0</span>
                        <span class="stat-lbl">📖 ดูเนื้อหา</span>
                    </div>
                    <div class="stat-pill">
                        <span class="stat-num" id="statVisitsNum">0</span>
                        <span class="stat-lbl">🧭 เข้าชมหน้า</span>
                    </div>
                </div>
            </div>

            <!-- Search in History & Filter Tabs -->
            <div class="drawer-controls">
                <div class="history-search-input-box">
                    <span class="history-search-icon">🔍</span>
                    <input type="search" id="inputFilterHistory" placeholder="ค้นหาในประวัติ..." autocomplete="off">
                </div>
                <div class="history-filter-tabs">
                    <button type="button" class="filter-tab-btn active" data-filter="all">ทั้งหมด</button>
                    <button type="button" class="filter-tab-btn" data-filter="search">🔍 คำค้นหา</button>
                    <button type="button" class="filter-tab-btn" data-filter="view">🏮 ประเพณี/อาหาร</button>
                    <button type="button" class="filter-tab-btn" data-filter="page_visit">🧭 หน้าที่เข้าชม</button>
                </div>
            </div>

            <!-- Stream of history cards -->
            <div class="drawer-history-list" id="drawerHistoryList">
                <div class="history-empty-state">
                    <div class="empty-icon">⏳</div>
                    <h4>กำลังโหลดประวัติ...</h4>
                </div>
            </div>

            <!-- Footer -->
            <div class="drawer-footer">
                <button type="button" class="btn-clear-history" id="btnClearAllHistory" title="ลบประวัติการใช้งานทั้งหมดของคุณ">
                    <span>🗑️</span> ล้างประวัติทั้งหมด
                </button>
                <button type="button" class="btn-close-drawer-bottom" id="btnCloseDrawerBottom">
                    ปิด
                </button>
            </div>
        `;

        document.body.appendChild(drawer);

        // Bind events
        drawer.querySelector("#btnCloseDrawer").addEventListener("click", closeHistoryDrawer);
        drawer.querySelector("#btnCloseDrawerBottom").addEventListener("click", closeHistoryDrawer);
        drawer.querySelector("#btnClearAllHistory").addEventListener("click", clearAllHistory);

        // Filter tabs
        const filterBtns = drawer.querySelectorAll(".filter-tab-btn");
        filterBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                filterBtns.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                state.activeFilter = btn.dataset.filter || "all";
                loadUserHistory();
            });
        });

        // Search input in drawer
        const searchInput = drawer.querySelector("#inputFilterHistory");
        let debounceTimer = null;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                state.searchQuery = e.target.value.trim();
                loadUserHistory();
            }, 300);
        });

        // Close on Escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && drawer.classList.contains("active")) {
                closeHistoryDrawer();
            }
        });
    }

    /**
     * อัปเดตสถิติตัวเลขใน Drawer
     */
    function updateStatsInDrawer() {
        const searchesEl = document.getElementById("statSearchesNum");
        const viewsEl = document.getElementById("statViewsNum");
        const visitsEl = document.getElementById("statVisitsNum");
        const counterBadge = document.getElementById("userHistoryCounterBadge");

        if (searchesEl) searchesEl.textContent = state.historyStats.searches;
        if (viewsEl) viewsEl.textContent = state.historyStats.views;
        if (visitsEl) visitsEl.textContent = state.historyStats.visits;
        if (counterBadge) counterBadge.textContent = state.historyStats.total;
    }

    /**
     * เปิดหน้าต่าง History Drawer
     */
    function openHistoryDrawer() {
        const backdrop = document.getElementById("aiHistoryBackdrop");
        const drawer = document.getElementById("aiHistoryDrawer");

        if (!backdrop || !drawer) {
            buildHistoryDrawer();
        }

        const b = document.getElementById("aiHistoryBackdrop");
        const d = document.getElementById("aiHistoryDrawer");
        if (b && d) {
            b.classList.add("active");
            d.classList.add("active");
            document.body.style.overflow = "hidden";
            loadUserHistory();
        }
    }

    /**
     * ปิดหน้าต่าง History Drawer
     */
    function closeHistoryDrawer() {
        const backdrop = document.getElementById("aiHistoryBackdrop");
        const drawer = document.getElementById("aiHistoryDrawer");
        if (backdrop) backdrop.classList.remove("active");
        if (drawer) drawer.classList.remove("active");
        document.body.style.overflow = "";
    }

    /**
     * โหลดข้อมูลประวัติจาก API
     */
    async function loadUserHistory() {
        const listContainer = document.getElementById("drawerHistoryList");
        if (!listContainer) return;

        state.isLoadingHistory = true;
        listContainer.innerHTML = `
            <div class="history-empty-state">
                <div class="empty-icon">⏳</div>
                <h4>กำลังค้นหาประวัติ...</h4>
            </div>
        `;

        try {
            const params = new URLSearchParams();
            if (state.activeFilter && state.activeFilter !== "all") {
                params.append("type", state.activeFilter);
            }
            if (state.searchQuery) {
                params.append("q", state.searchQuery);
            }
            params.append("limit", "70");

            const res = await fetch(`${API_BASE}/user_history.php?${params.toString()}`);
            if (!res.ok) throw new Error("ไม่สามารถโหลดประวัติได้");

            const data = await res.json();
            if (data.success) {
                state.historyItems = data.items || [];
                if (data.stats) {
                    state.historyStats = data.stats;
                    updateStatsInDrawer();
                }
                renderHistoryCards(state.historyItems);
            } else {
                listContainer.innerHTML = `
                    <div class="history-empty-state">
                        <div class="empty-icon">⚠️</div>
                        <h4>${escapeHtml(data.message || "ไม่สามารถดึงข้อมูลได้")}</h4>
                    </div>
                `;
            }
        } catch (err) {
            listContainer.innerHTML = `
                <div class="history-empty-state">
                    <div class="empty-icon">⚠️</div>
                    <h4>เกิดข้อผิดพลาดในการโหลดประวัติ</h4>
                    <p>กรุณาลองใหม่อีกครั้ง</p>
                </div>
            `;
        } finally {
            state.isLoadingHistory = false;
        }
    }

    /**
     * แสดงผลการ์ดประวัติใน Drawer
     */
    function renderHistoryCards(items) {
        const listContainer = document.getElementById("drawerHistoryList");
        if (!listContainer) return;

        if (!items || items.length === 0) {
            listContainer.innerHTML = `
                <div class="history-empty-state">
                    <div class="empty-icon">📜</div>
                    <h4>ยังไม่มีประวัติในหมวดหมู่นี้</h4>
                    <p>ลองค้นหาวัฒนธรรม ประเพณี หรือเปิดดูเรื่องราวต่างๆ ในเว็บไซต์ ระบบจะจดจำและบันทึกประวัติไว้ให้คุณโดยอัตโนมัติ</p>
                </div>
            `;
            return;
        }

        listContainer.innerHTML = "";

        items.forEach(item => {
            const card = document.createElement("div");
            card.className = "history-card-item";
            card.dataset.id = item.id;

            let iconEmoji = "🔍";
            let typeClass = "type-search";
            let typeLabel = "คำค้นหา";

            if (item.action_type === "view") {
                iconEmoji = "🏮";
                typeClass = "type-view";
                typeLabel = "เปิดดูเนื้อหา";
            } else if (item.action_type === "page_visit") {
                iconEmoji = "🧭";
                typeClass = "type-page_visit";
                typeLabel = "เข้าชมหน้า";
            } else if (item.action_type === "filter") {
                iconEmoji = "⚙️";
                typeClass = "type-filter";
                typeLabel = "ตัวกรอง";
            }

            const titleText = item.query_text || item.target_title || "กิจกรรม";
            const detailText = item.target_title && item.target_title !== titleText ? item.target_title : (item.target_url || "");

            card.innerHTML = `
                <div class="history-card-icon ${typeClass}">${iconEmoji}</div>
                <div class="history-card-main">
                    <div class="history-card-title-row">
                        <div class="history-card-title">${escapeHtml(titleText)}</div>
                        <span class="history-card-time">${escapeHtml(item.thai_time)}</span>
                    </div>
                    ${detailText ? `<div class="history-card-detail">${escapeHtml(detailText)}</div>` : ""}
                    <div class="history-card-actions">
                        ${getActionJumpButton(item)}
                        <button type="button" class="btn-history-del-item" title="ลบรายการนี้" data-del-id="${item.id}">✕</button>
                    </div>
                </div>
            `;

            // ผูกเหตุการณ์ปุ่มลบเดี่ยว
            const delBtn = card.querySelector(".btn-history-del-item");
            if (delBtn) {
                delBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    deleteHistoryItem(item.id, card);
                });
            }

            // ผูกปุ่มกระโดด/ค้นหาซ้ำ
            const jumpBtn = card.querySelector(".btn-history-jump");
            if (jumpBtn) {
                jumpBtn.addEventListener("click", () => {
                    handleJumpAction(item);
                });
            }

            listContainer.appendChild(card);
        });
    }

    /**
     * สร้างปุ่ม Action ย้อนกลับสำหรับแต่ละประวัติ
     */
    function getActionJumpButton(item) {
        if (item.action_type === "search") {
            return `<button type="button" class="btn-history-jump"><span>🔍</span> ค้นหาอีกครั้ง</button>`;
        } else if (item.action_type === "page_visit" || item.action_type === "view") {
            return `<button type="button" class="btn-history-jump"><span>↗</span> ไปยังหน้านี้</button>`;
        }
        return `<button type="button" class="btn-history-jump"><span>↗</span> เรียกดู</button>`;
    }

    /**
     * จัดการเมื่อคลิก "ค้นหาอีกครั้ง" หรือ "ไปยังหน้านี้"
     */
    function handleJumpAction(item) {
        closeHistoryDrawer();

        if (item.action_type === "search") {
            const searchInput = document.getElementById("siteSearchInput");
            if (searchInput) {
                searchInput.value = item.query_text;
                searchInput.focus();
                // trigger search
                searchInput.dispatchEvent(new Event("input", { bubbles: true }));
            } else {
                // หากหน้าปัจจุบันไม่มี search ให้ย้ายไป index.html
                window.location.href = `index.html?q=${encodeURIComponent(item.query_text)}`;
            }
        } else if (item.target_url) {
            window.location.href = item.target_url;
        }
    }

    /**
     * ลบรายการประวัติรายการเดียว
     */
    async function deleteHistoryItem(id, cardElement) {
        try {
            const res = await fetch(`${API_BASE}/user_history.php?id=${encodeURIComponent(id)}`, {
                method: "DELETE",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            if (data.success) {
                if (cardElement) {
                    cardElement.style.opacity = "0";
                    cardElement.style.transform = "translateX(20px)";
                    setTimeout(() => cardElement.remove(), 200);
                }
                state.historyStats.total = Math.max(0, state.historyStats.total - 1);
                updateStatsInDrawer();
            } else {
                alert(data.message || "ไม่สามารถลบรายการได้");
            }
        } catch (e) {
            alert("เกิดข้อผิดพลาดในการเชื่อมต่อเพื่อลบรายการ");
        }
    }

    /**
     * ล้างประวัติทั้งหมด
     */
    async function clearAllHistory() {
        if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการล้างประวัติการค้นหาและกิจกรรมทั้งหมดของคุณ? (ไม่สามารถกู้คืนได้)")) {
            return;
        }

        try {
            const res = await fetch(`${API_BASE}/user_history.php?clear_all=1`, {
                method: "DELETE",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ clear_all: 1 })
            });
            const data = await res.json();
            if (data.success) {
                state.historyItems = [];
                state.historyStats = { total: 0, searches: 0, views: 0, visits: 0, filters: 0 };
                updateStatsInDrawer();
                renderHistoryCards([]);
            } else {
                alert(data.message || "ไม่สามารถล้างประวัติได้");
            }
        } catch (e) {
            alert("เกิดข้อผิดพลาดในการเชื่อมต่อเพื่อล้างประวัติ");
        }
    }

    /**
     * แนบ Event Listener สำหรับการคลิกดูการ์ดประเพณีและอาหารบนหน้าภูมิภาค
     */
    function attachContentClickListeners() {
        document.addEventListener("click", (e) => {
            const card = e.target.closest(".slider-card, .dress-card, [data-tradition-id], [data-food-id], .calendar-card");
            if (!card) return;

            const titleEl = card.querySelector("h3, h4, .card-title, strong");
            if (!titleEl) return;

            const titleText = titleEl.textContent.trim();
            if (!titleText) return;

            // ตรวจสอบชนิด
            const isFood = card.closest("#food") || card.classList.contains("food-card");
            const actionType = "view";
            const targetUrl = window.location.pathname + (card.id ? `#${card.id}` : "");

            logActivity(actionType, titleText, isFood ? `อาหาร: ${titleText}` : `ประเพณี: ${titleText}`, targetUrl);
        });
    }

    /**
     * Helper Escape HTML
     */
    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Export API ให้กับ JavaScript หน้าอื่นเรียกใช้
    window.SiamStoriesUser = {
        get state() { return state; },
        logActivity,
        openHistoryDrawer,
        closeHistoryDrawer,
        loadUserHistory,
        clearAllHistory
    };

    // เริ่มต้นทำงานทันทีเมื่อเอกสารพร้อม
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", checkAuthStatus);
    } else {
        checkAuthStatus();
    }
})();
