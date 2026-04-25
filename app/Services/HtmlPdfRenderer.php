<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Throwable;

class HtmlPdfRenderer
{
    public function renderView(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $html = view($view, $data)->render();

        return $this->renderHtml($html, $paper, $orientation, $options);
    }

    public function renderHtml(string $html, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $chromePath = $this->detectExecutable([
            (string) config('id-cards.chrome_path'),
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium-browser',
            '/usr/local/bin/chromium',
            'google-chrome',
            'chromium-browser',
            'chromium',
        ]);

        $nodeBinary = $this->detectExecutable([
            (string) config('id-cards.node_binary'),
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
            'node',
        ]);

        if ($chromePath === null || $nodeBinary === null) {
            return $this->renderWithDomPdf($html, $paper, $orientation);
        }

        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->emulateMedia('screen')
            ->format(strtoupper($paper))
            ->margins(0, 0, 0, 0)
            ->setOption('preferCSSPageSize', true)
            ->setOption('printBackground', true)
            ->setOption('displayHeaderFooter', false)
            ->setOption('waitUntil', (string) config('id-cards.wait_until', 'load'))
            ->timeout((int) config('id-cards.timeout', 90))
            ->addChromiumArguments([
                'disable-dev-shm-usage',
                'font-render-hinting=medium',
            ]);

        if ($orientation === 'landscape') {
            $browsershot->landscape();
        }

        $browsershot->setChromePath($chromePath);
        $browsershot->setNodeBinary($nodeBinary);

        $nodeModulesPath = (string) config('id-cards.node_modules_path');
        if ($nodeModulesPath !== '' && is_dir($nodeModulesPath)) {
            $browsershot->setNodeModulePath($nodeModulesPath);
        }

        if (config('id-cards.no_sandbox', true)) {
            $browsershot->noSandbox();
        }

        if (! empty($options['wait_for_function'])) {
            $browsershot->waitForFunction(
                (string) $options['wait_for_function'],
                timeout: (int) ($options['wait_for_function_timeout'] ?? 0),
            );
        }

        if (! empty($options['delay'])) {
            $browsershot->delay((int) $options['delay']);
        }

        try {
            return $browsershot->pdf();
        } catch (Throwable) {
            return $this->renderWithDomPdf($html, $paper, $orientation);
        }
    }

    public function renderUrl(string $url, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $chromePath = $this->detectExecutable([
            (string) config('id-cards.chrome_path'),
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium-browser',
            '/usr/local/bin/chromium',
            'google-chrome',
            'chromium-browser',
            'chromium',
        ]);

        $nodeBinary = $this->detectExecutable([
            (string) config('id-cards.node_binary'),
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
            'node',
        ]);

        if ($chromePath === null || $nodeBinary === null) {
            throw new \RuntimeException('Chrome or Node binary is not available for URL PDF rendering.');
        }

        $browsershot = Browsershot::url($url)
            ->showBackground()
            ->emulateMedia('screen')
            ->format(strtoupper($paper))
            ->margins(0, 0, 0, 0)
            ->setOption('preferCSSPageSize', true)
            ->setOption('printBackground', true)
            ->setOption('displayHeaderFooter', false)
            ->setOption('waitUntil', (string) config('id-cards.wait_until', 'load'))
            ->timeout((int) config('id-cards.timeout', 90))
            ->addChromiumArguments([
                'disable-dev-shm-usage',
                'font-render-hinting=medium',
            ]);

        if ($orientation === 'landscape') {
            $browsershot->landscape();
        }

        $browsershot->setChromePath($chromePath);
        $browsershot->setNodeBinary($nodeBinary);

        $nodeModulesPath = (string) config('id-cards.node_modules_path');
        if ($nodeModulesPath !== '' && is_dir($nodeModulesPath)) {
            $browsershot->setNodeModulePath($nodeModulesPath);
        }

        if (config('id-cards.no_sandbox', true)) {
            $browsershot->noSandbox();
        }

        if (! empty($options['cookies']) && is_array($options['cookies'])) {
            $domain = parse_url($url, PHP_URL_HOST) ?: 'localhost';
            $browsershot->useCookies($options['cookies'], $domain);
        }

        if (! empty($options['wait_for_function'])) {
            $browsershot->waitForFunction(
                (string) $options['wait_for_function'],
                timeout: (int) ($options['wait_for_function_timeout'] ?? 0),
            );
        }

        if (! empty($options['delay'])) {
            $browsershot->delay((int) $options['delay']);
        }

        return $browsershot->pdf();
    }

    private function renderWithDomPdf(string $html, string $paper, string $orientation): string
    {
        return Pdf::loadHTML($html)
            ->setPaper($paper, $orientation)
            ->output();
    }

    private function detectExecutable(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            if (str_contains($candidate, DIRECTORY_SEPARATOR)) {
                if (is_executable($candidate)) {
                    return $candidate;
                }

                continue;
            }

            $resolved = trim((string) shell_exec('command -v '.escapeshellarg($candidate).' 2>/dev/null'));
            if ($resolved !== '' && is_executable($resolved)) {
                return $resolved;
            }
        }

        return null;
    }
}
