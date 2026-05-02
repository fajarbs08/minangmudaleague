<?php

namespace App\Services;

use RuntimeException;
use Spatie\Browsershot\Browsershot;

class HtmlPdfRenderer
{
    public function renderView(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $html = view($view, $data)->render();

        return $this->renderHtml($html, $paper, $orientation, $options);
    }

    public function renderHtml(string $html, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $chromePath = $this->detectExecutable((string) config('id-cards.chrome_path'));
        $nodeBinary = $this->detectExecutable((string) config('id-cards.node_binary'));

        if ($chromePath === null || $nodeBinary === null) {
            throw new RuntimeException('Browsershot membutuhkan Chrome/Chromium dan Node yang valid. Set ID_CARDS_CHROME_PATH dan ID_CARDS_NODE_BINARY ke path executable yang benar.');
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

        return $browsershot->pdf();
    }

    public function renderUrl(string $url, string $paper = 'a4', string $orientation = 'portrait', array $options = []): string
    {
        $chromePath = $this->detectExecutable((string) config('id-cards.chrome_path'));
        $nodeBinary = $this->detectExecutable((string) config('id-cards.node_binary'));

        if ($chromePath === null || $nodeBinary === null) {
            throw new RuntimeException('Browsershot membutuhkan Chrome/Chromium dan Node yang valid. Set ID_CARDS_CHROME_PATH dan ID_CARDS_NODE_BINARY ke path executable yang benar.');
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

    private function detectExecutable(string $candidate): ?string
    {
        $candidate = trim($candidate);

        if ($candidate === '') {
            return null;
        }

        if (is_executable($candidate)) {
            return $candidate;
        }

        return null;
    }
}
