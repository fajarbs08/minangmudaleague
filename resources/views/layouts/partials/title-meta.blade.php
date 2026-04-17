<!-- Title Meta -->
<meta charset="utf-8" />
@php
    $resolvedTitle = $seoTitle ?? (($title ?? config('app.name')).' | '.config('app.name'));
    $resolvedDescription = $seoDescription ?? ('Sistem administrasi '.config('app.name').'.');
    $resolvedUrl = $seoUrl ?? url()->current();
    $resolvedImage = $seoImage ?? asset('og-share-card.jpg');
    $resolvedImageType = str_ends_with(strtolower(parse_url($resolvedImage, PHP_URL_PATH) ?: ''), '.png') ? 'image/png' : 'image/jpeg';
    $resolvedRobots = $seoRobots ?? 'noindex,nofollow';
    $resolvedType = $seoType ?? 'website';
@endphp
<title>{{ $resolvedTitle }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="{{ $resolvedDescription }}" />
<meta name="author" content="{{ config('app.name') }}" />
<meta name="robots" content="{{ $resolvedRobots }}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="canonical" href="{{ $resolvedUrl }}">
<meta property="og:locale" content="id_ID" />
<meta property="og:type" content="{{ $resolvedType }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:title" content="{{ $resolvedTitle }}" />
<meta property="og:description" content="{{ $resolvedDescription }}" />
<meta property="og:url" content="{{ $resolvedUrl }}" />
<meta property="og:image" content="{{ $resolvedImage }}" />
<meta property="og:image:secure_url" content="{{ $resolvedImage }}" />
<meta property="og:image:type" content="{{ $resolvedImageType }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta property="og:image:alt" content="{{ $resolvedTitle }}" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $resolvedTitle }}" />
<meta name="twitter:description" content="{{ $resolvedDescription }}" />
<meta name="twitter:image" content="{{ $resolvedImage }}" />
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
