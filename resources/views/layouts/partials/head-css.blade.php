@yield('css')
<script>
    (function () {
        const html = document.documentElement;
        const defaultConfig = {
            theme: 'light',
            topbar: { color: 'topbar-light' },
            menu: { size: 'default', color: 'sidebar-dark' },
        };

        html.classList.add('layout-preload');

        let config = JSON.parse(JSON.stringify(defaultConfig));

        try {
            const savedConfig = sessionStorage.getItem('__THEME_CONFIG__');
            if (savedConfig) {
                const parsed = JSON.parse(savedConfig);

                config = {
                    ...config,
                    ...parsed,
                    topbar: {
                        ...config.topbar,
                        ...(parsed?.topbar || {}),
                    },
                    menu: {
                        ...config.menu,
                        ...(parsed?.menu || {}),
                    },
                };
            }
        } catch (error) {
            config = defaultConfig;
        }

        html.setAttribute('data-bs-theme', config.theme || defaultConfig.theme);
        html.setAttribute('data-sidenav-size', config.menu.size === 'sidebar-hover' ? 'hover' : (config.menu.size || defaultConfig.menu.size));
        html.classList.add(config.topbar.color || defaultConfig.topbar.color);
        html.classList.add(config.menu.color || defaultConfig.menu.color);

        if (window.innerWidth <= 1140) {
            html.classList.add('sidebar-hidden');
            return;
        }

        if (config.menu.size === 'sidebar-hover') {
            html.classList.add('sidebar-hover');
            return;
        }

        html.classList.add(config.menu.size || defaultConfig.menu.size);
    })();
