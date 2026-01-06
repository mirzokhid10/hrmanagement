<aside class="app-menubar" id="menubar">
    <div class="app-navbar-brand">
        <a class="navbar-brand-logo" href="">
            <img src="{{ asset('assets/images/logo.svg') }}" alt="logo">
        </a>
        <a class="navbar-brand-mini visible-light" href="">
            <img src="{{ asset('assets/images/logo-text.svg') }}" alt="logo">
        </a>
        <a class="navbar-brand-mini visible-dark" href="">
            <img src="assets/images/logo-text-white.svg" alt="logo">
        </a>
    </div>
    <nav class="app-navbar" data-simplebar>
        <ul class="menubar">
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fa-solid fa-table-cells-large"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('admin.dashboard') }}">
                            <span class="menu-label">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('admin.employee.index') }}">
                            <span class="menu-label">Employee</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('admin.time-offs.index') }}">
                            <span class="menu-label">Time Offs</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="">
                            <span class="menu-label">Attendance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="leave.html">
                            <span class="menu-label">Leave</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="payroll.html">
                            <span class="menu-label">Payroll</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="recruitment.html">
                            <span class="menu-label">Recruitment</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="task-management.html">
                            <span class="menu-label">Task Management</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="analytics.html">
                            <span class="menu-label">Analytics</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Apps & Pages</span>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="chat.html">
                    <i class="fi fi-rr-comment"></i>
                    <span class="menu-label">Chat</span>
                </a>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="calendar.html">
                    <i class="fi fi-rr-calendar"></i>
                    <span class="menu-label">Calendar</span>
                </a>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-envelope"></i>
                    <span class="menu-label">Email</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="inbox.html">
                            <span class="menu-label">Inbox</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="compose.html">
                            <span class="menu-label">Compose</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="read-email.html">
                            <span class="menu-label">Read email</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-file"></i>
                    <span class="menu-label">Pages</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="pricing.html">
                            <span class="menu-label">Pricing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="faqs.html">
                            <span class="menu-label">FAQ's</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="coming-soon.html">
                            <span class="menu-label">Coming Soon</span>
                        </a>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Blog</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="blog-grid.html">
                                    <span class="menu-label">Blog Grid</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="blog-list.html">
                                    <span class="menu-label">Blog List</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="blog-details.html">
                                    <span class="menu-label">Blog Details</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Error</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="error-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="error-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="error-full.html">
                                    <span class="menu-label">Full</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Under Construction</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="construction-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="construction-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="construction-full.html">
                                    <span class="menu-label">Full</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-user-key"></i>
                    <span class="menu-label">Authentication</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Login</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="login-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="login-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="login-frame.html">
                                    <span class="menu-label">Frame</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Register</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="register-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="register-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="register-frame.html">
                                    <span class="menu-label">Frame</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Forgot Password</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="forgot-password-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="forgot-password-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="forgot-password-frame.html">
                                    <span class="menu-label">Frame</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">New Password</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="new-password-basic.html">
                                    <span class="menu-label">Basic</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="new-password-cover.html">
                                    <span class="menu-label">Cover</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="new-password-frame.html">
                                    <span class="menu-label">Frame</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Components</span>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-flux-capacitor"></i>
                    <span class="menu-label">UI Components</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="accordion.html">
                            <span class="menu-label">Accordion</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="alerts.html">
                            <span class="menu-label">Alerts</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="badge.html">
                            <span class="menu-label">Badge</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="breadcrumb.html">
                            <span class="menu-label">Breadcrumb</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="buttons.html">
                            <span class="menu-label">Buttons</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="typography.html">
                            <span class="menu-label">Typography</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="button-group.html">
                            <span class="menu-label">Button Group</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="card.html">
                            <span class="menu-label">Card</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="collapse.html">
                            <span class="menu-label">Collapse</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="carousel.html">
                            <span class="menu-label">Carousel</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="dropdowns.html">
                            <span class="menu-label">Dropdowns</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="modal.html">
                            <span class="menu-label">Modal</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="navbar.html">
                            <span class="menu-label">Navbar</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="list-group.html">
                            <span class="menu-label">List Group</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="tabs.html">
                            <span class="menu-label">Tabs</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="offcanvas.html">
                            <span class="menu-label">Offcanvas</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="pagination.html">
                            <span class="menu-label">Pagination</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="popovers.html">
                            <span class="menu-label">Popovers</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="progress.html">
                            <span class="menu-label">Progress</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="scrollspy.html">
                            <span class="menu-label">Scrollspy</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="spinners.html">
                            <span class="menu-label">Spinners</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="toasts.html">
                            <span class="menu-label">Toasts</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="tooltips.html">
                            <span class="menu-label">Tooltips</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-apps-add"></i>
                    <span class="menu-label">Extended UI</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="avatar.html">
                            <span class="menu-label">Avatar</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="card-action.html">
                            <span class="menu-label">Card action</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="drag-drop.html">
                            <span class="menu-label">Drag & drop</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="simplebar.html">
                            <span class="menu-label">Simplebar</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="swiper.html">
                            <span class="menu-label">Swiper</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="team.html">
                            <span class="menu-label">Team</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-bolt"></i>
                    <span class="menu-label">Icons</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="flaticon.html">
                            <span class="menu-label">Flaticon</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="lucide.html">
                            <span class="menu-label">Lucide</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="fontawesome.html">
                            <span class="menu-label">Font Awesome</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Forms & Tables</span>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-form"></i>
                    <span class="menu-label">Form Elements</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="form-elements.html">
                            <span class="menu-label">Form Elements</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="form-floating.html">
                            <span class="menu-label">Form floating</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="form-input-group.html">
                            <span class="menu-label">Form input group</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="form-layout.html">
                            <span class="menu-label">Form layout</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="form-validation.html">
                            <span class="menu-label">Form validation</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="flatpickr.html">
                            <span class="menu-label">Flatpickr</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="tagify.html">
                            <span class="menu-label">Tagify</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-table-layout"></i>
                    <span class="menu-label">Table</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="table.html">
                            <span class="menu-label">Table</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="datatable.html">
                            <span class="menu-label">Datatable</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Charts & Maps</span>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-chart-pie-alt"></i>
                    <span class="menu-label">Charts</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="apex-chart.html">
                            <span class="menu-label">Apex Chart</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="chart-js.html">
                            <span class="menu-label">Chart JS</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rr-marker"></i>
                    <span class="menu-label">Maps</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item">
                        <a class="menu-link" href="vector-map.html">
                            <span class="menu-label">JS Vector Map</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="leaflet.html">
                            <span class="menu-label">Leaflet</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="menu-heading">
                <span class="menu-label">Others</span>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="javascript:void(0);">
                    <i class="fi fi-rs-badget-check-alt"></i>
                    <span class="menu-label">Badge</span>
                    <span class="badge badge-sm rounded-pill bg-secondary ms-2 float-end">5</span>
                </a>
            </li>
            <li class="menu-item menu-arrow">
                <a class="menu-link" href="javascript:void(0);" role="button">
                    <i class="fi fi-rs-floor-layer"></i>
                    <span class="menu-label">Multi Level</span>
                </a>
                <ul class="menu-inner">
                    <li class="menu-item menu-arrow">
                        <a class="menu-link" href="javascript:void(0);">
                            <span class="menu-label">Multi Level 2</span>
                        </a>
                        <ul class="menu-inner">
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <span class="menu-label">Multi Level 3</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <span class="menu-label">Multi Level 3</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="javascript:void(0);">
                                    <span class="menu-label">Multi Level 3</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <div class="app-footer">
        <a href="faqs.html" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
            <i class="fi fi-rs-interrogation text-primary"></i>
            <span class="nav-text">Help and Support</span>
        </a>
    </div>
</aside>
