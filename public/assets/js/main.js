/* ================================
   GLOBAL CONSTANTS
================================ */
const SIDEBAR_BREAKPOINT = 1191;

/* ================================
   APP TOGGLER
================================ */
const initAppToggler = () => {
    const appTogglers = document.querySelectorAll(".app-toggler");
    const appMenubars = document.querySelectorAll(".app-menubar");

    if (!appTogglers.length) return;

    appTogglers.forEach((toggler) => {
        toggler.addEventListener("click", () => {
            toggler.classList.toggle("active");

            if (window.innerWidth >= SIDEBAR_BREAKPOINT) {
                const current =
                    document.documentElement.getAttribute("data-app-sidebar");
                document.documentElement.setAttribute(
                    "data-app-sidebar",
                    current === "full" ? "mini" : "full"
                );
            } else {
                appMenubars.forEach((m) => m.classList.toggle("open"));
            }
        });
    });

    appMenubars.forEach((menubar) => {
        menubar.addEventListener("mouseenter", () => {
            if (
                document.documentElement.getAttribute("data-app-sidebar") ===
                "mini"
            ) {
                document.documentElement.setAttribute(
                    "data-app-sidebar",
                    "mini-hover"
                );
            }
        });

        menubar.addEventListener("mouseleave", () => {
            if (
                document.documentElement.getAttribute("data-app-sidebar") ===
                "mini-hover"
            ) {
                document.documentElement.setAttribute(
                    "data-app-sidebar",
                    "mini"
                );
            }
        });
    });
};

/* ================================
   CURRENT YEAR
================================ */
const currentYear = () => {
    document.querySelectorAll(".currentYear").forEach((el) => {
        el.textContent = new Date().getFullYear();
    });
};

/* ================================
   DYNAMIC HEIGHTS
================================ */
const setElementHeight = () => {
    const footer = document.querySelector(".footer-wrapper");
    const chatBox = document.querySelector(".chat-wrapper");

    if (footer) {
        document.documentElement.style.setProperty(
            "--footer-height",
            `${footer.offsetHeight}px`
        );
    }

    if (chatBox) {
        document.documentElement.style.setProperty(
            "--chat-height",
            `${chatBox.offsetHeight}px`
        );
    }
};

/* ================================
   SELECT PICKER
================================ */
const initSelectPicker = () => {
    document.querySelectorAll(".select-status").forEach((dropdown) => {
        const toggleBtn = dropdown.querySelector(".dropdown-toggle");
        const items = dropdown.querySelectorAll(".dropdown-item");

        if (!toggleBtn) return;

        const updateBtn = (text, cls) => {
            toggleBtn.classList.forEach((c) => {
                if (/^btn-/.test(c) && !["btn-sm", "btn-lg"].includes(c)) {
                    toggleBtn.classList.remove(c);
                }
            });
            if (cls) toggleBtn.classList.add(...cls.split(" "));
            toggleBtn.textContent = text;
        };

        const defaultItem = dropdown.querySelector('[data-selected="true"]');
        if (defaultItem) {
            updateBtn(
                defaultItem.textContent.trim(),
                defaultItem.getAttribute("data-class")
            );
        }

        items.forEach((item) => {
            item.addEventListener("click", (e) => {
                e.preventDefault();
                items.forEach((i) => i.removeAttribute("data-selected"));
                item.setAttribute("data-selected", "true");
                updateBtn(
                    item.textContent.trim(),
                    item.getAttribute("data-class")
                );
            });
        });
    });
};

/* ================================
   CHECKBOX SYNC
================================ */
const initSectionCheckboxSync = () => {
    document.querySelectorAll(".data-row-checkbox").forEach((section) => {
        const master = section.querySelector("[data-row-checkbox]");
        const boxes = section.querySelectorAll("[data-checkbox]");
        if (!master || !boxes.length) return;

        master.addEventListener("change", () => {
            boxes.forEach((cb) => (cb.checked = master.checked));
        });

        boxes.forEach((cb) => {
            cb.addEventListener("change", () => {
                master.checked = [...boxes].every((c) => c.checked);
            });
        });
    });
};

/* ================================
   BOOTSTRAP
================================ */
const initTooltips = () => {
    if (!window.bootstrap) return;
    document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach((el) => new bootstrap.Tooltip(el));
};

const initPopover = () => {
    if (!window.bootstrap) return;
    document
        .querySelectorAll('[data-bs-toggle="popover"]')
        .forEach((el) => new bootstrap.Popover(el));
};

