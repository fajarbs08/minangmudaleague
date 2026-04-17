@extends('public.layout')

@push('styles')
    <style>
        .lap-public .lap-information-page {
            background: #ffffff;
        }

        .lap-public .lap-information-page.section-gap {
            padding: 72px 0 96px;
        }

        .lap-public .lap-information-page .section-title-area {
            overflow: hidden;
            position: relative;
            z-index: 1;
            margin-bottom: 34px;
            padding-top: 14px;
        }

        .lap-public .lap-information-page .section-title-area::before {
            content: "INFORMASI";
            position: absolute;
            left: 50%;
            top: -38px;
            transform: translateX(-50%);
            font-size: clamp(72px, 12vw, 170px);
            font-weight: 800;
            letter-spacing: .04em;
            color: rgba(15, 23, 42, 0.025);
            line-height: 1;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
        }

        .lap-public .lap-information-page .section-title-area .pretitle {
            color: #e41b23;
            position: static;
            top: auto;
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .12em;
            line-height: 1;
            text-transform: uppercase;
            -webkit-text-fill-color: currentColor;
            -webkit-text-stroke-width: 0;
        }

        .lap-public .lap-information-page .section-title-area p {
            max-width: 760px;
            margin: 18px auto 0;
            overflow-wrap: anywhere;
        }

        .lap-public .lap-information-page .section-title-area .section-title {
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .lap-public .lap-information-filter {
            background: #ffffff;
            border: 1px solid #eceff4;
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .05);
            height: 100%;
            padding: 22px;
        }

        .lap-public .lap-information-filter .form-label {
            color: #5b6473;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .lap-public .lap-information-filter .form-control,
        .lap-public .lap-information-filter .form-select {
            background: #ffffff;
            border: 1px solid #dce1e8;
            border-radius: 12px;
            min-height: 48px;
        }

        .lap-public .lap-information-filter .btn {
            min-height: 48px;
        }

        .lap-public .lap-information-stat {
            align-items: center;
            background: #ffffff;
            border: 1px solid #eceff4;
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .05);
            display: flex;
            justify-content: space-between;
            min-height: 128px;
            height: 100%;
            padding: 18px 20px;
        }

        .lap-public .lap-information-stat strong {
            color: #10131f;
            display: block;
            font-size: 26px;
            line-height: 1;
        }

        .lap-public .lap-information-stat span {
            color: #6b7280;
            display: block;
            font-size: 12px;
            letter-spacing: .08em;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .lap-public .lap-information-stat i {
            color: #e41b23;
            font-size: 18px;
        }

        .lap-public .lap-information-card {
            background: #ffffff;
            border: 1px solid #eceff4;
            border-radius: 22px;
            overflow: hidden;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .lap-public .lap-information-card:hover {
            box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
            transform: translateY(-4px);
        }

        .lap-public .lap-information-card .blog-picture {
            background: #f3f5f9;
            display: block;
        }

        .lap-public .lap-information-card .blog-picture img {
            height: 260px;
            object-fit: cover;
            width: 100%;
        }

        .lap-public .lap-information-file {
            align-items: center;
            background: linear-gradient(180deg, #f7f8fb 0%, #eef2f7 100%);
            color: #10131f;
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
            min-height: 260px;
            padding: 28px;
            text-align: center;
        }

        .lap-public .lap-information-file .type {
            color: #e41b23;
            font-size: 52px;
            font-weight: 800;
            line-height: 1;
        }

        .lap-public .lap-information-file .meta {
            color: #6b7280;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .lap-public .lap-information-card .contents {
            padding: 26px;
        }

        .lap-public .lap-information-card .blog-title {
            color: #111111;
            display: block;
            font-size: 28px;
            line-height: 1.3;
            margin-bottom: 14px;
        }

        .lap-public .lap-information-card .blog-title:hover {
            color: #e41b23;
        }

        /* Override theme's feed-blog-item layout (absolute header + double separators). */
        .lap-public .lap-information-page .news-feed-area {
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding-right: 0;
        }

        .lap-public .lap-information-card.feed-blog-item {
            display: flex;
            align-items: stretch;
            gap: 26px;
            margin-bottom: 0;
            overflow: hidden;
            padding: 24px;
        }

        .lap-public .lap-information-card.feed-blog-item .contents {
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: center;
            border: none;
            padding: 0;
        }

        .lap-public .lap-information-card.feed-blog-item .blog-picture {
            border: 1px solid #eceff4;
            border-radius: 18px;
            flex: 0 0 320px;
            height: 100%;
            min-height: 260px;
            overflow: hidden;
        }

        .lap-public .lap-information-card.feed-blog-item .blog-picture.lap-blog-thumb {
            border-right: 1px solid #eceff4;
        }

        .lap-public .lap-information-card.feed-blog-item .blog-picture img,
        .lap-public .lap-information-card.feed-blog-item .lap-information-file {
            height: 100%;
            min-height: 100%;
        }

        .lap-public .lap-information-card.feed-blog-item .blog-picture img {
            object-fit: cover;
        }

        .lap-public .lap-information-content-row {
            --bs-gutter-x: 28px;
            --bs-gutter-y: 28px;
            align-items: flex-start;
            margin-top: 8px;
        }

        .lap-public .lap-information-card.feed-blog-item .flex-wrapper {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 14px;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-right: 0;
            position: static;
            top: auto;
        }

        .lap-public .lap-information-card.feed-blog-item .catagory-tag {
            border-radius: 999px;
            font-size: 12px;
            letter-spacing: .1em;
            padding: 7px 14px;
        }

        .lap-public .lap-information-card.feed-blog-item .blog-author {
            background: transparent;
            filter: none;
            font-size: 12px;
            letter-spacing: .14em;
            padding: 0;
            text-transform: uppercase;
        }

        .lap-public .lap-information-card.feed-blog-item p {
            border-bottom: 0;
            color: #6b7280;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .lap-public .lap-information-card .blog-bottom-action {
            border-top: 1px solid #eceff4;
            margin-top: 18px;
            padding-top: 16px;
        }

        .lap-public .lap-information-card .blog-bottom-action {
            align-items: center;
            column-gap: 16px;
            display: flex;
            flex-wrap: wrap;
            row-gap: 10px;
        }

        .lap-public .lap-information-card .blog-bottom-action .item {
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: .02em;
        }

        .lap-public .lap-information-card .blog-bottom-action .item i {
            color: #111111;
            margin-right: 6px;
        }

        .lap-public .lap-information-card .blog-bottom-action a.item:hover {
            color: #e41b23;
        }

        .lap-public .lap-information-sidebar .widget {
            background: #ffffff;
            border: 1px solid #eceff4;
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .05);
            margin-bottom: 20px;
            overflow: hidden;
            padding: 26px 24px;
        }

        .lap-public .lap-information-sidebar .widget-title-box {
            border-bottom: 1px solid #eceff4;
            margin-bottom: 20px;
            padding-bottom: 16px;
        }

        .lap-public .lap-information-sidebar .widget-title-box h4 {
            color: #111111;
            font-size: 18px;
            margin: 0;
        }

        .lap-public .lap-information-sidebar .post-list,
        .lap-public .lap-information-sidebar .list-none {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 0;
            padding: 0;
        }

        .lap-public .lap-information-sidebar .blog-post,
        .lap-public .lap-information-sidebar .list-none li {
            margin: 0;
        }

        .lap-public .lap-information-sidebar .post-content h6 {
            color: #111111;
            font-size: 18px;
            line-height: 1.25;
            margin-bottom: 6px !important;
            text-transform: uppercase;
        }

        .lap-public .lap-information-sidebar .post-content .fs-14 {
            color: #6b7280;
            display: block;
            font-size: 15px !important;
            line-height: 1.6;
        }

        .lap-public .lap-information-sidebar .summary-item {
            border: 1px solid #eceff4;
            border-radius: 16px;
            padding: 16px 18px;
        }

        .lap-public .news-feed-section .news-right-widget.lap-information-sidebar .widget.widget-categories-list .list-none li a {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 14px;
            color: #1f2937;
            display: flex;
            font-family: inherit;
            font-size: 16px;
            gap: 14px;
            justify-content: space-between;
            line-height: 1.4;
            position: static;
            padding: 12px 14px;
            text-decoration: none;
            transition: all .2s ease;
            width: 100%;
        }

        .lap-public .lap-information-sidebar .widget.widget-post {
            padding-bottom: 18px;
        }

        .lap-public .news-feed-section .news-right-widget.lap-information-sidebar .widget.widget-categories-list ul li a i,
        .lap-public .news-feed-section .news-right-widget.lap-information-sidebar .widget.widget-categories-list ul li a .category-link-label,
        .lap-public .news-feed-section .news-right-widget.lap-information-sidebar .widget.widget-categories-list ul li a .category-link-text,
        .lap-public .news-feed-section .news-right-widget.lap-information-sidebar .widget.widget-categories-list ul li a .category-count {
            position: static;
            right: auto;
            top: auto;
        }

        .lap-public .lap-information-sidebar .widget.widget-post .post-list li .blog-post {
            padding-right: 0;
        }

        .lap-public .lap-information-sidebar .category-link-label {
            align-items: center;
            display: flex;
            flex: 1 1 auto;
            font-size: 16px;
            font-weight: 700;
            gap: 10px;
            min-width: 0;
        }

        .lap-public .lap-information-sidebar .category-link-label i {
            color: #9ca3af;
            font-size: 13px;
            transition: color .2s ease, transform .2s ease;
        }

        .lap-public .lap-information-sidebar .category-link-text {
            display: block;
            overflow-wrap: anywhere;
            text-transform: uppercase;
        }

        .lap-public .lap-information-sidebar .category-count {
            background: #f3f4f6;
            border-radius: 999px;
            color: #4b5563;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 700;
            justify-content: center;
            line-height: 1;
            margin-left: auto;
            min-width: 34px;
            padding: 4px 10px;
        }

        .lap-public .lap-information-sidebar .list-none a:hover,
        .lap-public .lap-information-sidebar .list-none a.is-active {
            background: #fff5f5;
            border-color: rgba(228, 27, 35, .12);
            color: #111111;
            transform: translateX(2px);
        }

        .lap-public .lap-information-sidebar .list-none a:hover .category-link-label i,
        .lap-public .lap-information-sidebar .list-none a.is-active .category-link-label i {
            color: #e41b23;
            transform: translateX(2px);
        }

        .lap-public .lap-information-sidebar .list-none a.is-active .category-count {
            background: #e41b23;
            color: #ffffff;
        }

        .lap-public .lap-information-sidebar .list-none a:hover,
        .lap-public .lap-information-sidebar .post-content h6 a:hover {
            color: #e41b23;
        }

        @media (max-width: 991.98px) {
            .lap-public .lap-information-page .section-title-area::before {
                top: -18px;
            }

            .lap-public .lap-information-card .blog-picture img {
                height: 220px;
            }

            .lap-public .lap-information-file {
                min-height: 220px;
            }

            .lap-public .lap-information-sidebar {
                position: static;
            }

            .lap-public .lap-information-card.feed-blog-item {
                flex-direction: column;
                gap: 18px;
                padding: 18px;
            }

            .lap-public .lap-information-card.feed-blog-item .blog-picture {
                flex-basis: auto;
                min-height: 220px;
                width: 100%;
            }

            .lap-public .lap-information-card.feed-blog-item .blog-picture.lap-blog-thumb {
                border-right: 1px solid #eceff4;
                border-bottom: 1px solid #eceff4;
            }
        }

        @media (max-width: 768px) {
            .lap-public .lap-information-page.section-gap {
                padding: 60px 0;
            }

            .lap-public .lap-information-page .section-title-area {
                margin-bottom: 28px;
                padding-top: 6px;
            }

            .lap-public .lap-information-page .section-title-area::before {
                font-size: clamp(48px, 18vw, 84px);
                top: 8px;
                max-width: 100%;
            }

            .lap-public .lap-information-page .section-title-area .pretitle {
                font-size: 12px;
                letter-spacing: .16em;
            }

            .lap-public .lap-information-page .section-title-area .section-title {
                font-size: clamp(28px, 8vw, 38px) !important;
                line-height: 1.1;
                margin-bottom: 14px !important;
            }

            .lap-public .lap-information-page .section-title-area p {
                font-size: 14px;
                line-height: 1.7;
                margin-top: 14px;
                max-width: 100%;
                padding: 0 8px;
            }

            .lap-public .lap-information-content-row {
                --bs-gutter-x: 22px;
                --bs-gutter-y: 22px;
            }

            .lap-public .lap-information-filter,
            .lap-public .lap-information-stat,
            .lap-public .lap-information-sidebar .widget {
                border-radius: 18px;
            }
        }

        @media (max-width: 575.98px) {
            .lap-public .lap-information-page.section-gap {
                padding: 44px 0 56px;
            }

            .lap-public .lap-information-page .section-title-area {
                margin-bottom: 22px;
            }

            .lap-public .lap-information-page .section-title-area::before {
                font-size: 44px;
                left: 50%;
                letter-spacing: .06em;
                top: 14px;
                transform: translateX(-50%);
            }

            .lap-public .lap-information-page .section-title-area .section-title {
                font-size: 26px !important;
                line-height: 1.12;
            }

            .lap-public .lap-information-filter form.row {
                --bs-gutter-y: 14px;
            }

            .lap-public .lap-information-filter {
                padding: 18px;
            }

            .lap-public .lap-information-stat {
                min-height: 96px;
            }

            .lap-public .lap-information-card .contents {
                padding: 20px;
            }

            .lap-public .lap-information-card .blog-title {
                font-size: 22px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $categoryLabels = [
            'template' => 'Template',
            'flow' => 'Flow',
            'rules' => 'Rules',
            'manual' => 'Manual',
            'other' => 'Lainnya',
        ];
    @endphp

    <section class="news-feed-section section-gap lap-information-page">
        <div class="container">
            <div class="section-title-area section-title-area1 text-center mb--50">
                <span class="pretitle">Publikasi</span>
                <h1 class="section-title">PUSAT INFORMASI</h1>
                <p>Seluruh informasi di halaman ini ditarik langsung dari data yang dipublikasikan admin dan ditampilkan dengan format yang seragam dengan halaman publik lainnya.</p>
            </div>

            <div class="row g-4 mb-40">
                <div class="col-lg-8">
                    <div class="lap-information-filter">
                        <form method="GET" action="{{ route('public.information') }}" class="row g-3 align-items-end">
                            <div class="col-lg-6">
                                <label class="form-label">Cari informasi</label>
                                <input type="text" name="search" value="{{ $informationSearch }}" class="form-control" placeholder="Cari judul atau deskripsi">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select">
                                    <option value="">Semua kategori</option>
                                    @foreach ($categoryLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($activeInformationCategory === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 d-grid">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-2 col-sm-6">
                    <div class="lap-information-stat">
                        <div>
                            <strong>{{ $publishedResources->count() }}</strong>
                            <span>Dokumen</span>
                        </div>
                        <i class="fas fa-file-lines"></i>
                    </div>
                </div>
                <div class="col-lg-2 col-sm-6">
                    <div class="lap-information-stat">
                        <div>
                            <strong>{{ $resourceCategories->count() }}</strong>
                            <span>Kategori</span>
                        </div>
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>

            <div class="row lap-information-content-row">
                <div class="col-xl-9 col-md-8">
                    <div class="news-feed-area">
                        @forelse ($publishedResources as $resource)
                            <div class="blog-item feed-blog-item lap-information-card">
                                <a href="{{ route('public.information.show', ['resourceSlug' => $resource->public_slug]) }}" class="blog-picture lap-blog-thumb">
                                    @if ($resource->is_image)
                                        <img src="{{ $resource->file_url }}" alt="{{ $resource->title }}">
                                    @else
                                        <div class="lap-information-file">
                                            <div class="type">{{ $resource->type_label }}</div>
                                            <div class="meta">{{ $resource->badge_label }}</div>
                                            <div class="small">{{ $resource->file_size_label }}</div>
                                        </div>
                                    @endif
                                </a>
                                <div class="contents">
                                    <div class="flex-wrapper">
                                        <span class="catagory-tag">{{ strtoupper($resource->badge_label) }}</span>
                                        <div class="blog-author">
                                            <div class="author-dp lap-author-badge">
                                                <span class="lap-author-avatar">
                                                    @if ($resource->author_avatar_url)
                                                        <img src="{{ $resource->author_avatar_url }}" alt="{{ $resource->author_name }}">
                                                    @else
                                                        <span class="lap-author-avatar-fallback">{{ $resource->author_initials }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="author-name">BY {{ strtoupper($resource->author_name) }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('public.information.show', ['resourceSlug' => $resource->public_slug]) }}" class="blog-title">{{ strtoupper($resource->title) }}</a>
                                    <p>{{ $resource->description ?: ($resource->type_label . ' · ' . $resource->file_size_label) }}</p>
                                    <div class="blog-bottom-action">
                                        <span class="item date"><i class="fas fa-calendar-alt"></i> {{ $resource->created_at?->translatedFormat('d F Y') ?: '-' }}</span>
                                        <span class="item"><i class="fas fa-folder-open"></i> {{ $categoryLabels[$resource->category] ?? strtoupper($resource->category) }}</span>
                                        <a href="{{ route('public.information.show', ['resourceSlug' => $resource->public_slug]) }}" class="item"><i class="fas fa-arrow-up-right-from-square"></i> Detail</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="lap-summary-card">
                                <h3 class="section-title mb--20">Belum ada informasi publik</h3>
                                <p class="lap-copy mb-0">Informasi resmi yang diterbitkan admin akan tampil di halaman ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="col-xl-3 col-md-4">
                    <div class="news-right-widget lap-information-sidebar">
                        <div class="widget widget-post">
                            <div class="widget-title-box">
                                <h4 class="widget-sub-title2">Ringkasan</h4>
                            </div>
                            <ul class="post-list">
                                <li><div class="blog-post summary-item"><div class="post-content"><h6 class="mb-10">Informasi publik</h6><span class="fs-14">{{ $publishedResources->count() }} informasi terbit</span></div></div></li>
                                <li><div class="blog-post summary-item"><div class="post-content"><h6 class="mb-10">Kategori aktif</h6><span class="fs-14">{{ $resourceCategories->count() }} kategori tersedia</span></div></div></li>
                                <li><div class="blog-post summary-item"><div class="post-content"><h6 class="mb-10">Pencarian</h6><span class="fs-14">{{ $informationSearch ? 'Filter pencarian aktif' : 'Semua dokumen tampil' }}</span></div></div></li>
                            </ul>
                        </div>
                        <div class="widget widget-categories-list">
                            <div class="widget-title-box">
                                <h4 class="widget-sub-title2">Kategori</h4>
                            </div>
                            <ul class="list-none">
                                <li>
                                    <a href="{{ route('public.information', array_filter(['search' => $informationSearch])) }}" class="{{ blank($activeInformationCategory) ? 'is-active' : '' }}">
                                        <div class="category-link-label">
                                            <i class="fal fa-angle-right"></i>
                                            <strong class="category-link-text">Semua</strong>
                                        </div>
                                        <strong class="category-count">{{ $resourceCategories->sum('total') }}</strong>
                                    </a>
                                </li>
                                @foreach ($resourceCategories as $category)
                                    <li>
                                        <a href="{{ route('public.information', array_filter(['category' => $category->category, 'search' => $informationSearch])) }}" class="{{ $activeInformationCategory === $category->category ? 'is-active' : '' }}">
                                            <div class="category-link-label">
                                                <i class="fal fa-angle-right"></i>
                                                <strong class="category-link-text">{{ strtoupper($categoryLabels[$category->category] ?? $category->category) }}</strong>
                                            </div>
                                            <strong class="category-count">{{ $category->total }}</strong>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="widget widget-post">
                            <div class="widget-title-box">
                                <h4 class="widget-sub-title2">Terbaru</h4>
                            </div>
                            <ul class="post-list">
                                @forelse ($latestResources as $resource)
                                    <li>
                                        <div class="blog-post">
                                            <div class="post-content">
                                                <h6 class="mb-10">
                                                    <a href="{{ route('public.information.show', ['resourceSlug' => $resource->public_slug]) }}">{{ $resource->title }}</a>
                                                </h6>
                                                <span class="fs-14">{{ $resource->created_at?->translatedFormat('d M Y') ?: '-' }}</span>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li><div class="blog-post"><div class="post-content"><span class="fs-14">Belum ada data terbaru.</span></div></div></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
