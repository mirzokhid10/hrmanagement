<!DOCTYPE html>
<html lang="en">

<head>
    <base href="https://gxon.layoutdrop.com/laravel/demo">

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
    <meta property="og:url" content="https://gxon.layoutdrop.com/laravel/demo/demo/">
    <meta property="og:site_name" content="GXON | HR Management Laravel Admin Dashboard Template">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_us">
    <meta property="og:title" content="GXON | HR Management Laravel Admin Dashboard Template">
    <meta property="og:description"
        content="GXON is a professional and modern HR Management Laravel Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <meta property="og:image" content="https://gxon.layoutdrop.com/laravel/demo/demo/preview.png">
    <!-- end::GXON Meta Social -->

    <!-- begin::GXON Meta Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="https://gxon.layoutdrop.com/laravel/demo/demo/">
    <meta name="twitter:creator" content="@layoutdrop">
    <meta name="twitter:title" content="GXON | HR Management Laravel Admin Dashboard Template">
    <meta name="twitter:description"
        content="GXON is a professional and modern HR Management Laravel Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
    <!-- end::GXON Meta Twitter -->

    <!-- begin::GXON Website Page Title -->
    <title>GXON | HR Management Laravel Admin Dashboard Template</title>
    <!-- end::GXON Website Page Title -->

    <!-- begin::GXON Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- end::GXON Mobile Specific -->

    <!-- begin::GXON Favicon Tags -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
    <!-- end::GXON Favicon Tags -->

    <!-- begin::GXON Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
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
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <!-- end::GXON CSS Stylesheet -->
</head>

<body>
    <div class="page-layout">
        <div class="auth-cover-wrapper">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="auth-cover"
                        style="background-image: url(https://gxon.layoutdrop.com/laravel/demo/assets/images/auth/auth-cover-bg.png;">
                        <div class="clearfix">
                            <img src="https://gxon.layoutdrop.com/laravel/demo/assets/images/auth/auth.png"
                                alt="" class="img-fluid cover-img ms-5">
                            <div class="auth-content">
                                <h1 class="display-6 fw-bold">Welcome Back!</h1>
                                <p>Our HR Management & Administration ensure your organization runs smoothly, focusing
                                    on people, compliance, and efficiency to drive growth and employee satisfaction.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="p-3 p-sm-5 maxw-450px m-auto">
                        <div class="mb-4 text-center">
                            <a href="https://gxon.layoutdrop.com/laravel/demo" aria-label="GXON logo">
                                <img class="visible-light"
                                    src="https://gxon.layoutdrop.com/laravel/demo/assets/images/logo-full.svg"
                                    alt="GXON logo">
                                <img class="visible-dark"
                                    src="https://gxon.layoutdrop.com/laravel/demo/assets/images/logo-full-white.svg"
                                    alt="GXON logo">
                            </a>
                        </div>
                        <div class="text-center mb-5">
                            <h5 class="mb-1">Welcome to GXON</h5>
                            <p>Sign in to access your secure admin dashboard.</p>
                        </div>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-4">
                                <x-input-label class="form-label" for="email" :value="__('Email Address')" />
                                <x-text-input id="email" class="form-control" type="email" name="email"
                                    :value="old('email')" required autofocus autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <x-input-label for="password" :value="__('Password')" />

                                <x-text-input id="password" class="form-control" type="password" name="password"
                                    required autocomplete="current-password" />

                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0">
                                        <label for="remember_me" class="inline-flex items-center">
                                            <input id="remember_me" type="checkbox"
                                                class="form-check-input border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                name="remember">
                                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                        </label>
                                    </div>
                                    <a href="https://gxon.layoutdrop.com/laravel/demo/forgot-password-basic">Forgot
                                        Password?</a>
                                </div>
                            </div>
                            <div class="mb-3">

                                <x-primary-button class="btn btn-primary waves-effect waves-light w-100">
                                    {{ __('Log in') }}
                                </x-primary-button>
                            </div>
                            <p class="mb-5 text-center">Don’t have an account? <a href="{{ route('register') }}">Sign
                                    Up here</a>
                            </p>
                            <div class="border-bottom position-relative my-3 text-center">
                                <span class="px-3 position-absolute translate-middle top-50 start-50 bg-body">Or
                                    Continue With</span>
                            </div>
                            <div class="d-flex gap-2 justify-content-center mt-5">
                                <a href="javascript:void(0);"
                                    class="btn btn-icon btn-subtle-facebook rounded-circle waves-effect waves-light">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <a href="javascript:void(0);"
                                    class="btn btn-icon btn-subtle-twitter rounded-circle waves-effect waves-light">
                                    <i class="fa-brands fa-x-twitter"></i>
                                </a>
                                <a href="javascript:void(0);"
                                    class="btn btn-icon btn-subtle-github rounded-circle waves-effect waves-light">
                                    <i class="fa-brands fa-github"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- begin::GXON Page Scripts -->
    <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('assets/js/appSettings.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <!-- end::GXON Page Scripts -->
</body>

</html>
