@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                © <script>
                document.write(new Date().getFullYear())
                </script> {{ config('variables.creatorName') }}
            </div>
            <div class="d-none d-lg-inline-block">
                <a href="/" class="footer-link me-4">Landing Page</a>
                <a href="/api/admin/dashboard" class="footer-link me-4" target="_blank">Dashboard API</a>
                <a href="/api/landing/content" class="footer-link">Landing API</a>
            </div>
        </div>
    </div>
</footer>
<!--/ Footer-->
