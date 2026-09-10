<?php

namespace Webkul\Core\Traits;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

trait PDFHandler
{
    /**
     * Download PDF as a streamed response.
     */
    public function downloadPDF(string $html, ?string $fileName = null)
    {
        $fileName = $this->resolvePdfFileName($fileName);

        if (class_exists(Mpdf::class)) {
            $mpdf = $this->buildMpdf($html);

            return response()->streamDownload(
                fn () => print ($mpdf->Output('', 'S')),
                $fileName.'.pdf'
            );
        }

        $html = $this->preparePdfHtml($html);

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->set_option('defaultFont', 'Courier')
            ->download($fileName.'.pdf');
    }

    /**
     * Generate pdf content.
     */
    public function generatePdf(string $html): string
    {
        if (class_exists(Mpdf::class)) {
            return $this->buildMpdf($html)->Output('', 'S');
        }

        $html = $this->preparePdfHtml($html);

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->set_option('defaultFont', 'Courier')
            ->output();
    }

    /**
     * Build and configure an mPDF instance with the given HTML.
     */
    private function buildMpdf(string $html): Mpdf
    {
        $tempDir = storage_path('app/mpdf');

        if (! file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        if (! is_writable($tempDir)) {
            $tempDir = sys_get_temp_dir();
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => $tempDir,
        ]);

        if ($this->isRtlLocale()) {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);

        return $mpdf;
    }

    /**
     * Prepare HTML for PDF rendering: encode and adjust Arabic/Persian glyphs.
     */
    private function preparePdfHtml(string $html): string
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        return $this->adjustArabicAndPersianContent($html);
    }

    /**
     * Determine if the current locale uses RTL direction.
     */
    private function isRtlLocale(): bool
    {
        return core()->getCurrentLocale()->direction === 'rtl';
    }

    /**
     * Resolve a PDF file name, generating a random one if not provided.
     */
    private function resolvePdfFileName(?string $fileName): string
    {
        return $fileName ?? Str::random(32);
    }

    /**
     * Adjust Arabic and Persian glyph rendering for PDF output.
     */
    private function adjustArabicAndPersianContent(string $html): string
    {
        $arabic = new Arabic;

        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $segment = substr($html, $positions[$i - 1], $positions[$i] - $positions[$i - 1]);

            $converted = $arabic->utf8Glyphs($segment);

            $html = substr_replace($html, $converted, $positions[$i - 1], $positions[$i] - $positions[$i - 1]);
        }

        return $html;
    }
}
