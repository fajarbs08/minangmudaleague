@php
    $pageWidth = (float) ($document['pageSize']['widthMm'] ?? $document['cardSize']['widthMm']);
    $pageHeight = (float) ($document['pageSize']['heightMm'] ?? $document['cardSize']['heightMm']);
    $cardWidth = (float) $document['cardSize']['widthMm'];
    $cardHeight = (float) $document['cardSize']['heightMm'];
    $paddingX = 10.0;
    $paddingY = 10.0;
    $gap = 6.0;
    $contentWidth = max(1.0, $pageWidth - (2 * $paddingX));
    $contentHeight = max(1.0, $pageHeight - (2 * $paddingY));
    $columns = max(1, (int) floor(($contentWidth + $gap) / ($cardWidth + $gap)));
    $rows = max(1, (int) floor(($contentHeight + $gap) / ($cardHeight + $gap)));
    $perPage = max(1, $columns * $rows);
    $pages = collect($document['cards'])->values()->chunk($perPage);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }}</title>
    <style>
        @page {
            size: {{ $pageWidth }}mm {{ $pageHeight }}mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 8pt;
        }

        .page {
            position: relative;
            width: {{ $pageWidth }}mm;
            height: {{ $pageHeight }}mm;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .sheet {
            position: absolute;
            left: {{ $paddingX }}mm;
            top: {{ $paddingY }}mm;
            width: {{ $contentWidth }}mm;
            height: {{ $contentHeight }}mm;
        }

        .card-slot {
            position: absolute;
            width: {{ $cardWidth }}mm;
            height: {{ $cardHeight }}mm;
        }

        .card {
            position: relative;
            width: {{ $cardWidth }}mm;
            height: {{ $cardHeight }}mm;
            border: 0.25mm solid #d8e0ea;
            border-radius: 3.8mm;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #e7eef6 100%);
        }

        .card-official {
            background: linear-gradient(135deg, #f7fcfb 0%, #dff7f2 100%);
            border-color: #cceae4;
        }

        .watermark {
            position: absolute;
            left: 4mm;
            top: 12mm;
            width: 39mm;
            opacity: 0.045;
            z-index: 0;
        }

        .head {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2;
            height: 8.8mm;
            background: #28415f;
            color: #ffffff;
            padding: 0 3mm;
            border-bottom: 0.2mm solid rgba(255, 255, 255, 0.1);
        }

        .card-official .head {
            background: #0f766e;
        }

        .head-table,
        .body-table,
        .right-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .head-table td {
            height: 8.8mm;
            vertical-align: middle;
        }

        .title {
            font-size: 4.25mm;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1;
        }

        .brand-cell {
            width: 8mm;
            text-align: right;
        }

        .brand-logo {
            width: 7mm;
            height: 7mm;
            display: block;
            margin-left: auto;
        }

        .body {
            position: absolute;
            top: 8.8mm;
            left: 0;
            right: 0;
            bottom: 2.6mm;
            z-index: 1;
            padding: 3.5mm 4mm 2.4mm;
            overflow: hidden;
        }

        .body-left {
            padding-right: 1.8mm;
            vertical-align: top;
        }

        .body-right {
            width: 19.2mm;
            vertical-align: top;
        }

        .meta-table td {
            font-size: 2.6mm;
            line-height: 1.1;
            padding: 0 0 1mm;
            vertical-align: top;
        }

        .meta-label {
            width: 8.6mm;
            text-align: right;
            padding-right: 0.55mm;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .meta-sep {
            width: 2.2mm;
            text-align: center;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .meta-value {
            font-weight: 800;
            color: #0f172a;
            word-break: normal;
            overflow-wrap: normal;
            white-space: normal;
        }

        .meta-row-single td {
            padding-bottom: 1mm;
        }

        .meta-value-one-line {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            overflow-wrap: normal;
        }

        .meta-value-multiline {
            word-break: normal;
            white-space: normal;
            overflow-wrap: normal;
            line-height: 1.15;
        }

        .meta-value-line {
            display: block;
        }

        .meta-value-multiline .meta-value-line {
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .photo-wrap,
        .qr-wrap {
            border: 0.18mm solid #d9e3ee;
            background: #ffffff;
            border-radius: 1mm;
            text-align: center;
            overflow: hidden;
        }

        .media-stack {
            width: 19.2mm;
            margin-left: auto;
        }

        .photo-wrap {
            width: 19.2mm;
            padding: 0.45mm;
            background: rgba(255, 255, 255, 0.86);
            border: 0.12mm solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 0.5mm 1.4mm rgba(15, 23, 42, 0.08), inset 0 0 0 0.08mm rgba(255, 255, 255, 0.35);
        }

        .photo {
            width: 100%;
            height: 21.6mm;
            display: block;
            border-radius: 0.7mm;
        }

        .qr-wrap {
            position: relative;
            width: 11.4mm;
            height: 11.4mm;
            padding: 0;
            border: 0.12mm solid rgba(100, 116, 139, 0.14);
            background: #ffffff;
            border-radius: 0.45mm;
            margin: 0.55mm auto 0;
            line-height: 0;
        }

        .qr-block {
            width: 19.2mm;
            margin: 0.55mm auto 0;
            text-align: center;
        }

        .qr {
            position: absolute;
            left: 0.9mm;
            top: 0.9mm;
            width: 9.6mm;
            height: 9.6mm;
            display: block;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
            image-rendering: -webkit-optimize-contrast;
        }

        .qr-status {
            margin-top: 0.45mm;
            font-size: 0.9mm;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-align: center;
            color: #28415f;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .qr-label {
            display: none;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2.6mm;
            margin-top: 0;
        }

        .footer-table td {
            height: 2.6mm;
            vertical-align: middle;
        }

        .footer-seg-1 {
            width: 24%;
            background: #c58a4b;
        }

        .card-official .footer-seg-1 {
            background: #d97706;
        }

        .footer-seg-2 {
            width: 45%;
            background: #3f5f88;
        }

        .card-official .footer-seg-2 {
            background: #0f766e;
        }

        .footer-seg-3 {
            width: 31%;
            background: #d8e0ea;
        }

        .footer-site {
            text-align: left;
            padding-left: 1.2mm;
            padding-right: 0;
            color: #28415f;
            font-size: 1.05mm;
            line-height: 1;
            font-weight: 600;
            white-space: nowrap;
        }

        .card-official .footer-site {
            color: #0f5c56;
        }
    </style>
</head>
<body>
    @foreach ($pages as $cards)
        <div class="page">
            <div class="sheet">
                @foreach ($cards as $card)
                    @php
                        $columnIndex = $loop->index % $columns;
                        $rowIndex = intdiv($loop->index, $columns);
                        $left = $columnIndex * ($cardWidth + $gap);
                        $top = $rowIndex * ($cardHeight + $gap);
                    @endphp
                    <div class="card-slot" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                        <div class="card {{ $card['type'] === 'official' ? 'card-official' : '' }}">
                            <div class="head">
                                <table class="head-table">
                                    <tr>
                                        <td>
                                            <div class="title">{{ strtoupper($card['front']['title']) }}</div>
                                        </td>
                                        <td class="brand-cell">
                                            @if (! empty($document['assets']['leagueLogoLight']))
                                                <img src="{{ $document['assets']['leagueLogoLight'] }}" alt="Logo" class="brand-logo">
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            @if (! empty($document['assets']['leagueWatermark']))
                                <img src="{{ $document['assets']['leagueWatermark'] }}" alt="" class="watermark">
                            @endif

                            <div class="body">
                                <table class="body-table">
                                    <tr>
                                        <td class="body-left">
                                            <table class="meta-table">
                                                <colgroup>
                                                    <col style="width: 7.2mm;">
                                                    <col style="width: 1.8mm;">
                                                    <col>
                                                </colgroup>
                                                @foreach (($card['front']['rows'] ?? $card['front']['meta']) as $meta)
                                                    @php
                                                        $singleLine = in_array((string) ($meta['label'] ?? ''), ['Nama', 'Klub'], true);
                                                    @endphp
                                                    <tr class="{{ $singleLine ? 'meta-row-single' : '' }}">
                                                        <td class="meta-label">{{ $meta['label'] }}</td>
                                                        <td class="meta-sep">:</td>
                                                        <td class="meta-value{{ $singleLine ? ' meta-value-one-line' : '' }}{{ ! empty($meta['multiline']) ? ' meta-value-multiline' : '' }}">
                                                            @if (! empty($meta['lines']))
                                                                @foreach ($meta['lines'] as $line)
                                                                    <span class="meta-value-line">{{ $line }}</span>
                                                                @endforeach
                                                            @else
                                                                {{ $meta['value'] }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                        <td class="body-right">
                                            <div class="media-stack">
                                                <div class="photo-wrap">
                                                    <img src="{{ $card['front']['photoSrc'] }}" alt="{{ $card['front']['name'] }}" class="photo">
                                                </div>
                                                <div class="qr-block">
                                                    <div class="qr-wrap">
                                                        <img src="{{ $card['back']['qrSrc'] }}" alt="QR" class="qr">
                                                        <div class="qr-label">{{ $card['back']['qrLabel'] ?? 'Verify' }}</div>
                                                    </div>
                                                    @if (! empty($card['front']['verificationText']))
                                                        <div class="qr-status">{{ $card['front']['verificationText'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="footer">
                                <table class="footer-table">
                                    <tr>
                                        <td class="footer-seg-1"></td>
                                        <td class="footer-seg-2"></td>
                                        <td class="footer-seg-3 footer-site">{{ $document['website'] }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</body>
</html>
