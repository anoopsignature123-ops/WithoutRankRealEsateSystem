<aside class="app-sidebar shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" >
            <img src="{{ asset('assets/images/admin.png') }}" alt="Logo" height="50px" width="150px" />
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
                            class="nav-link {{ request()->routeIs('associate-panel.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li
                        class="nav-item
                            {{ request()->routeIs('associate-panel.view-profile') ||
                            request()->routeIs('associate-panel.edit-profile') ||
                            request()->routeIs('associate-panel.change-password') ||
                            request()->routeIs('associate-panel.welcome-letter')
                                ? 'menu-open'
                                : '' }}">
                        <a href="#"
                            class="nav-link
                            {{ request()->routeIs('associate-panel.view-profile') ||
                            request()->routeIs('associate-panel.edit-profile') ||
                            request()->routeIs('associate-panel.change-password') ||
                            request()->routeIs('associate-panel.welcome-letter')
                                ? 'active'
                                : '' }}">
                            <i class="nav-icon bi bi-person-lines-fill"></i>
                            <p>Profile<i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.view-profile') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.view-profile') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>View Profile</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.edit-profile') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.edit-profile') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Edit Profile</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.change-password') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.change-password') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Change Password</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.welcome-letter') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.welcome-letter') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Welcome Letter</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li
                        class="nav-item
                            {{ request()->routeIs('associate-panel.register-create') ||
                            request()->routeIs('associate-panel.associate-edit') ||
                            request()->routeIs('associate-panel.associate-detail') ||
                            request()->routeIs('associate-panel.booking-detail') ||
                            request()->routeIs('associate-panel.customer-ledger')
                                ? 'menu-open'
                                : '' }}">
                        <a href="#"
                            class="nav-link
                                {{ request()->routeIs('associate-panel.register-create') ||
                                request()->routeIs('associate-panel.associate-edit') ||
                                request()->routeIs('associate-panel.associate-detail') ||
                                request()->routeIs('associate-panel.booking-detail') ||
                                request()->routeIs('associate-panel.customer-ledger')
                                    ? 'active'
                                    : '' }}">
                            <i class="nav-icon bi bi-briefcase"></i>
                            <p>Business Details
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.register-create') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.register-create') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>New Registration</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.associate-detail') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.associate-detail') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Associate Details</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.booking-detail') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.booking-detail') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Booking Details</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.customer-ledger') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.customer-ledger') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Customer Ledger</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li
                        class="nav-item
                            {{ request()->routeIs('associate-panel.my-tree') ||
                            request()->routeIs('associate-panel.my-direct') ||
                            request()->routeIs('associate-panel.my-downline')
                                ? 'menu-open'
                                : '' }}">

                        <a href="#"
                            class="nav-link
                                {{ request()->routeIs('associate-panel.my-tree') ||
                                request()->routeIs('associate-panel.my-direct') ||
                                request()->routeIs('associate-panel.my-downline')
                                    ? 'active'
                                    : '' }}">

                            <i class="nav-icon bi bi-people"></i>
                            <p>Team<i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-tree') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-tree') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>My Tree View</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-direct') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-direct') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>My Direct</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-downline') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-downline') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>My Downline</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-envelope"></i>
                            <p>My Account<i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-record-fill "></i>
                                    <p>Payout Details</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-laptop"></i>
                            <p>Pin Management<i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon bi bi-record-fill "></i>
                                    <p>View E-Pins</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('associate-panel.plot-avilable') }}"
                            class="nav-link {{ request()->routeIs('associate-panel.plot-avilable') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-grid"></i>
                            <p>Plot Availability</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('associate-panel.support.index') }}"
                            class="nav-link {{ request()->routeIs('associate-panel.support.index') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-life-preserver"></i>
                            <p>Support</p>
                        </a>
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
