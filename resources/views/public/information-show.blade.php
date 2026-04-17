@extends('public.layout')

@push('styles')
    <style>
        .lap-public .lap-resource-hero {
            overflow: hidden;
        }

        .lap-public .lap-resource-preview {
            background: linear-gradient(135deg, #16324f 0%, #235347 100%);
            border-radius: 18px;
            min-height: 340px;
            overflow: hidden;
        }

        .lap-public .lap-resource-preview iframe {
            background: #fff;
            border: 0;
            height: 560px;
            width: 100%;
        }

        .lap-public .lap-resource-file-card {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 22px;
            color: #fff;
            min-height: 340px;
            padding: 32px;
        }

        .lap-public .lap-resource-file-card .eyebrow {
            font-size: 12px;
            letter-spacing: .12em;
            opacity: .78;
            text-transform: uppercase;
        }

        .lap-public .lap-resource-file-card .resource-type {
            display: block;
            font-size: 68px;
            font-weight: 800;
            line-height: 1;
            margin: 18px 0 12px;
        }

        .lap-public .lap-resource-file-card .resource-meta {
            color: rgba(255, 255, 255, .82);
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
        }

        .lap-public .lap-resource-file-card.word-theme {
            background: linear-gradient(135deg, rgba(18, 83, 170, .92) 0%, rgba(12, 49, 105, .96) 100%);
        }

        .lap-public .lap-resource-file-card.word-theme .resource-type {
            color: #dbeafe;
        }

        .lap-public .lap-resource-actions {
            align-items: stretch;
            display: grid;
            gap: 14px 16px;
            grid-template-columns: repeat(3, max-content);
            margin-top: 10px;
            justify-content: start;
        }

        .lap-public .lap-resource-actions .btn {
            min-width: 176px;
        }

        .lap-public .lap-resource-actions .btn:nth-child(4) {
            grid-column: 1 / span 2;
        }

        .lap-public .news-right-widget .widget .btn {
            min-width: 168px;
        }

        .lap-public .lap-resource-nav {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lap-public .lap-resource-nav-card {
            background: #fff;
            border: 1px solid #ebedf2;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .06);
            display: block;
            min-height: 100%;
            padding: 22px 24px;
        }

        .lap-public .lap-resource-nav-card .eyebrow {
            color: #6b7280;
            display: block;
            font-size: 12px;
            letter-spacing: .12em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .lap-public .lap-resource-nav-card h5 {
            color: #10131f;
            font-size: 19px;
            line-height: 1.45;
            margin: 0 0 8px;
        }

        .lap-public .lap-resource-nav-card p {
            color: #5c6271;
            margin: 0;
        }

        .lap-public .lap-resource-nav-card:hover,
        .lap-public .lap-resource-nav-card:focus {
            text-decoration: none;
        }

        .lap-public .lap-resource-url {
            background: #f7f8fb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            color: #374151;
            display: block;
            font-size: 14px;
            line-height: 1.6;
            overflow-wrap: anywhere;
            padding: 14px 16px;
        }

        .lap-public .lap-copy-feedback {
            color: #16a34a;
            display: none;
            font-size: 13px;
            margin-top: 10px;
        }

        .lap-public .lap-copy-feedback.is-visible {
            display: block;
        }

        /* Make feed-blog-item header/footer layout consistent with information list page. */
        .lap-public .lap-resource-hero.feed-blog-item .contents {
            border: 0;
        }

        .lap-public .lap-resource-hero.feed-blog-item .flex-wrapper {
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

        .lap-public .lap-resource-hero.feed-blog-item .catagory-tag {
            border-radius: 999px;
            font-size: 12px;
            letter-spacing: .1em;
            padding: 7px 14px;
        }

        .lap-public .lap-resource-hero.feed-blog-item .blog-author {
            background: transparent;
            filter: none;
            font-size: 12px;
            letter-spacing: .14em;
            padding: 0;
            text-transform: uppercase;
        }

        .lap-public .lap-resource-hero.feed-blog-item .blog-bottom-action {
            align-items: center;
            column-gap: 16px;
            display: flex;
            flex-wrap: wrap;
            row-gap: 10px;
        }

        .lap-public .lap-resource-hero.feed-blog-item .blog-bottom-action .item {
            color: rgba(255, 255, 255, .8);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: .02em;
        }

        .lap-public .lap-resource-hero.feed-blog-item .blog-bottom-action .item i {
            color: rgba(255, 255, 255, .95);
            margin-right: 6px;
        }

        @media (max-width: 991.98px) {
            .lap-public .lap-resource-preview iframe {
                height: 420px;
            }

            .lap-public .lap-resource-nav {
                grid-template-columns: 1fr;
            }

            .lap-public .lap-resource-actions {
                grid-template-columns: 1fr;
            }

            .lap-public .lap-resource-actions .btn,
            .lap-public .news-right-widget .widget .btn {
                width: 100%;
            }

            .lap-public .lap-resource-actions .btn:nth-child(4) {
                grid-column: auto;
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

    <section class="news-feed-section section-gap">
        <div class="container">
            <div class="row mb-15">
                <div class="col-xl-8 col-md-7">
                    <div class="news-feed-area">
                        <div class="blog-item feed-blog-item lap-resource-hero">
                            <div class="blog-picture lap-blog-thumb lap-resource-preview">
                                @if ($resource->is_image)
                                    <img src="{{ $resource->file_url }}" alt="{{ $resource->title }}">
                                @elseif ($resource->is_pdf)
                                    <iframe src="{{ $resource->file_url }}#toolbar=0&navpanes=0" title="{{ $resource->title }}"></iframe>
                                @else
                                    <div class="lap-resource-file-card d-flex flex-column justify-content-between {{ $resource->is_word ? 'word-theme' : '' }}">
                                        <div>
                                            <span class="eyebrow">Dokumen Kompetisi</span>
                                            <span class="resource-type">{{ $resource->type_label }}</span>
                                            <h3 class="text-white mb-3">{{ $resource->title }}</h3>
                                            <p class="mb-0 text-white-50">
                                                {{ $resource->description ?: ($resource->is_word ? 'Dokumen Word ini tersedia untuk diunduh dan dibuka di aplikasi pengolah dokumen.' : 'File ini tersedia sebagai dokumen resmi yang dipublikasikan melalui pusat informasi.') }}
                                            </p>
                                        </div>
                                        <div class="resource-meta mt-4">
                                            <span>{{ $categoryLabels[$resource->category] ?? strtoupper($resource->category) }}</span>
                                            <span>{{ $resource->file_size_label }}</span>
                                            <span>{{ $resource->file_name }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
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
                                <h2 class="blog-title mb-3">{{ strtoupper($resource->title) }}</h2>
                                <div class="blog-bottom-action mb-4">
                                    <span class="item date"><i class="fas fa-calendar-alt"></i> {{ $resource->created_at?->translatedFormat('d F Y') ?: '-' }}</span>
                                    <span class="item"><i class="fas fa-folder-open"></i> {{ $categoryLabels[$resource->category] ?? strtoupper($resource->category) }}</span>
                                    <span class="item"><i class="fas fa-file"></i> {{ $resource->type_label }}</span>
                                    <span class="item"><i class="fas fa-weight-hanging"></i> {{ $resource->file_size_label }}</span>
                                </div>

                                <div class="lap-summary-card mb-30">
                                    <p class="lap-copy mb-0">
                                        {{ $resource->description ?: 'Dokumen ini dipublikasikan admin melalui pusat informasi kompetisi dan tersedia untuk dibuka langsung atau diunduh.' }}
                                    </p>
                                </div>

                                <div class="d-flex flex-wrap gap-2 lap-resource-actions">
                                    <a href="{{ $resource->file_url }}" target="_blank" class="btn btn-primary">Buka File</a>
                                    <a href="{{ $resource->file_url }}" target="_blank" download class="btn btn-light">Unduh File</a>
                                    <button type="button" class="btn btn-light" data-copy-resource-link="{{ $resourcePageUrl }}">Copy Link</button>
                                    <a href="{{ route('public.information') }}" class="btn btn-light">Kembali ke Informasi</a>
                                </div>
                                <div class="lap-copy-feedback" data-copy-feedback>Link berhasil disalin.</div>
                            </div>
                        </div>

                        @if ($previousResource || $nextResource)
                            <div class="lap-resource-nav mt-4">
                                @if ($previousResource)
                                    <a href="{{ route('public.information.show', ['resourceSlug' => $previousResource->public_slug]) }}" class="lap-resource-nav-card">
                                        <span class="eyebrow">Informasi Sebelumnya</span>
                                        <h5>{{ $previousResource->title }}</h5>
                                        <p>{{ $categoryLabels[$previousResource->category] ?? strtoupper($previousResource->category) }}</p>
                                    </a>
                                @endif
                                @if ($nextResource)
                                    <a href="{{ route('public.information.show', ['resourceSlug' => $nextResource->public_slug]) }}" class="lap-resource-nav-card">
                                        <span class="eyebrow">Informasi Berikutnya</span>
                                        <h5>{{ $nextResource->title }}</h5>
                                        <p>{{ $categoryLabels[$nextResource->category] ?? strtoupper($nextResource->category) }}</p>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-xl-4 col-md-5">
                    <div class="news-right-widget">
                        <div class="widget widget-post mb-40">
                            <div class="widget-title-box pb-25 mb-30">
                                <h4 class="widget-sub-title2 fs-20">Detail Dokumen</h4>
                            </div>
                            <ul class="post-list">
                                <li><div class="blog-post mb-30"><div class="post-content"><h6 class="mb-10">Kategori</h6><span class="fs-14">{{ $categoryLabels[$resource->category] ?? strtoupper($resource->category) }}</span></div></div></li>
                                <li><div class="blog-post mb-30"><div class="post-content"><h6 class="mb-10">Jenis File</h6><span class="fs-14">{{ $resource->type_label }}</span></div></div></li>
                                <li><div class="blog-post mb-30"><div class="post-content"><h6 class="mb-10">Ukuran</h6><span class="fs-14">{{ $resource->file_size_label }}</span></div></div></li>
                                <li><div class="blog-post mb-30"><div class="post-content"><h6 class="mb-10">Nama File</h6><span class="fs-14">{{ $resource->file_name }}</span></div></div></li>
                            </ul>
                        </div>

                        <div class="widget widget-categories-list mb-40">
                            <div class="widget-title-box pb-25 mb-30">
                                <h4 class="widget-sub-title2 fs-20">Aksi</h4>
                            </div>
                            <ul class="list-none">
                                <li><a href="{{ $resource->file_url }}" target="_blank"><i class="fal fa-angle-right"></i> Buka file</a></li>
                                <li><a href="{{ $resource->file_url }}" target="_blank" download><i class="fal fa-angle-right"></i> Unduh file</a></li>
                                <li><a href="{{ route('public.information', ['category' => $resource->category]) }}"><i class="fal fa-angle-right"></i> Lihat kategori serupa</a></li>
                                <li><a href="{{ route('public.information') }}"><i class="fal fa-angle-right"></i> Semua informasi</a></li>
                            </ul>
                        </div>

                        <div class="widget widget-post mb-40">
                            <div class="widget-title-box pb-25 mb-30">
                                <h4 class="widget-sub-title2 fs-20">Bagikan Halaman</h4>
                            </div>
                            <div class="post-content">
                                <span class="lap-resource-url">{{ $resourcePageUrl }}</span>
                                <button type="button" class="btn btn-light mt-3" data-copy-resource-link="{{ $resourcePageUrl }}">Copy Link</button>
                                <div class="lap-copy-feedback" data-copy-feedback>Link berhasil disalin.</div>
                            </div>
                        </div>

                        <div class="widget widget-post mb-40">
                            <div class="widget-title-box pb-25 mb-30">
                                <h4 class="widget-sub-title2 fs-20">Informasi Terkait</h4>
                            </div>
                            <ul class="post-list">
                                @forelse ($relatedResources as $item)
                                    <li>
                                        <div class="blog-post mb-30">
                                            <div class="post-content">
                                                <h6 class="mb-10">
                                                    <a href="{{ route('public.information.show', ['resourceSlug' => $item->public_slug]) }}">{{ $item->title }}</a>
                                                </h6>
                                                <span class="fs-14">{{ $item->created_at?->translatedFormat('d M Y') ?: '-' }}</span>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li><div class="blog-post mb-30"><div class="post-content"><span class="fs-14">Belum ada informasi terkait.</span></div></div></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyButtons = document.querySelectorAll('[data-copy-resource-link]');

            if (!copyButtons.length) {
                return;
            }

            const copyText = async (text) => {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                    return;
                }

                const tempInput = document.createElement('textarea');
                tempInput.value = text;
                tempInput.setAttribute('readonly', '');
                tempInput.style.position = 'absolute';
                tempInput.style.left = '-9999px';
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            };

            copyButtons.forEach((button) => {
                button.addEventListener('click', async function () {
                    const link = this.getAttribute('data-copy-resource-link');
                    const feedback = this.parentElement.querySelector('[data-copy-feedback]')
                        || this.closest('.contents, .post-content')?.querySelector('[data-copy-feedback]');

                    try {
                        await copyText(link);

                        if (feedback) {
                            feedback.classList.add('is-visible');
                            window.setTimeout(() => feedback.classList.remove('is-visible'), 2000);
                        }
                    } catch (error) {
                        console.error('Failed to copy resource link.', error);
                    }
                });
            });
        });
    </script>
@endsection
