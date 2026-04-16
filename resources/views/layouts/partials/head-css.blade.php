@yield('css')
<script>
    (function () {
        if (window.innerWidth > 1140) {
            return;
        }

        document.documentElement.classList.add('sidebar-hidden');
    })();
</script>
<style>
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

    .competition-table-wrap,
    .table-responsive.competition-table-wrap {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    .competition-table > tbody > tr > td {
        position: relative;
    }

    .competition-table-wrap.dropdown-open {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    .competition-table {
        --bs-table-bg: transparent;
        --bs-table-hover-bg: rgba(15, 23, 42, 0.025);
        margin-bottom: 0;
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
            width: min(220px, calc(100vw - 1rem));
            max-width: calc(100vw - 1rem);
            right: 0 !important;
            left: auto !important;
        }

        .competition-action-menu .dropdown-item {
            white-space: normal;
        }
    }

</style>
@vite(['resources/scss/app.scss'])
@vite(['resources/js/config.js'])