/* ================================
   SIDEBAR MENU (jQuery)
================================ */
const initSidebarMenu = () => {
    if (typeof jQuery === "undefined") return;
    const $ = jQuery;

    // Sab submenus initially close karo
    $(".app-navbar .menubar > li.menu-arrow > a").next(".menu-inner").slideUp();
    $(".app-navbar .menu-inner > li > a").next(".menu-inner").slideUp();

    // Click handler
    $(
        ".app-navbar .menubar > li.menu-arrow > a, .app-navbar .menu-inner > li > a"
    )
        .off("click")
        .on("click", (e) => {
            const $this = $(e.currentTarget);
            const $submenu = $this.next(".menu-inner");

            if ($this.hasClass("open")) {
                // Agar open hai to close karo
                $this.removeClass("open");
                $submenu.slideUp();
            } else {
                if (!window.event.ctrlKey) {
                    $this.addClass("open");
                }

                if ($submenu.length > 0) {
                    e.preventDefault(); // sirf submenu ke liye prevent default
                    $submenu.slideDown();

                    // Siblings close karo
                    $this
                        .parent("li")
                        .siblings("li")
                        .children("a")
                        .removeClass("open")
                        .next(".menu-inner")
                        .slideUp();
                } else {
                    $submenu.slideUp();
                }
            }
        });

    // Active link highlight aur menu expand
    let o = $(".app-navbar .menubar a")
        .filter(function () {
            return this.href === window.location.href;
        })
        .addClass("active")
        .parent()
        .addClass("active")
        .parent()
        .show()
        .siblings("a")
        .addClass("active open")
        .parent()
        .parent()
        .show()
        .siblings("a")
        .addClass("open");

    while (o.is("li")) {
        o = o
            .parent()
            .slideDown()
            .parent("li")
            .children("a")
            .addClass("active");
    }
};

/* ================================
   CHECKABLE ITEMS
================================ */
const initCheckable = () => {
    document.querySelectorAll(".checkable-wrapper").forEach((wrapper) => {
        const all = wrapper.querySelector(".checkable-check-all");
        const boxes = wrapper.querySelectorAll(".checkable-check-input");

        if (all) {
            all.addEventListener("change", () => {
                boxes.forEach((b) => {
                    b.checked = all.checked;
                    b.closest(".checkable-item")?.classList.toggle(
                        "is-checked",
                        all.checked
                    );
                });
            });
        }

        wrapper.addEventListener("change", (e) => {
            if (!e.target.classList.contains("checkable-check-input")) return;
            e.target
                .closest(".checkable-item")
                ?.classList.toggle("is-checked", e.target.checked);
            if (all) {
                all.checked = !wrapper.querySelector(
                    ".checkable-check-input:not(:checked)"
                );
            }
        });
    });
};

/* ================================
   EMAIL + CHAT SIDEBAR
================================ */
const initEmailSidebarToggle = () => {
    const t = document.querySelector(".mail-sidebar-toggler");
    const s = document.querySelector(".mail-sidebar");
    const o = document.querySelector(".sidebar-mobile-overlay");
    if (!t || !s || !o) return;

    t.onclick = () => {
        s.classList.toggle("open");
        o.classList.toggle("show", s.classList.contains("open"));
    };
    o.onclick = () => {
        s.classList.remove("open");
        o.classList.remove("show");
    };
};

const initChatSidebarToggle = () => {
    const t = document.querySelector(".chat-sidebar-toggler");
    const s = document.querySelector(".chat-sidebar");
    const o = document.querySelector(".sidebar-mobile-overlay");
    const c = document.querySelector(".btn-close");
    if (!t || !s || !o) return;

    const close = () => {
        s.classList.remove("open");
        o.classList.remove("show");
    };

    t.onclick = () => {
        s.classList.toggle("open");
        o.classList.toggle("show", s.classList.contains("open"));
    };
    o.onclick = close;
    c && (c.onclick = close);
};

/* ================================
   BOOKMARKS
================================ */
const initBookmarks = () => {
    document.addEventListener("click", (e) => {
        const bm = e.target.closest(".mail-item-bookmark");
        bm && bm.classList.toggle("active");
    });
};

/* ================================
   THEME SWITCHER
================================ */
const ThemeSwitcher = () => {
    const get = (n) =>
        document.cookie.match(new RegExp(`(^| )${n}=([^;]+)`))?.[2];
    const set = (n, v) =>
        (document.cookie = `${n}=${v};path=/;max-age=31536000`);

    const apply = (t) => {
        const theme =
            t === "auto"
                ? matchMedia("(prefers-color-scheme: dark)").matches
                    ? "dark"
                    : "light"
                : t;
        document.documentElement.setAttribute("data-bs-theme", theme);
    };

    window.addEventListener("DOMContentLoaded", () => {
        const theme = get("theme") || "light";
        apply(theme);

        document.querySelectorAll("[data-bs-theme-value]").forEach((btn) => {
            btn.onclick = () => {
                const v = btn.dataset.bsThemeValue;
                set("theme", v);
                apply(v);
            };
        });
    });
};

/* ================================
   INIT
================================ */
document.addEventListener("DOMContentLoaded", () => {
    try {
        Waves?.init();
        initAppToggler();
        setElementHeight();
        currentYear();
        initSectionCheckboxSync();
        initSelectPicker();
        initTooltips();
        initPopover();
        initCheckable();
        initSidebarMenu();
        initEmailSidebarToggle();
        initChatSidebarToggle();
        initBookmarks();
        ThemeSwitcher();
    } catch (e) {
        console.error("Init Error:", e);
    }
});

/* ================================
   DATATABLE REDRAW
================================ */
$(document).ready(function () {
    $(".dataTable").each(function () {
        const dt = $(this).DataTable();
        dt.on("draw.dt", () => {
            initSelectPicker();
            initSectionCheckboxSync();
        });
    });
});
