<aside class="app-menubar" id="menubar">
    <!-- Brand Logo -->
    <div class="app-navbar-brand">
        <a class="navbar-brand-logo" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/logo.svg') }}" alt="logo">
        </a>
        <a class="navbar-brand-mini visible-light" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/images/logo-text.svg') }}" alt="logo">
        </a>
    </div>

    <!-- Scrollable Menu -->
    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">

            <!-- SECTION: MAIN -->
            <li class="menu-heading"><span class="menu-label">Main</span></li>

            <x-nav-item route="admin.dashboard" icon="fa-solid fa-table-columns" label="Dashboard" />

            <!-- SECTION: PEOPLE & TIME -->
            <li class="menu-heading"><span class="menu-label">People & Time</span></li>

            <x-nav-item route="admin.employee.index" icon="fa-solid fa-users" label="Employees" />

            <x-nav-item route="admin.attendance.index" icon="fa-solid fa-clock" label="Attendance" />

            <!-- Using a plane icon for Time Off (Vacation) looks better in HR apps -->
            <x-nav-item route="admin.time-offs.index" icon="fa-solid fa-plane-departure" label="Time Off" />

            <!-- SECTION: RECRUITMENT -->
            @role('admin|hr-manager')
                <li class="menu-heading"><span class="menu-label">Recruitment</span></li>

                <li class="menu-item menu-arrow {{ request()->routeIs('admin.recruitment.*') ? 'open' : '' }}">
                    <a class="menu-link" href="javascript:void(0);" role="button">
                        <i class="fa-solid fa-briefcase"></i>
                        <span class="menu-label">Hiring</span>
                    </a>
                    <ul class="menu-inner" style="{{ request()->routeIs('admin.recruitment.*') ? 'display: block;' : '' }}">

                        <x-nav-item route="admin.recruitment.index" icon="fa-solid fa-clipboard-list" label="Vacancies" />

                        <x-nav-item route="admin.candidates.index" icon="fa-solid fa-user-tie" label="Candidates" />

                        <x-nav-item route="admin.onboarding.index" icon="fa-solid fa-list-check" label="Onboarding" />
                    </ul>
                </li>
            @endrole

            <!-- SECTION: ORGANIZATION -->
            <li class="menu-heading"><span class="menu-label">Organization</span></li>

            <x-nav-item route="admin.announcements.index" icon="fa-solid fa-bullhorn" label="Announcements" />

            <x-nav-item route="admin.document.policies" icon="fa-solid fa-file-contract" label="Documents & Policies" />

            <!-- SECTION: FINANCE -->
            @role('admin')
                <li class="menu-heading"><span class="menu-label">Finance</span></li>

                <x-nav-item route="admin.payroll.index" icon="fa-solid fa-money-bill-wave" label="Payroll" />
            @endrole

            <!-- SECTION: SYSTEM -->
            @role('admin')
                <li class="menu-heading"><span class="menu-label">System</span></li>

                <li class="menu-item menu-arrow {{ request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                    <a class="menu-link" href="javascript:void(0);" role="button">
                        <i class="fa-solid fa-gears"></i>
                        <span class="menu-label">Settings</span>
                    </a>
                    <ul class="menu-inner" style="{{ request()->routeIs('admin.settings.*') ? 'display: block;' : '' }}">

                        <x-nav-item route="admin.office-location.index" icon="fa-solid fa-map-location-dot"
                            label="Office Locations" />

                        <x-nav-item route="admin.settings.roles" icon="fa-solid fa-shield-halved"
                            label="Roles & Permissions" />

                        <!-- Robot icon for your AI/Bot integrations -->
                        <x-nav-item route="admin.settings.integrations" icon="fa-solid fa-robot" label="Integrations" />
                    </ul>
                </li>
            @endrole

        </ul>
    </nav>

    <!-- Footer -->
    <div class="app-footer">
        <a href="#" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
            <i class="fa-solid fa-circle-question text-primary"></i>
            <span class="nav-text">Help Center</span>
        </a>
    </div>
</aside>
