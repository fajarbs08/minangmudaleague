<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class HtmlPdfRenderer
{
    public function renderView(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $html = view($view, $data)->render();

        return $this->renderHtml($html, $paper, $orientation, $options);
    }

    public function renderHtml(string $html, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        return Pdf::setOption([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 96,
        ])
            ->loadHTML($html)
            ->setPaper($paper, $orientation)
            ->output();
    }

    public function renderUrl(string $url, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        throw new RuntimeException('Render PDF dari URL tidak didukung lagi setelah migrasi ke dompdf. Render view atau HTML secara langsung.');
    }
}
