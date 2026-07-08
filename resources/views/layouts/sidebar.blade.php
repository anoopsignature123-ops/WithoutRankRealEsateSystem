<aside class="app-sidebar shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#">
            <img src="{{ asset('assets/images/admin.png') }}" alt="Logo" height="50px" width="150px" />
        </a>
    </div>
    @php
        $isAssociate = auth()->guard('associate')->check();
        $isCustomer = auth()->guard('customer')->check();

        if ($isAssociate) {
            $user = auth()->guard('associate')->user();
        } elseif ($isCustomer) {
            $user = auth()->guard('customer')->user();
        } else {
            $user = auth()->user();
        }

        $menus =
            !$isAssociate && !$isCustomer
                ? App\Models\Module::whereNull('parent_id')->with('children')->orderBy('sort_order')->get()
                : collect();
    @endphp

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
                {{-- CUSTOMER MENU --}}
                @if ($isCustomer)

                    <li class="nav-item">
                        <a href="{{ route('customer-panel.dashboard') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer-panel.profile') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.profile') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-circle"></i>
                            <p>My Profile</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customer-panel.booking-history') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.booking-history') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-bookmark"></i>
                            <p>My Booking History</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customer-panel.payment-history') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.payment-history') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-wallet2"></i>
                            <p>My Payment History</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customer-panel.my-plot-booking') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.my-plot-booking') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-house-check"></i>
                            <p>My Plot Booking</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('customer-panel.support') }}"
                            class="nav-link {{ request()->routeIs('customer-panel.support') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-life-preserver"></i>
                            <p>Support</p>
                        </a>
                    </li>
                @elseif($isAssociate)
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
                            <p>
                                Team Management
                                <i class="right bi bi-chevron-right nav-arrow"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-tree') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-tree') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-diagram-3-fill"></i>
                                    <p>Team Tree</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-direct') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-direct') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-person-plus-fill"></i>
                                    <p>Direct Team</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('associate-panel.my-downline') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.my-downline') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-people-fill"></i>
                                    <p>Network Team</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    {{-- <li
                        class="nav-item
                            {{ request()->routeIs('associate-panel.payout-details') ? 'menu-open' : '' }}">

                        <a href="#"
                            class="nav-link
                                {{ request()->routeIs('associate-panel.payout-details') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-cash-stack"></i>
                            <p>My Account <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('associate-panel.payout-details') }}"
                                    class="nav-link {{ request()->routeIs('associate-panel.payout-details*') ? 'active' : '' }}">

                                    <i class="nav-icon bi bi-record-fill"></i>
                                    <p>Payout Details</p>
                                </a>
                            </li>
                        </ul>
                    </li> --}}
                    {{-- <li class="nav-item">
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
                    </li> --}}
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
                                                    <p>
                                                        <span>{{ $child->name }}</span>
                                                        @if ($child->slug === 'multiple-cheque-clearance' && ($chequeClearanceCount ?? 0) > 0)
                                                            <span
                                                                class="sidebar-count-badge">{{ $chequeClearanceCount }}</span>
                                                        @endif
                                                    </p>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @else
                            {{-- Menu item --}}
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
