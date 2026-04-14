@php
    $pageWidth = (float) ($document['pageSize']['widthMm'] ?? $document['cardSize']['widthMm']);
    $pageHeight = (float) ($document['pageSize']['heightMm'] ?? $document['cardSize']['heightMm']);
    $cardWidth = (float) $document['cardSize']['widthMm'];
    $cardHeight = (float) $document['cardSize']['heightMm'];
    $paddingX = 10.0;
    $paddingY = 10.0;
    $gap = 6.0;
    $columns = max(1, (int) floor(($pageWidth - (2 * $paddingX) + $gap) / ($cardWidth + $gap)));
    $rows = max(1, (int) floor(($pageHeight - (2 * $paddingY) + $gap) / ($cardHeight + $gap)));
    $perPage = max(1, $columns * $rows);
    $chunks = collect($document['cards'])->chunk($perPage);
@endphp

@foreach ($chunks as $cards)
    <div class="idc-page">
        <div class="idc-page-grid" style="--idc-page-columns: {{ $columns }}; --idc-card-gap: {{ $gap }}mm;">
            @foreach ($cards as $card)
                <div class="idc-card-wrapper">
                    @include('competition.id-cards.partials.front', ['document' => $document, 'card' => $card])
                </div>
            @endforeach
        </div>
    </div>
@endforeach
