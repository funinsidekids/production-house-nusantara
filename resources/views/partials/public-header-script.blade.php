<script>
    (() => {
        const topBar = document.getElementById('publicTopBar');
        const headerNav = document.getElementById('publicHeaderNav');
        const headerLogo = document.getElementById('publicHeaderLogoImage');
        const mobileToggle = document.getElementById('publicMobileToggle');
        const mobileMenu = document.getElementById('publicMobileMenu');
        const mobileOverlay = document.getElementById('publicMobileOverlay');

        if (!(headerNav instanceof HTMLElement)) {
            return;
        }

        const isStickyEnabled = headerNav.dataset.stickyOnScroll === '1';
        const isShrinkEnabled = headerNav.dataset.shrinkOnScroll === '1';
        const mobileMode = headerNav.dataset.mobileMode || 'drawer';

        const syncOffsets = () => {
            const topHeight = topBar instanceof HTMLElement ? topBar.offsetHeight : 0;
            headerNav.style.top = `${topHeight}px`;
            if (mobileMenu instanceof HTMLElement && mobileMode === 'dropdown') {
                const navHeight = headerNav.offsetHeight;
                mobileMenu.style.top = `${topHeight + navHeight}px`;
            }
        };

        const applyHeaderMode = () => {
            const scrolled = window.scrollY > 10;
            if (isStickyEnabled) {
                headerNav.classList.toggle('is-sticky', scrolled);
            }
            if (isShrinkEnabled) {
                headerNav.classList.toggle('is-shrunk', scrolled);
            }
            if (headerLogo instanceof HTMLImageElement) {
                const primary = headerLogo.dataset.primaryLogo || '';
                const sticky = headerLogo.dataset.stickyLogo || primary;
                const nextLogo = scrolled ? sticky : primary;
                if (nextLogo !== '' && headerLogo.src !== nextLogo) {
                    headerLogo.src = nextLogo;
                }
            }
        };

        const closeMobileMenu = () => {
            if (mobileMenu instanceof HTMLElement) {
                mobileMenu.classList.remove('open');
            }
            if (mobileOverlay instanceof HTMLElement) {
                mobileOverlay.classList.remove('open');
            }
        };

        mobileToggle?.addEventListener('click', () => {
            if (!(mobileMenu instanceof HTMLElement) || !(mobileOverlay instanceof HTMLElement)) {
                return;
            }
            const willOpen = !mobileMenu.classList.contains('open');
            mobileMenu.classList.toggle('open', willOpen);
            mobileOverlay.classList.toggle('open', willOpen);
        });

        mobileOverlay?.addEventListener('click', closeMobileMenu);
        mobileMenu?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMobileMenu);
        });

        window.addEventListener('scroll', applyHeaderMode, { passive: true });
        window.addEventListener('resize', syncOffsets);
        syncOffsets();
        applyHeaderMode();
    })();
</script>
