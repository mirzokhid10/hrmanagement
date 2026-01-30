<header class="app-header">
    <div class="app-header-inner">
        <!-- Mobile Toggle -->
        <button class="app-toggler" type="button">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <!-- LEFT SIDE: Search & Quick Links -->
        <div class="app-header-start d-flex align-items-center">
            <!-- Search Bar -->
            {{-- {{ route('admin.search') }} --}}
            {{-- <form class="d-none d-md-flex align-items-center h-100 w-lg-250px w-xxl-300px position-relative m-0"
                action="" method="GET">
                <button type="submit" class="btn btn-sm border-0 position-absolute start-0 ms-3 p-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <input type="text" name="q" class="form-control rounded-5 ps-5"
                    placeholder="Search employees, jobs...">
            </form> --}}

            <!-- Quick Links (Visible on large screens) -->
            {{-- <ul class="navbar-nav gap-4 flex-row d-none d-xxl-flex">
                <li class="nav-item">
                    <a class="nav-link" href="#">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Help</a>
                </li>
            </ul> --}}
        </div>

        <!-- RIGHT SIDE: Actions & Profile -->
        <div class="app-header-end">

            <div class="vr my-3"></div>

            <!-- Notifications & Actions -->
            <div class="d-flex align-items-center gap-sm-2 gap-0 px-lg-4 px-sm-2 px-1">

                <!-- Inbox / Messages -->
                {{--    <a href="#"
                    class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative">
                    <i class="fa-regular fa-envelope"></i>
                    <!-- Unread Indicator -->
                    <span
                        class="position-absolute top-0 end-0 p-1 mt-1 me-1 bg-danger border border-3 border-light rounded-circle">
                        <span class="visually-hidden">New messages</span>
                    </span>
                </a>

                <!-- Notifications Dropdown -->
                <div class="dropdown text-end">
                    <a href="javascript:void(0);"
                        class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="fa-regular fa-bell"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-lg-end p-0 w-300px mt-2">
                        <div class="px-3 py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifications</h6>
                            <a href="#" class="text-xs text-muted">Mark all read</a>
                        </div>

                        <div class="p-2" style="height: 300px; overflow-y: auto;" data-simplebar>
                            <ul class="list-group list-group-flush">
                                <!-- Dynamic Notifications Loop -->
                                @php
                                    // Simulating empty or populated notifications.
                                    // Later replace with: auth()->user()->unreadNotifications
                                    $notifications = [];
                                @endphp

                                @forelse($notifications as $notification)
                                    <li class="list-group-item">
                                        <!-- Notification Item Code -->
                                    </li>
                                @empty
                                    <li class="text-center py-5">
                                        <div class="text-muted mb-2"><i class="fa-regular fa-bell-slash fa-2x"></i>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-0">No new notifications</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Calendar Link -->
                <a href="#" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light">
                    <i class="fa-regular fa-calendar-days"></i>
                </a> --}}
                <div class="dropdown text-end">
                    <a href="javascript:void(0);"
                        class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light d-flex align-items-center justify-content-center"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        @if (app()->getLocale() == 'uz')
                            <img src="https://flagcdn.com/w40/uz.png" alt="UZ"
                                style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                        @elseif(app()->getLocale() == 'ru')
                            <img src="https://flagcdn.com/w40/ru.png" alt="RU"
                                style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                        @else
                            <img src="https://flagcdn.com/w40/gb.png" alt="EN"
                                style="width: 20px; height: 15px; object-fit: cover; border-radius: 2px;">
                        @endif
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end p-2 mt-2 shadow-lg border-0">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 rounded {{ app()->getLocale() == 'uz' ? 'active' : '' }}"
                                href="{{ route('admin.language.switch', 'uz') }}">
                                <img src="https://flagcdn.com/w40/uz.png" width="20" class="rounded-1">
                                <span>O'zbekcha</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 rounded {{ app()->getLocale() == 'ru' ? 'active' : '' }}"
                                href="{{ route('admin.language.switch', 'ru') }}">
                                <img src="https://flagcdn.com/w40/ru.png" width="20" class="rounded-1">
                                <span>Русский</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 rounded {{ app()->getLocale() == 'en' ? 'active' : '' }}"
                                href="{{ route('admin.language.switch', 'en') }}">
                                <img src="https://flagcdn.com/w40/gb.png" width="20" class="rounded-1">
                                <span>English</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- END: LANGUAGE SWITCHER -->
            </div>

            <div class="vr my-3"></div>

            <!-- User Profile Dropdown -->
            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
                <a href="javascript:void(0);" class="d-flex align-items-center py-2 text-decoration-none"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <div class="text-end me-2 d-none d-lg-inline-block">
                        <div class="fw-bold text-dark">{{ Auth::user()->name ?? 'Guest User' }}</div>
                        <small class="text-muted d-block lh-sm">
                            {{ Auth::user()->getRoleNames()->first() ?? 'Employee' }}
                        </small>
                    </div>
                    <div class="avatar avatar-sm rounded-circle">
                        <!-- Dynamic Avatar with Fallback to Initials -->
                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'Guest') . '&background=random' }}"
                            alt="user" class="rounded-circle">
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end w-225px mt-1 shadow-lg">
                    <li class="d-flex align-items-center p-3 border-bottom">
                        <div class="avatar avatar-sm rounded-circle">
                            <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'Guest') . '&background=random' }}"
                                alt="user" class="rounded-circle">
                        </div>
                        <div class="ms-2 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate">{{ Auth::user()->name ?? 'Guest' }}</div>
                            <small
                                class="text-muted d-block lh-sm text-truncate">{{ Auth::user()->email ?? '' }}</small>
                        </div>
                    </li>

                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <i class="fa-solid fa-user text-muted w-20px"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <i class="fa-solid fa-list-check text-muted w-20px"></i> My Tasks
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <i class="fa-solid fa-gear text-muted w-20px"></i> Settings
                        </a>
                    </li>

                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                <i class="fa-solid fa-right-from-bracket w-20px"></i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
