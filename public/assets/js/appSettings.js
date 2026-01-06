const APP_SIDEBAR_BREAKPOINT = 1191;

const appSettings = {
    appTheme: "light",
    appSidebar: "full",
    appColor: "blue",
    appLayout: "default",
    appBoxed: false,
};

document.addEventListener("DOMContentLoaded", () => {
    const {
        appTheme = "light",
        appSidebar = "full",
        appColor = "blue",
    } = appSettings;

    document.documentElement.setAttribute(
        "data-bs-theme",
        appTheme === "dark" ? "dark" : "light"
    );

    if (window.innerWidth >= APP_SIDEBAR_BREAKPOINT) {
        const sidebarType = appSidebar === "mini" ? "mini" : "full";
        document.documentElement.setAttribute("data-app-sidebar", sidebarType);
    }

    document.documentElement.setAttribute(
        "data-color-theme",
        appColor === "green" ? "green" : "blue"
    );
});
