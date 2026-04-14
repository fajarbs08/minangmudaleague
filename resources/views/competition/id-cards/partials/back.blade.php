<div class="idc-page">
    <div class="idc-card idc-card--back">
        <img src="{{ $document['assets']['leagueWatermark'] }}" alt="" class="idc-watermark">
        <div class="idc-header-glow"></div>

        <div class="idc-back-shell">
            <div class="idc-back-head">
                <div>
                    <h2 class="idc-back-title">{{ $card['back']['title'] }}</h2>
                    <div class="idc-back-subtitle">{{ $card['back']['subtitle'] }}</div>
                </div>
                <img src="{{ $document['club']['logoSrc'] }}" alt="Club mark" class="idc-club-mark">
            </div>

            <div class="idc-back-main">
                <div class="idc-facts">
                    @foreach ($card['back']['facts'] as $fact)
                        <div class="idc-fact">
                            <div class="idc-fact-label">{{ $fact['label'] }}</div>
                            <div class="idc-fact-value">{{ $fact['value'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="idc-back-side">
                    <div class="idc-qr-panel">
                        <img src="{{ $card['back']['qrSrc'] }}" alt="{{ $card['back']['qrLabel'] }}" class="idc-qr">
                        <div class="idc-qr-label">{{ $card['back']['qrLabel'] }}</div>
                    </div>
                    <div class="idc-chip idc-chip--muted idc-back-chip">Valid</div>
                </div>
            </div>

            <div class="idc-notes">
                @foreach ($card['back']['detailLines'] as $detailLine)
                    <div class="idc-note-line">{{ $detailLine }}</div>
                @endforeach
                <div class="idc-disclaimer">{{ $card['back']['disclaimer'] }}</div>
            </div>
        </div>

        <div class="idc-back-footer">
            <div class="idc-verify">
                <strong>Verify Online</strong>
                {{ $card['back']['verificationUrl'] }}
            </div>
            <div class="idc-back-footer-right">
                <div class="idc-organizer">
                    <strong>Organizer</strong>
                    {{ $document['organizer'] }}
                </div>
                <div class="idc-verify-url">{{ $document['website'] }}</div>
            </div>
        </div>
    </div>
</div>
