<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="Verifikasi publik data pemain {{ $player->name }} di Liga Anak Piaman Laweh.">
    <meta name="robots" content="{{ $robotsContent ?? 'noindex,nofollow' }}">
    <meta name="theme-color" content="#e41b23">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="Liga Anak Piaman Laweh">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="Verifikasi publik data pemain {{ $player->name }} di Liga Anak Piaman Laweh.">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $player->photo_file_url ?: asset('og-share-card.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="Verifikasi publik data pemain {{ $player->name }} di Liga Anak Piaman Laweh.">
    <meta name="twitter:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:image" content="{{ $player->photo_file_url ?: asset('og-share-card.jpg') }}">
    <meta name="twitter:image:alt" content="{{ $title }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --line: #dbe7f5;
            --primary: #2563eb;
            --primary-deep: #1e3a8a;
            --text: #0f172a;
            --muted: #64748b;
            --ok-bg: #dcfce7;
            --ok-text: #166534;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #eff6ff 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 880px;
            margin: 0 auto;
            padding: 24px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        .head {
            padding: 20px 24px;
            background: linear-gradient(90deg, var(--primary-deep), var(--primary));
            color: #fff;
        }
        .eyebrow {
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            opacity: .78;
        }
        .title {
            margin-top: 8px;
            font-size: 28px;
            font-weight: 800;
        }
        .body {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 24px;
            padding: 24px;
        }
        .photo {
            width: 100%;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #f8fafc;
            display: block;
        }

        .photo-fallback {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: linear-gradient(135deg, #111827 0%, #334155 100%);
            color: rgba(255, 255, 255, 0.94);
            display: grid;
            place-items: center;
            font-size: 56px;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .name {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.05;
        }
        .sub {
            margin-top: 8px;
            color: var(--muted);
            font-weight: 600;
        }
        .badge {
            display: inline-flex;
            margin-top: 14px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--ok-bg);
            color: var(--ok-text);
            font-size: 13px;
            font-weight: 700;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }
        .note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }
        .item {
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #f8fbff;
        }
        .label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
        }
        .value {
            margin-top: 6px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
            word-break: break-word;
        }
        @media (max-width: 640px) {
            .wrap { padding: 14px; }
            .body { grid-template-columns: 1fr; }
            .photo { max-width: 180px; }
            .grid { grid-template-columns: 1fr; }
            .name { font-size: 24px; }
        }
    </style>
</head>
<body>
    @php($clubModel = method_exists($player, 'seasonClub') && $player->seasonClub ? $player->seasonClub : $player->club)
    <div class="wrap">
        <div class="card">
            <div class="head">
                <div class="eyebrow">Verifikasi Publik</div>
                <div class="title">Data Pemain</div>
            </div>
            <div class="body">
                <div>
                    @if ($player->photo_file_url)
                        <img src="{{ $player->photo_file_url }}" alt="{{ $player->name }}" class="photo">
                    @else
                        @php($playerInitial = \Illuminate\Support\Str::of($player->name)->trim()->substr(0, 1)->upper())
                        <div class="photo-fallback" aria-hidden="true">{{ $playerInitial ?: 'P' }}</div>
                    @endif
                </div>
                <div>
                    <div class="name">{{ $player->name }}</div>
                    <div class="sub">{{ $clubModel?->name ?: '-' }} · {{ $player->primaryAgeGroup?->name ?: '-' }}</div>
                    <div class="badge">Terverifikasi untuk dilihat publik</div>
                    <div class="note">Halaman QR publik ini hanya menampilkan ringkasan verifikasi pemain. Data pribadi dan dokumen pendukung tidak ditampilkan.</div>

                    <div class="grid">
                        <div class="item"><div class="label">Klub</div><div class="value">{{ $clubModel?->name ?: '-' }}</div></div>
                        <div class="item"><div class="label">Kelompok Usia</div><div class="value">{{ $player->primaryAgeGroup?->name ?: '-' }}</div></div>
                        <div class="item"><div class="label">Posisi</div><div class="value">{{ $player->displayPosition($player->primary_age_group_id) ?: $player->position ?: '-' }}</div></div>
                        <div class="item"><div class="label">Nomor Punggung</div><div class="value">{{ $player->displayJerseyNumber($player->primary_age_group_id) ?: '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
