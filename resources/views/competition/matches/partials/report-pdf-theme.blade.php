<style>
    @page {
        size: A4;
        margin: 6mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: DejaVu Sans, sans-serif;
        color: #0f172a;
        font-size: 10px;
        line-height: 1.35;
        background: #ffffff;
    }

    .report-sheet {
        width: 100%;
    }

    .report-block {
        margin-bottom: 10px;
    }

    .report-frame {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .report-frame th,
    .report-frame td {
        border: 1px solid #334e68;
        padding: 5px 6px;
        vertical-align: top;
    }

    .report-head td {
        padding: 6px 8px;
        vertical-align: middle;
    }

    .report-logo-box {
        text-align: center;
    }

    .report-logo-box img {
        max-width: 72px;
        max-height: 72px;
        display: block;
        margin: 0 auto 6px;
        object-fit: contain;
    }

    .report-brand,
    .report-side-note {
        font-size: 8px;
        line-height: 1.45;
        text-transform: uppercase;
        color: #475569;
    }

    .report-brand strong,
    .report-side-note strong {
        display: block;
        margin-top: 4px;
        font-size: 10px;
        color: #0f172a;
    }

    .report-head-title {
        text-align: center;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .report-head-subtitle {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .report-meta td {
        font-size: 10px;
        vertical-align: middle;
    }

    .report-meta .label {
        width: 16%;
        font-weight: 700;
        text-transform: uppercase;
    }

    .report-meta .sep {
        width: 2%;
        text-align: center;
    }

    .report-meta .value-wide {
        width: 32%;
    }

    .report-meta .value {
        width: 18%;
    }

    .section-banner {
        margin: 10px 0 6px;
        border: 1px solid #2f4f74;
        background: #eaf2fb;
        padding: 6px 7px;
    }

    .section-banner-title {
        margin: 0 0 2px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .section-banner-copy {
        margin: 0;
        font-size: 9px;
        color: #64748b;
    }

    .summary-table td {
        width: 50%;
        background: #fbfdff;
    }

    .summary-card-label {
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
    }

    .summary-card-value {
        margin: 4px 0;
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
        color: #0f172a;
    }

    .summary-card-copy {
        font-size: 9px;
        color: #64748b;
    }

    .report-data {
        page-break-inside: auto;
    }

    .report-data thead {
        display: table-header-group;
    }

    .report-data th,
    .report-data td {
        font-size: 9px;
    }

    .report-data thead th {
        background: #d9e7f6;
        color: #102a43;
        font-weight: 700;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .group-title {
        background: #eef2f7 !important;
        font-size: 10px !important;
    }

    .group-banner {
        border: 1px solid #274c77;
        border-bottom: 0;
        background: #274c77;
        color: #ffffff;
        padding: 5px 6px;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
    }

    .report-data tbody tr:first-child td {
        border-top-color: #274c77;
    }

    .report-row-odd td {
        background: #f1ede6 !important;
    }

    .report-row-even td {
        background: #e4effa !important;
    }

    .report-row-odd .points {
        background: #ddd4c7 !important;
    }

    .report-row-even .points {
        background: #cadcf2 !important;
    }

    .report-data.compact-stats th,
    .report-data.compact-stats td {
        padding: 4px 4px;
        font-size: 7.5px;
    }

    .report-data.compact-stats .group-title {
        font-size: 9px !important;
    }

    .report-data.compact-stats .cell-club {
        font-size: 8px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .points {
        font-weight: 800;
        background: #eef5ff !important;
        color: #0f2747;
    }

    .cell-club {
        white-space: normal;
        word-break: break-word;
        line-height: 1.2;
    }

    .cell-tight {
        white-space: nowrap;
    }

    .empty-row td {
        padding: 10px 8px;
        color: #64748b;
        text-align: center;
    }

    .report-note {
        display: block;
        width: 100%;
        border: 1px solid #9bb6d3;
        border-top: 0;
        background: #edf5ff;
        padding: 6px 8px;
        font-size: 8px;
        color: #355070;
        line-height: 1.45;
    }

    .report-note strong {
        color: #0f172a;
    }
</style>
