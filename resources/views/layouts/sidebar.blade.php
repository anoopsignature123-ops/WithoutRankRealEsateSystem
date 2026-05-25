<aside class="app-sidebar shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" class="brand-link">
            <img src="{{ asset('assets/images/AdminLTELogo.png') }}" alt="Logo"
                class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">
                {{ auth()->guard('associate')->check() ? 'Associate Panel' : 'Admin Panel' }}
            </span>
        </a>
    </div>

    @php
        $isAssociate = auth()->guard('associate')->check();
        $user = $isAssociate ? auth()->guard('associate')->user() : auth()->user();
        $menus = !$isAssociate
            ? App\Models\Module::whereNull('parent_id')->with('children')->orderBy('sort_order')->get()
            : collect();
    @endphp

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">

                @if ($isAssociate)

                    <li class="nav-item">
                        <a href="{{ route('associate-panel.dashboard') }}"
                            class="nav-link {{ request()->routeIs('associate.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li
                        class="nav-item {{ request()->is('associate-panel/view-profile') || request()->is('associate-panel/edit-profile') || request()->is('associate-panel/change-password') || request()->is('associate-panel.welcome-letter') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('associate-panel/view-profile') || request()->is('associate-panel/edit-profile') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-lines-fill"></i>
                            <p>
                                Profile
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.view-profile') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.view-profile') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>View Profile</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.edit-profile') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.edit-profile') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Edit Profile</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.change-password') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.change-password') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Change Password</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.welcome-letter') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.welcome-letter') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Welcome Letter</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item {{ request()->routeIs('associate-panel.*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->routeIs('associate-panel.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-briefcase"></i>
                            <p>
                                Business Details
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.register-create') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.register-create') || request()->routeIs('associate-panel.associate-edit') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-person-plus"></i>
                                    <p>New Registration</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.associate-detail') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.associate-detail') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Associate Details</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.booking-detail') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.booking-detail') || request()->routeIs('associate-panel.team-business-report') || request()->routeIs('associate-panel.due-emi-amount') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Booking Details</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.customer-ledger') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.customer-ledger') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Customer Ledger</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-people"></i>
                            <p>
                                Team
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>My Downline</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>My Direct</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>My Tree View</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-envelope"></i>
                            <p>
                                My Account
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Payout Details</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-laptop"></i>
                            <p>
                                Pin Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>View E-Pins</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-calendar3"></i>
                            <p>Plot Availbility</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-life-preserver"></i>
                            <p>
                                Support
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Send Enquiry</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-circle-fill "></i>
                                    <p>Enquiry Details</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    @foreach ($menus as $menu)
                        @if ($menu->children->count() > 0)
                            @php
                                $visibleChildren = $menu->children->filter(function ($child) use ($user) {
                                    return $user->can($child->slug . '-list');
                                });
                                $parentActive = $visibleChildren->contains(function ($child) {
                                    return request()->routeIs(...explode(',', $child->active_routes));
                                });
                            @endphp
                            @if ($visibleChildren->count() > 0)
                                <li class="nav-item {{ $parentActive ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ $parentActive ? 'active' : '' }}">
                                        <i class="nav-icon {{ $menu->icon }}"></i>
                                        <p>
                                            {{ $menu->name }}
                                            <i class="nav-arrow bi bi-chevron-right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @foreach ($visibleChildren as $child)
                                            <li class="nav-item">
                                                <a href="{{ $child->route_name && Route::has($child->route_name) ? route($child->route_name) : '#' }}"
                                                    class="nav-link {{ request()->routeIs(...explode(',', $child->active_routes)) ? 'active' : '' }}">
                                                    <i class="nav-icon {{ $child->icon }}"></i>
                                                    <p>{{ $child->name }}</p>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @else
                            {{-- Single Menu --}}
                            @if ($user->can($menu->slug . '-list'))
                                <li class="nav-item">
                                    <a href="{{ $menu->route_name && Route::has($menu->route_name) ? route($menu->route_name) : '#' }}"
                                        class="nav-link {{ request()->routeIs(...explode(',', $menu->active_routes)) ? 'active' : '' }}">
                                        <i class="nav-icon {{ $menu->icon }}"></i>
                                        <p>{{ $menu->name }}</p>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                @endif
            </ul>
        </nav>
    </div>
</aside>
