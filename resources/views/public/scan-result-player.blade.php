<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
    <div class="wrap">
        <div class="card">
            <div class="head">
                <div class="eyebrow">Verifikasi Publik</div>
                <div class="title">Data Pemain</div>
            </div>
            <div class="body">
                <div>
                    <img src="{{ $player->photo_file_url ?: asset('images/users/avatar-1.jpg') }}" alt="{{ $player->name }}" class="photo">
                </div>
                <div>
                    <div class="name">{{ $player->name }}</div>
                    <div class="sub">{{ $player->club?->name ?: '-' }} · {{ $player->primaryAgeGroup?->name ?: '-' }}</div>
                    <div class="badge">Terverifikasi untuk dilihat publik</div>

                    <div class="grid">
                        <div class="item"><div class="label">Tempat, Tanggal Lahir</div><div class="value">{{ trim(($player->birth_place ?: '-').', '.(optional($player->birth_date)->format('d M Y') ?: '-')) }}</div></div>
                        <div class="item"><div class="label">Sekolah</div><div class="value">{{ $player->school_name ?: '-' }}</div></div>
                        <div class="item"><div class="label">Posisi</div><div class="value">{{ $player->displayPosition($player->primary_age_group_id) ?: $player->position ?: '-' }}</div></div>
                        <div class="item"><div class="label">Nomor Punggung</div><div class="value">{{ $player->displayJerseyNumber($player->primary_age_group_id) ?: '-' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
