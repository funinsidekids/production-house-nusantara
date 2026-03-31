@php
$brandPrimary = (string) ($dashboardBrandLogos['primary'] ?? ($dashboardBrandLogo ?? ''));
$brandSticky = (string) ($dashboardBrandLogos['sticky'] ?? $brandPrimary);
$headerPosition = (string) ($dashboardBrandLogos['header_position'] ?? 'left');
$headerJustifyClass = $headerPosition === 'center' ? 'justify-content-center' : ($headerPosition === 'right' ? 'justify-content-end' : 'justify-content-start');
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{url('/')}}" class="app-brand-link gap-2 w-100 {{ $headerJustifyClass }}">
        <span class="app-brand-logo demo">
            @if ($brandPrimary !== '')
                <img
                    id="dashboardNavbarBrandLogo"
                    src="{{ $brandPrimary }}"
                    data-primary-logo="{{ $brandPrimary }}"
                    data-sticky-logo="{{ $brandSticky !== '' ? $brandSticky : $brandPrimary }}"
                    alt="PH NUSANTARA"
                    style="height: 30px; width: auto; object-fit: contain;"
                >
            @else
                @include('_partials.macros')
            @endif
        </span>
    </a>
</div>
@endif

@once
<script>
    (() => {
        const brandLogo = document.getElementById('dashboardNavbarBrandLogo');
        if (!(brandLogo instanceof HTMLImageElement)) {
            return;
        }
        const primaryLogo = brandLogo.dataset.primaryLogo || '';
        const stickyLogo = brandLogo.dataset.stickyLogo || primaryLogo;
        if (primaryLogo === '') {
            return;
        }
        const applyLogoMode = () => {
            const useSticky = window.scrollY > 10;
            const target = useSticky ? stickyLogo : primaryLogo;
            if (target !== '' && brandLogo.src !== target) {
                brandLogo.src = target;
            }
        };
        window.addEventListener('scroll', applyLogoMode, { passive: true });
        applyLogoMode();
    })();
</script>
@endonce

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="icon-base bx bx-search icon-md"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search module..." aria-label="Search module...">
        </div>
    </div>
    <!-- /Search -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <li class="nav-item lh-1 me-4">
            <a class="btn btn-sm btn-primary" href="/">Landing Page</a>
        </li>

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Studio Admin</h6>
                                <small class="text-muted">Panel Admin</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base bx bx-home icon-md me-3"></i><span>Dashboard Home</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="/api/admin/dashboard" target="_blank">
                        <i class="icon-base bx bx-data icon-md me-3"></i><span>Dashboard API</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="/api/landing/content" target="_blank">
                        <i class="icon-base bx bx-world icon-md me-3"></i><span>Landing API</span>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="/">
                        <i class="icon-base bx bx-left-arrow-alt icon-md me-3"></i><span>Kembali ke Landing</span>
                    </a>
                </li>
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>
