<div class="idc-card idc-card--front idc-card--{{ $card['type'] }}">
    <img src="{{ $document['assets']['leagueWatermark'] }}" alt="" class="idc-watermark">

        <div class="idc-compact-head">
            <div class="idc-compact-title">{{ strtoupper($card['front']['title']) }}</div>
            <div class="idc-compact-brand">
                <img src="{{ $document['assets']['leagueLogoLight'] }}" alt="League logo" class="idc-compact-brand-logo">
            </div>
        </div>

        <div class="idc-compact-body">
            <div class="idc-compact-left">
                @foreach (($card['front']['rows'] ?? $card['front']['meta']) as $meta)
                    <div class="idc-compact-row">
                        <span class="idc-compact-label">{{ $meta['label'] }}</span>
                        <span class="idc-compact-sep">:</span>
                        <span class="idc-compact-value">{{ $meta['value'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="idc-compact-right">
                <div class="idc-compact-photo-card">
                    <img src="{{ $card['front']['photoSrc'] }}" alt="{{ $card['front']['name'] }}" class="idc-compact-photo{{ $card['front']['photoMissing'] ? ' idc-photo-placeholder' : '' }}">
                </div>
                <div class="idc-compact-qr-card">
                    <img src="{{ $card['back']['qrSrc'] }}" alt="{{ $card['back']['qrLabel'] }}" class="idc-compact-qr">
                </div>
            </div>
        </div>

    <div class="idc-compact-foot">
        <div class="idc-compact-foot-bar"></div>
        <div class="idc-compact-foot-site">{{ $document['website'] }}</div>
    </div>
</div>
