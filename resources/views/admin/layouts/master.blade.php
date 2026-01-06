<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>
    <!-- begin::GXON Meta Basic -->
    <meta charset="utf-8">
    <meta name="theme-color" content="#316AFF">
    <meta name="robots" content="index, follow">
    <meta name="author" content="LayoutDrop">
    <meta name="format-detection" content="telephone=no">
    <meta name="keywords"
        content="hr dashboard, admin template, hr management, employee management, hr admin panel, gxon bootstrap dashboard, hr software ui, hrm dashboard, bootstrap hr template, responsive, bootstrap hr template, light mode, dark mode">
    <meta name="description"
        content="GXON is a professional and modern HR Management Laravel Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <!-- end::GXON Meta Basic -->

    <!-- begin::GXON Meta Social -->
    <meta property="og:url" content="laravel/demo/index.html">
    <meta property="og:site_name" content="HR Management">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_us">
    <meta property="og:title" content="HR Management">
    <meta property="og:description"
        content="GXON is a professional and modern HR Management Laravel Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <meta property="og:image" content="laravel/demo/preview.html">
    <!-- end::GXON Meta Social -->

    <!-- begin::GXON Meta Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="laravel/demo/index.html">
    <meta name="twitter:creator" content="@layoutdrop">
    <meta name="twitter:title" content="HR Management">
    <meta name="twitter:description"
        content="GXON is a professional and modern HR Management Laravel Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <!-- end::GXON Meta Twitter -->

    <!-- begin::GXON Website Page Title -->
    <title>HR Management</title>
    <!-- end::GXON Website Page Title -->

    <!-- begin::GXON Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end::GXON Mobile Specific -->

    <!-- begin::GXON Favicon Tags -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    <!-- end::GXON Favicon Tags -->

    <!-- begin::GXON Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;display=swap"
        rel="stylesheet">
    <!-- end::GXON Google Fonts -->

    <!-- begin::GXON Required Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
    <!-- end::GXON Required Stylesheet -->

    <!-- begin::GXON CSS Stylesheet -->

    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- end::GXON Required Stylesheet -->


    <!-- begin:: Fontawesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- end:: Fontawesome Icons -->

    @notifyCss
</head>

<body>
    <div class="page-layout">
        <!-- begin::GXON Page Header -->
        @include('admin.layouts.navbar')
        <!-- end::GXON Page Header --> <!-- begin::GXON Sidebar Menu -->
        @include('admin.layouts.sidebar')
        <!-- end::GXON Sidebar Menu -->

        <!-- begin::GXON Sidebar right -->
        <div class="app-sidebar-end">
            <ul class="sidebar-list">
                <li>
                    <a href="task-management.html">
                        <div
                            class="avatar avatar-sm bg-warning shadow-sharp-warning rounded-circle text-white mx-auto mb-2">
                            <i class="fi fi-rr-to-do"></i>
                        </div>
                        <span class="text-dark">Task</span>
                    </a>
                </li>
                <li>
                    <a href="faqs.html">
                        <div
                            class="avatar avatar-sm bg-secondary shadow-sharp-secondary rounded-circle text-white mx-auto mb-2">
                            <i class="fi fi-rr-interrogation"></i>
                        </div>
                        <span class="text-dark">Help</span>
                    </a>
                </li>
                <li>
                    <a href="calendar.html">
                        <div class="avatar avatar-sm bg-info shadow-sharp-info rounded-circle text-white mx-auto mb-2">
                            <i class="fi fi-rr-calendar"></i>
                        </div>
                        <span class="text-dark">Event</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);">
                        <div class="avatar avatar-sm bg-gray shadow-sharp-gray rounded-circle text-white mx-auto mb-2">
                            <i class="fi fi-rr-settings"></i>
                        </div>
                        <span class="text-dark">Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- end::GXON Sidebar right -->
        <main class="app-wrapper">
            <div class="container">

                @yield('content')

            </div>
        </main>

        <!-- begin::GXON Footer -->
        <footer class="footer-wrapper bg-body">
            <div class="container">
                <div class="row g-2">
                    <div class="col-lg-6 col-md-7 text-center text-md-start">
                        <p class="mb-0">© <span class="currentYear">2025</span> GXON. Proudly powered by <a
                                href="javascript:void(0);">LayoutDrop</a>.</p>
                    </div>
                    <div class="col-lg-6 col-md-5">
                        <ul
                            class="d-flex list-inline mb-0 gap-3 flex-wrap justify-content-center justify-content-md-end">
                            <li>
                                <a class="text-body" href="https://gxon.layoutdrop.com/laravel/demo">Home</a>
                            </li>
                            <li>
                                <a class="text-body" href="faqs.html">Faq's</a>
                            </li>
                            <li>
                                <a class="text-body" href="faqs.html">Support</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end::GXON Footer -->
    </div>

    <!-- Add notification component -->
    <x-notify::notify />

    <!-- Add Laravel Notify JavaScript -->
    @notifyJs
    <!-- begin::GXON Page Scripts -->
    <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('assets/libs/sortable/Sortable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/chartjs/chart.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    <script src="{{ asset('assets/js/appSettings.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- end::GXON Page Scripts -->
</body>

<!-- Mirrored from gxon.layoutdrop.com/laravel/demo/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 30 Dec 2025 12:17:19 GMT -->

</html>