</script>
<style>
    html.layout-preload .main-nav,
    html.layout-preload .page-container,
    html.layout-preload .topbar,
    html.layout-preload .main-nav * {
        transition: none !important;
    }

    @media (min-width: 1141px) {
        html.sidebar-hover .page-container,
        html[data-sidenav-size="condensed"] .page-container {
            margin-left: var(--bs-main-nav-width-sm, 80px);
        }

        html.sidebar-hover .topbar,
        html[data-sidenav-size="condensed"] .topbar {
            padding-left: var(--bs-main-nav-width-sm, 80px);
        }

        html[data-sidenav-size="condensed"] .main-nav,
        html.sidebar-hover .main-nav:not(:hover) {
            width: var(--bs-main-nav-width-sm, 80px);
            min-width: var(--bs-main-nav-width-sm, 80px);
        }
    }

    :root {
        --lap-admin-ink: #030523;
        --lap-admin-navy: #0d2f67;
        --lap-admin-accent: #fe5900;
        --lap-admin-accent-deep: #d94c00;
        --lap-admin-danger: #e41b23;
        --lap-admin-muted: #667085;
        --lap-admin-surface: #f7f8fc;
    }

    @media (max-width: 1140px) {
        html:not(.sidebar-enable) .main-nav {
            margin-left: calc(var(--bs-main-nav-width, 250px) * -1);
            visibility: hidden;
        }

        html.sidebar-enable .main-nav {
            margin-left: 0;
            visibility: visible;
        }

        html:not(.sidebar-enable) .page-container {
            margin-left: 0;
        }
    }

    @media (max-width: 600px) {
        .topbar .topbar-user-dropdown {
            position: relative;
        }

        .topbar .topbar-user-dropdown .topbar-user-menu {
            width: auto;
            min-width: 180px;
            max-width: 220px;
        }
    }

    .lap-admin-chip {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.72rem;
        font-weight: 800;
        gap: 0.35rem;
        letter-spacing: 0.08em;
        line-height: 1;
        padding: 0.45rem 0.78rem;
        text-transform: uppercase;
    }

    .lap-admin-chip-primary {
        background: rgba(254, 89, 0, 0.10);
        border-color: rgba(254, 89, 0, 0.16);
        color: var(--lap-admin-accent);
    }

    .lap-admin-chip-support {
        background: rgba(13, 47, 103, 0.10);
        border-color: rgba(13, 47, 103, 0.16);
        color: var(--lap-admin-navy);
    }

    .lap-admin-chip-approved {
        background: rgba(3, 5, 35, 0.12);
        border-color: rgba(3, 5, 35, 0.16);
        color: var(--lap-admin-ink);
    }

    .lap-admin-chip-pending {
        background: rgba(254, 89, 0, 0.10);
        border-color: rgba(254, 89, 0, 0.18);
        color: var(--lap-admin-accent-deep);
    }

    .lap-admin-chip-revision {
        background: rgba(13, 47, 103, 0.10);
        border-color: rgba(13, 47, 103, 0.18);
        color: var(--lap-admin-navy);
    }

    .lap-admin-chip-danger {
        background: rgba(228, 27, 35, 0.10);
        border-color: rgba(228, 27, 35, 0.18);
        color: var(--lap-admin-danger);
    }

    .lap-admin-chip-draft {
        background: rgba(3, 5, 35, 0.08);
        border-color: rgba(3, 5, 35, 0.14);
        color: rgba(3, 5, 35, 0.82);
    }

    .lap-admin-chip-count {
        font-size: 1rem;
        letter-spacing: 0;
        min-width: 2.5rem;
        justify-content: center;
        padding-inline: 0.7rem;
    }

    .lap-admin-stat-card {
        --lap-stat-accent: var(--lap-admin-accent);
        --lap-stat-soft: rgba(254, 89, 0, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        border: 1px solid rgba(3, 5, 35, 0.08);
        border-radius: 1rem;
        box-shadow: 0 18px 40px rgba(3, 5, 35, 0.05);
        overflow: hidden;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .lap-admin-stat-card::before {
        background: linear-gradient(90deg, var(--lap-stat-accent) 0%, transparent 78%);
        content: "";
        height: 3px;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
    }

    .lap-admin-stat-card:hover {
        border-color: rgba(3, 5, 35, 0.12);
        box-shadow: 0 22px 44px rgba(3, 5, 35, 0.08);
        transform: translateY(-2px);
    }

    .lap-admin-stat-card .card-body {
        position: relative;
    }

    .lap-admin-stat-card .avatar-md {
        background: var(--lap-stat-soft) !important;
        box-shadow: inset 0 0 0 1px rgba(3, 5, 35, 0.04);
    }

    .lap-admin-stat-card .avatar-md i {
        color: var(--lap-stat-accent) !important;
    }

    .lap-admin-stat-card .progress {
        background: rgba(3, 5, 35, 0.06);
    }

    .lap-admin-stat-card .progress-bar {
        background: var(--lap-stat-accent) !important;
    }

    .lap-admin-stat-card-primary {
        --lap-stat-accent: var(--lap-admin-accent);
        --lap-stat-soft: rgba(254, 89, 0, 0.10);
    }

    .lap-admin-stat-card-support {
        --lap-stat-accent: var(--lap-admin-navy);
        --lap-stat-soft: rgba(13, 47, 103, 0.10);
    }

    .lap-admin-stat-card-approved {
        --lap-stat-accent: var(--lap-admin-ink);
        --lap-stat-soft: rgba(3, 5, 35, 0.08);
    }

    .lap-admin-stat-card-pending {
        --lap-stat-accent: var(--lap-admin-accent-deep);
        --lap-stat-soft: rgba(254, 89, 0, 0.10);
    }

    .lap-admin-stat-card-danger {
        --lap-stat-accent: var(--lap-admin-danger);
        --lap-stat-soft: rgba(228, 27, 35, 0.10);
    }

    .lap-admin-stat-value {
        color: var(--lap-admin-ink);
        font-size: clamp(1.9rem, 2vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1;
        margin: 0;
    }

    .lap-admin-stat-copy {
        color: var(--lap-admin-muted) !important;
        margin: 0;
    }

    .lap-admin-mini-panel {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        border: 1px solid rgba(3, 5, 35, 0.08);
        border-radius: 0.95rem;
        box-shadow: 0 14px 32px rgba(3, 5, 35, 0.04);
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .lap-admin-mini-panel:hover {
        border-color: rgba(254, 89, 0, 0.18);
        box-shadow: 0 18px 36px rgba(3, 5, 35, 0.08);
        transform: translateY(-2px);
    }

    .lap-admin-page-head {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.25rem;
        justify-content: space-between;
        margin-bottom: 1.75rem;
    }

    .lap-admin-page-meta {
        flex: 1 1 28rem;
        max-width: 54rem;
        min-width: 0;
    }

    .lap-admin-page-meta .breadcrumb {
        margin-bottom: 0.55rem !important;
    }

    .lap-admin-page-title {
        color: var(--lap-admin-ink);
        font-size: clamp(1.65rem, 1.2rem + 1vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.08;
        margin-bottom: 0.35rem !important;
    }

    .lap-admin-page-copy {
        color: var(--lap-admin-muted) !important;
        line-height: 1.58;
        margin-bottom: 0 !important;
        max-width: 66ch;
    }

    .lap-admin-page-actions {
        align-items: flex-start;
        display: flex;
        flex: 0 1 auto;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: flex-end;
    }

    .lap-admin-page-actions .dropdown {
        flex: 0 0 auto;
    }

    .card-header {
        padding: 1rem 1.25rem;
    }

    .card-header .card-title {
        color: var(--lap-admin-ink);
        font-weight: 750;
        letter-spacing: -0.02em;
    }

    .card-header .text-muted,
    .card-header p.text-muted {
        line-height: 1.52;
        max-width: 68ch;
    }

    @media (max-width: 820px) {
        .lap-admin-page-head {
            margin-bottom: 1.25rem;
        }

        .lap-admin-page-actions {
            width: 100%;
        }

        .lap-admin-page-actions > .btn,
        .lap-admin-page-actions > a.btn,
        .lap-admin-page-actions > .dropdown,
        .lap-admin-page-actions > *:not(.dropdown) {
            flex: 1 1 100%;
        }

        .lap-admin-page-actions .dropdown > .btn,
        .lap-admin-page-actions .dropdown > .btn-group,
        .lap-admin-page-actions .dropdown > .dropdown-toggle {
            width: 100%;
            justify-content: center;
        }

        .lap-admin-page-title {
            font-size: 1.45rem;
        }

        .card-header {
            padding: 0.95rem 1rem;
        }
    }

    .competition-table-wrap,
    .table-responsive.competition-table-wrap {
        width: 100%;
        overflow: visible !important;
        overflow-y: visible !important;
    }

    .competition-table > tbody > tr > td {
        position: relative;
    }

    .competition-table-wrap.dropdown-open {
        overflow: visible !important;
        overflow-y: visible !important;
    }

    .competition-table {
        --bs-table-bg: transparent;
        --bs-table-hover-bg: rgba(15, 23, 42, 0.025);
        margin-bottom: 0;
    }

    .competition-table .lap-admin-chip {
        font-size: 0.64rem;
        letter-spacing: 0.06em;
        padding: 0.34rem 0.56rem;
        white-space: nowrap;
    }

    .competition-table > :not(caption) > * > * {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border-bottom-color: var(--bs-border-color-translucent);
        background-color: transparent;
    }

    .competition-table > thead {
        vertical-align: middle;
    }

    .competition-table > thead > tr > th {
        background: var(--bs-light-bg-subtle);
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom-width: 1px;
    }

    .competition-table > tbody > tr:last-child > td {
        border-bottom-width: 0;
    }

    .competition-table-compact > :not(caption) > * > * {
        padding: 0.7rem 0.85rem;
    }

    .competition-table-empty {
        color: var(--bs-secondary-color);
        text-align: center;
        padding-block: 1.4rem !important;
    }

    .competition-table-actions {
        min-width: 168px;
        width: 1%;
        white-space: nowrap;
    }

    .competition-table-meta {
        color: var(--bs-secondary-color);
        font-size: 0.825rem;
        margin-top: 0.25rem;
    }

    .competition-sort-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: inherit;
        text-decoration: none;
    }

    .competition-sort-link:hover,
    .competition-sort-link:focus {
        color: var(--bs-emphasis-color);
    }

    .competition-sort-link.active {
        color: var(--bs-emphasis-color);
    }

    .competition-sort-indicator {
        font-size: 0.75rem;
        opacity: 0.7;
        line-height: 1;
    }

    .competition-action-menu {
        min-width: 280px;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.9rem;
        box-shadow: 0 0.75rem 1.5rem rgba(15, 23, 42, 0.12);
        z-index: 1080;
        max-height: 70vh;
        overflow-y: auto;
    }

    .competition-action-menu .dropdown-item {
        border-radius: 0.65rem;
        padding: 0.6rem 0.75rem;
    }

    .competition-action-menu .dropdown-item.text-danger:hover,
    .competition-action-menu .dropdown-item.text-danger:focus {
        background-color: rgba(var(--bs-danger-rgb), 0.08);
        color: var(--bs-danger) !important;
    }

    .competition-action-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--bs-secondary-color);
    }

    .competition-action-section {
        padding: 0.25rem 0.25rem 0.35rem;
    }

    .competition-action-toggle {
        padding-right: 0.9rem;
    }

    .competition-table-actions .dropdown,
    .competition-table-actions .dropup {
        display: inline-block;
    }

    .competition-table-actions .dropdown .competition-action-menu,
    .competition-table-actions .dropup .competition-action-menu {
        inset: auto 0 calc(100% + 0.35rem) auto !important;
        transform: none !important;
        margin: 0 !important;
    }

    .competition-action-toggle:hover,
    .competition-action-toggle:focus,
    .competition-action-toggle.show {
        background-color: var(--bs-light);
        border-color: var(--bs-border-color);
        color: var(--bs-emphasis-color);
    }

    .competition-action-toggle-icon {
        width: 0.85rem;
        height: 0.85rem;
        flex-shrink: 0;
    }

    .review-actions-notes {
        resize: vertical;
        min-height: 78px;
        border-radius: 0.75rem;
    }

    .review-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .review-actions-button {
        border-radius: 0.7rem;
        font-weight: 600;
    }

    .review-actions-icon {
        width: 0.95rem;
        height: 0.95rem;
        flex-shrink: 0;
    }

    .review-actions-button-danger {
        grid-column: 1 / -1;
    }

    .competition-bulk-panel .form-select,
    .competition-bulk-panel .form-control,
    .competition-bulk-panel [data-bulk-submit],
    .competition-bulk-panel .choices__inner {
        min-height: 40px;
    }

    .competition-bulk-panel textarea.form-control {
        min-height: 40px;
        height: 40px;
        resize: none;
    }

    .competition-bulk-panel .choices {
        margin-bottom: 0;
    }

    .competition-bulk-panel .choices__inner {
        border-color: var(--bs-border-color);
        border-radius: var(--bs-border-radius);
        background: #fff;
        padding: 0.42rem 0.75rem;
    }

    .competition-bulk-panel .choices[data-type*="select-one"] .choices__inner {
        padding-bottom: 0.42rem;
    }

    .competition-bulk-panel .choices__list--dropdown,
    .competition-bulk-panel .choices__list[aria-expanded] {
        z-index: 1085;
        max-width: 100%;
        word-break: break-word;
    }

    @media (max-width: 820px) {
        .competition-table-wrap,
        .table-responsive.competition-table-wrap,
        .competition-table-wrap.dropdown-open {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        .competition-table-actions .competition-action-menu {
            position: fixed !important;
            inset: auto 0.75rem 0.75rem 0.75rem !important;
            width: auto !important;
            max-width: none !important;
            max-height: min(60vh, calc(100vh - 1.5rem)) !important;
            overflow-y: auto !important;
            border-radius: 1rem;
            box-shadow: 0 1.25rem 3rem rgba(15, 23, 42, 0.22);
            z-index: 1095;
        }

        .accordion .accordion-button {
            padding: 0.9rem 1rem;
        }

        .accordion .accordion-body {
            padding: 0.9rem;
        }

        .competition-bulk-panel {
            row-gap: 0.85rem !important;
        }

        .competition-bulk-panel > [class*="col-"] {
            width: 100%;
        }

        .competition-bulk-panel .form-label {
            display: block;
            margin-bottom: 0.35rem;
            min-height: 0;
            font-size: 0.875rem;
        }

        .competition-bulk-panel .form-label.d-block {
            margin-bottom: 0;
            line-height: 0;
        }

        .competition-bulk-panel .form-select,
        .competition-bulk-panel .form-control,
        .competition-bulk-panel .btn,
        .competition-bulk-panel .choices {
            width: 100%;
        }

        .competition-bulk-panel textarea.form-control {
            min-height: 40px;
            height: 40px;
        }

        .competition-bulk-panel [data-bulk-submit] {
            margin-top: 0.1rem;
        }
    }

    @media (max-width: 600px) {
        .competition-table-actions {
            min-width: 132px;
        }

        .competition-table-actions .dropdown,
        .competition-table-actions .dropup {
            width: auto;
        }

        .competition-action-toggle {
            min-width: 120px;
            justify-content: space-between;
        }

        .competition-table-actions .competition-action-menu {
            min-width: 0;
        }

        .competition-action-menu .dropdown-item {
            white-space: normal;
        }
    }

</style>
@vite(['resources/scss/app.scss'])
@vite(['resources/js/config.js'])
