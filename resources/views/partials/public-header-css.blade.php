        .public-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 55;
            background: rgba(0, 0, 0, .72);
            border-bottom: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(10px);
        }
        .public-topbar-inner {
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: .8rem;
            color: rgba(255, 255, 255, .88);
        }
        .public-topbar-quick {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }
        .public-topbar-quick a,
        .public-topbar-actions a {
            color: inherit;
            text-decoration: none;
        }
        .public-topbar-actions {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
        }
        .public-topbar-social {
            display: inline-flex;
            gap: .5rem;
        }
        .public-topbar-social a {
            padding: .1rem .45rem;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 999px;
        }
        .public-topbar-lang {
            display: inline-flex;
            gap: .3rem;
        }
        .public-header-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 54;
            border-bottom: 1px solid rgba(255, 255, 255, .14);
            transition: background .25s ease, box-shadow .25s ease, transform .25s ease, min-height .25s ease;
            min-height: 76px;
        }
        .public-header-nav.layout-transparent {
            background: rgba(3, 3, 3, .2);
            backdrop-filter: blur(8px);
        }
        .public-header-nav.layout-solid,
        .public-header-nav.is-sticky {
            background: rgba(7, 7, 7, .86);
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .32);
        }
        .public-header-nav.is-shrunk {
            min-height: 62px;
        }
        .public-header-nav .public-nav-inner {
            min-height: inherit;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            justify-content: space-between;
        }
        .public-brand {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            text-decoration: none;
            color: #fff;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: .92rem;
            white-space: nowrap;
        }
        .public-brand img {
            height: 34px;
            width: auto;
            object-fit: contain;
        }
        .public-nav-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            gap: .9rem;
        }
        .public-nav-item {
            position: relative;
        }
        .public-nav-link {
            color: rgba(255, 255, 255, .9);
            text-decoration: none;
            font-size: .9rem;
            font-weight: 600;
            padding: .45rem .35rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }
        .public-nav-item:hover > .public-nav-link {
            color: #fff;
        }
        .public-nav-caret {
            font-size: .75rem;
            opacity: .72;
        }
        .public-dropdown,
        .public-mega {
            position: absolute;
            top: calc(100% + .4rem);
            left: 0;
            min-width: 220px;
            background: rgba(11, 11, 11, .95);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: .65rem;
            padding: .6rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: all .2s ease;
            z-index: 8;
        }
        .public-nav-item:hover > .public-dropdown,
        .public-nav-item:hover > .public-mega {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .public-dropdown a,
        .public-mega a {
            color: rgba(255, 255, 255, .92);
            text-decoration: none;
            font-size: .85rem;
            display: block;
            padding: .35rem .4rem;
            border-radius: .4rem;
        }
        .public-dropdown a:hover,
        .public-mega a:hover {
            background: rgba(255, 255, 255, .12);
        }
        .public-dropdown ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .public-dropdown .public-sub-list {
            margin-left: .4rem;
            border-left: 1px dashed rgba(255, 255, 255, .18);
            padding-left: .45rem;
        }
        .public-mega {
            min-width: 520px;
        }
        .public-mega-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
        }
        .public-mega-col {
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: .5rem;
            padding: .45rem;
            background: rgba(255, 255, 255, .03);
        }
        .public-mega-col strong {
            font-size: .82rem;
            color: #fff;
        }
        .public-header-actions {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
        }
        .public-cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .6rem 1rem;
            border-radius: 999px;
            font-size: .86rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .public-cta-btn.color-gold {
            background: linear-gradient(135deg, #f3c469, #d89a2b);
            color: #201102;
        }
        .public-cta-btn.color-orange {
            background: linear-gradient(135deg, #f0a341, #df6a2d);
            color: #220d00;
        }
        .public-cta-btn.color-white {
            background: #fff;
            color: #0a0a0a;
        }
        .public-mobile-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: .65rem;
            border: 1px solid rgba(255, 255, 255, .22);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            font-size: 1.05rem;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .public-mobile-menu {
            display: none;
            position: fixed;
            z-index: 53;
            left: 0;
            right: 0;
            background: rgba(5, 5, 5, .96);
            border-top: 1px solid rgba(255, 255, 255, .14);
            border-bottom: 1px solid rgba(255, 255, 255, .14);
        }
        .public-mobile-menu.mode-drawer {
            top: 0;
            bottom: 0;
            right: auto;
            width: min(340px, 92vw);
            transform: translateX(-100%);
            transition: transform .25s ease;
            display: block;
            border-right: 1px solid rgba(255, 255, 255, .15);
        }
        .public-mobile-menu.mode-dropdown {
            transform: translateY(-10px);
            opacity: 0;
            visibility: hidden;
            transition: all .2s ease;
            display: block;
        }
        .public-mobile-menu.open.mode-drawer {
            transform: translateX(0);
        }
        .public-mobile-menu.open.mode-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .public-mobile-menu-inner {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            height: 100%;
            overflow: auto;
        }
        .public-mobile-item {
            color: rgba(255, 255, 255, .95);
            text-decoration: none;
            padding: .62rem .4rem;
            border-bottom: 1px dashed rgba(255, 255, 255, .12);
            font-size: .92rem;
        }
        .public-mobile-children {
            margin-left: .6rem;
            display: flex;
            flex-direction: column;
            gap: .15rem;
        }
        .public-mobile-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 52;
            opacity: 0;
            visibility: hidden;
            transition: all .2s ease;
        }
        .public-mobile-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        @media (max-width: 980px) {
            .public-topbar {
                display: none;
            }
            .public-header-nav {
                min-height: 64px;
            }
            .public-nav-list,
            .public-header-actions .public-cta-btn {
                display: none;
            }
            .public-mobile-toggle {
                display: inline-flex;
            }
            .public-mobile-menu.mode-dropdown {
                top: 64px;
            }
            .public-mega {
                min-width: 320px;
            }
        }
        @media (max-width: 640px) {
            .public-brand span {
                display: none;
            }
        }
