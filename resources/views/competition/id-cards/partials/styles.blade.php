<style>
    :root {
        --idc-page-width: {{ $document['pageSize']['widthMm'] ?? $document['cardSize']['widthMm'] }}mm;
        --idc-page-height: {{ $document['pageSize']['heightMm'] ?? $document['cardSize']['heightMm'] }}mm;
        --idc-card-width: {{ $document['cardSize']['widthMm'] }}mm;
        --idc-card-height: {{ $document['cardSize']['heightMm'] }}mm;
        --idc-page-padding-x: 10mm;
        --idc-page-padding-y: 10mm;
        --idc-card-gap: 6mm;
        --idc-primary: #3f5f88;
        --idc-primary-deep: #28415f;
        --idc-accent: #c58a4b;
        --idc-accent-soft: #f4e6d6;
        --idc-primary-soft: #e7eef6;
        --idc-text: #0f172a;
        --idc-muted: #64748b;
        --idc-line: rgba(63, 95, 136, 0.12);
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
        color: var(--idc-text);
        background: {{ $renderMode === 'preview' ? '#edf2f7' : '#ffffff' }};
    }

    @page {
        size: var(--idc-page-width) var(--idc-page-height);
        margin: 0;
    }

    .idc-page {
        position: relative;
        width: var(--idc-page-width);
        height: var(--idc-page-height);
        overflow: hidden;
        page-break-after: always;
        break-after: page;
        flex: 0 0 auto;
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        padding: var(--idc-page-padding-y) var(--idc-page-padding-x);
        background: #fff;
        box-shadow: {{ $renderMode === 'preview' ? '0 18px 45px rgba(15, 23, 42, 0.16)' : 'none' }};
        border-radius: {{ $renderMode === 'preview' ? '18px' : '0' }};
    }

    .idc-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }

    .idc-page-grid {
        display: grid;
        grid-template-columns: repeat(var(--idc-page-columns), var(--idc-card-width));
        grid-auto-rows: var(--idc-card-height);
        gap: var(--idc-card-gap);
        width: 100%;
        height: 100%;
        align-content: start;
        justify-content: start;
    }

    .idc-card {
        position: relative;
        width: var(--idc-card-width);
        height: var(--idc-card-height);
        overflow: hidden;
        border-radius: 3.8mm;
        background:
            radial-gradient(circle at top right, rgba(63, 95, 136, 0.08), transparent 28%),
            linear-gradient(135deg, #f8fafc 0%, #e7eef6 100%);
        border: 0.25mm solid #d8e0ea;
    }

    .idc-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(100, 116, 139, 0.06) 0, rgba(100, 116, 139, 0.06) 0.25mm, transparent 0.25mm, transparent 14mm),
            linear-gradient(0deg, rgba(255, 255, 255, 0.12) 0, rgba(255, 255, 255, 0.12) 0.25mm, transparent 0.25mm, transparent 14mm);
        background-size: 14mm 14mm;
        opacity: 0.28;
        pointer-events: none;
    }

    .idc-card::after {
        content: "";
        position: absolute;
        inset: 8.8mm 4.5mm 4.8mm;
        pointer-events: none;
        opacity: 0.08;
        background:
            radial-gradient(circle at 50% 50%, rgba(63, 95, 136, 0.16) 0 0.28mm, transparent 0.32mm),
            radial-gradient(circle at 50% 50%, transparent 0 7.6mm, rgba(63, 95, 136, 0.14) 7.75mm 8.1mm, transparent 8.2mm),
            linear-gradient(90deg, transparent 49.7%, rgba(63, 95, 136, 0.14) 49.7% 50.3%, transparent 50.3%),
            radial-gradient(circle at 0 50%, transparent 0 7.8mm, rgba(63, 95, 136, 0.12) 7.95mm 8.3mm, transparent 8.4mm),
            radial-gradient(circle at 100% 50%, transparent 0 7.8mm, rgba(63, 95, 136, 0.12) 7.95mm 8.3mm, transparent 8.4mm);
        background-repeat: no-repeat;
        background-position: center center, center center, center center, left center, right center;
        background-size: 100% 100%, 100% 100%, 100% 100%, 18mm 100%, 18mm 100%;
    }

    .idc-watermark {
        position: absolute;
        left: 4mm;
        top: 12mm;
        width: 39mm;
        opacity: 0.06;
        filter: grayscale(1);
    }

    .idc-compact-head {
        position: relative;
        z-index: 1;
        height: 8.8mm;
        padding: 0 3mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(90deg, var(--idc-primary-deep) 0%, var(--idc-primary) 100%);
        box-shadow: inset 0 -0.45mm 0 rgba(255, 255, 255, 0.08), 0 0.9mm 2mm rgba(40, 65, 95, 0.12);
        color: #fff;
    }

    .idc-compact-head::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 1.1mm;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.08));
        pointer-events: none;
    }

    .idc-compact-head::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 0.45mm;
        background: var(--idc-accent);
        pointer-events: none;
    }

    .idc-compact-title {
        font-size: 4.25mm;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .idc-compact-brand {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .idc-compact-brand-logo {
        width: 7.2mm;
        height: 7.2mm;
        object-fit: contain;
        display: block;
    }

    .idc-compact-body {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1fr 29.8mm;
        gap: 3.2mm;
        padding: 7.4mm 4.2mm 4.8mm;
    }

    .idc-compact-left {
        padding-top: 0;
    }

    .idc-compact-row {
        display: grid;
        grid-template-columns: 8.6mm 2.2mm 1fr;
        gap: 0;
        align-items: start;
        margin-bottom: 1.55mm;
    }

    .idc-compact-label,
    .idc-compact-sep,
    .idc-compact-value {
        font-size: 2.75mm;
        line-height: 1.08;
    }

    .idc-compact-label,
    .idc-compact-sep {
        font-weight: 500;
        color: var(--idc-muted);
    }

    .idc-compact-label {
        text-align: right;
        padding-right: 0.55mm;
    }

    .idc-compact-sep {
        text-align: center;
    }

    .idc-compact-value {
        font-weight: 800;
        color: var(--idc-text);
        word-break: break-word;
    }

    .idc-compact-right {
        display: grid;
        grid-template-columns: 16.6mm 11.8mm;
        align-items: center;
        justify-content: end;
        column-gap: 0.9mm;
        padding-top: 1.8mm;
        transform: translateX(-0.6mm);
    }

    .idc-compact-photo-card,
    .idc-compact-qr-card {
        border-radius: 1mm;
    }

    .idc-compact-photo-card {
        width: 100%;
        padding: 0.45mm;
        background: rgba(255, 255, 255, 0.86);
        border: 0.12mm solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 0.5mm 1.4mm rgba(15, 23, 42, 0.08), inset 0 0 0 0.08mm rgba(255, 255, 255, 0.35);
    }

    .idc-compact-qr-card {
        width: 100%;
        padding: 0.7mm;
        text-align: center;
        background: #f4f6f8;
        border: 0.12mm solid rgba(100, 116, 139, 0.14);
        box-shadow: 0 0.35mm 0.9mm rgba(15, 23, 42, 0.05), inset 0 0 0 0.08mm rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .idc-compact-photo,
    .idc-compact-qr {
        display: block;
        border-radius: 0.65mm;
        object-fit: cover;
        background: #fff;
    }

    .idc-compact-photo {
        width: 100%;
        height: 23.4mm;
        border: 0;
        border-radius: 0.7mm;
        box-shadow: none;
    }

    .idc-compact-qr {
        width: 100%;
        height: auto;
        aspect-ratio: 1;
        object-fit: contain;
        border: 0;
        padding: 0;
        background: transparent;
        margin: 0 auto;
        image-rendering: pixelated;
        image-rendering: crisp-edges;
        image-rendering: -webkit-optimize-contrast;
    }

    .idc-photo-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5mm;
        color: var(--idc-muted);
    }

    .idc-compact-foot {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        height: 2.6mm;
        display: flex;
        align-items: stretch;
    }

    .idc-compact-foot-bar {
        flex: 1 1 auto;
        background: linear-gradient(90deg, var(--idc-accent) 0%, #d1a06a 24%, var(--idc-primary-deep) 24%, var(--idc-primary) 74%, #d8e0ea 74%, #d8e0ea 100%);
    }

    .idc-compact-foot-site {
        flex: 0 0 12.8mm;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 0 1mm 0 0.6mm;
        background: transparent;
        color: var(--idc-primary-deep);
        font-size: 1.35mm;
        font-weight: 600;
        white-space: nowrap;
    }

    .idc-card--back {
        background:
            radial-gradient(circle at top right, rgba(70, 165, 237, 0.16), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);
    }

    .idc-card--back::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        width: 100%;
        height: 12.5mm;
        background: linear-gradient(90deg, #496fd7 0%, #46a5ed 100%);
    }

    .idc-back-shell {
        position: relative;
        z-index: 1;
        width: calc(100% - 9.2mm);
        margin: 8.8mm auto 0;
        padding: 3.4mm 3.6mm 3.2mm;
        background: rgba(255, 255, 255, 0.985);
        border-radius: 2.8mm;
        box-shadow: 0 2.4mm 5.2mm rgba(21, 53, 112, 0.12);
        border: 0.25mm solid rgba(73, 111, 215, 0.12);
    }

    .idc-back-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 3mm;
    }

    .idc-back-title {
        margin: 0;
        font-size: 3.7mm;
        line-height: 1;
        font-weight: 800;
        color: #3158c3;
    }

    .idc-back-subtitle {
        margin-top: 0.9mm;
        font-size: 1.95mm;
        line-height: 1.28;
        color: #6e7f99;
    }

    .idc-club-mark {
        width: 8.4mm;
        height: 8.4mm;
        display: block;
        object-fit: contain;
    }

    .idc-back-main {
        display: grid;
        grid-template-columns: 1fr 18mm;
        gap: 2.4mm;
        margin-top: 2.6mm;
    }

    .idc-facts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5mm;
    }

    .idc-fact {
        padding: 1.1mm 1.2mm;
        border-radius: 1.7mm;
        background: #f5f9ff;
        border: 0.25mm solid rgba(73, 111, 215, 0.14);
        min-height: 6.4mm;
    }

    .idc-fact-label {
        font-size: 1.35mm;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #6e7f99;
    }

    .idc-fact-value {
        margin-top: 0.35mm;
        font-size: 1.55mm;
        line-height: 1.2;
        font-weight: 700;
        color: #183b70;
        word-break: break-word;
    }

    .idc-back-side {
        display: flex;
        flex-direction: column;
        gap: 1.5mm;
    }

    .idc-qr-panel {
        padding: 1.6mm;
        border-radius: 2.3mm;
        background: #fff;
        border: 0.3mm solid rgba(26, 183, 89, 0.24);
        text-align: center;
        box-shadow: inset 0 0 0 0.2mm rgba(26, 183, 89, 0.08);
    }

    .idc-qr {
        width: 100%;
        aspect-ratio: 1;
        display: block;
        object-fit: contain;
    }

    .idc-qr-label {
        margin-top: 0.7mm;
        font-size: 1.6mm;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #1ab759;
    }

    .idc-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 3.8mm;
        padding: 0.65mm 1.35mm;
        border-radius: 999px;
        background: rgba(73, 111, 215, 0.08);
        border: 0.3mm solid rgba(73, 111, 215, 0.14);
        color: #496fd7;
        font-size: 1.45mm;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: center;
    }

    .idc-back-chip {
        min-height: 5.2mm;
    }

    .idc-notes {
        margin-top: 2mm;
        padding: 1.7mm 1.8mm;
        border-radius: 2.2mm;
        background: rgba(73, 111, 215, 0.05);
        border: 0.25mm solid rgba(73, 111, 215, 0.1);
    }

    .idc-note-line {
        font-size: 1.75mm;
        line-height: 1.3;
        color: #2d4369;
    }

    .idc-note-line + .idc-note-line {
        margin-top: 0.55mm;
    }

    .idc-disclaimer {
        margin-top: 1.2mm;
        font-size: 1.65mm;
        line-height: 1.28;
        color: #6e7f99;
    }

    .idc-back-footer {
        position: absolute;
        left: 4.6mm;
        right: 4.6mm;
        bottom: 2.5mm;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 3mm;
        padding-top: 1.6mm;
        border-top: 0.25mm solid rgba(73, 111, 215, 0.12);
    }

    .idc-verify,
    .idc-organizer {
        font-size: 1.5mm;
        line-height: 1.2;
        color: #6e7f99;
    }

    .idc-verify strong,
    .idc-organizer strong {
        display: block;
        margin-bottom: 0.35mm;
        font-size: 1.45mm;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #3158c3;
    }

    .idc-back-footer-right {
        text-align: right;
    }

    .idc-verify-url {
        margin-top: 0.6mm;
        font-size: 1.9mm;
        line-height: 1.2;
        word-break: break-word;
        color: #496fd7;
        font-weight: 700;
    }
</style>
