<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\BranchOffice;
use App\Models\Product;
use App\Models\ProductStockByBranch;
use App\Models\Seccion;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\Writer\PngWriter;

class WarehousePositionStickerService
{
    /**
     * Genera una etiqueta imprimible (SVG) con producto, posición y contexto de almacén.
     */
    public function buildSvgSticker(
        int $productId,
        string $positionCode,
        int $almacenId,
        ?int $seccionId,
        int $branchOfficeId
    ): string {
        $product = Product::query()->whereNull('deleted_at')->findOrFail($productId);
        $almacen = Almacen::query()->whereNull('deleted_at')->find($almacenId);
        $seccion = $seccionId ? Seccion::query()->whereNull('deleted_at')->find($seccionId) : null;
        $branch  = BranchOffice::query()->whereNull('deleted_at')->find($branchOfficeId);

        $stockQuery = ProductStockByBranch::query()
            ->where('product_id', $productId)
            ->where('branchOffice_id', $branchOfficeId)
            ->where('almacen_id', $almacenId)
            ->where('position_code', $positionCode);

        if ($seccionId !== null && $seccionId !== 0) {
            $stockQuery->where('seccion_id', $seccionId);
        }

        $stockRow = $stockQuery->orderByDesc('stock')->first();

        $payload = [
            'product_id'    => $productId,
            'position_code' => $positionCode,
            'almacen_id'    => $almacenId,
            'seccion_id'    => $seccionId,
            'branch_id'     => $branchOfficeId,
        ];
        $qrPngBase64 = $this->buildQrPngBase64(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $lines = [
            'Producto:',
            ...$this->wrapLines((string) $product->description, 42),
            '',
            'Posición: ' . $positionCode,
            'Sucursal: ' . ($branch->name ?? '—'),
            'Almacén: ' . ($almacen->name ?? '—'),
            'Sección: ' . ($seccion->name ?? '—'),
        ];

        if ($stockRow !== null) {
            $lines[] = 'Lote: ' . ($stockRow->num_lot ?? '—');
            if ($stockRow->date_expiration) {
                $lines[] = 'Venc.: ' . Carbon::parse($stockRow->date_expiration)->format('d/m/Y');
            }
            $lines[] = 'Stock: ' . rtrim(rtrim(number_format((float) $stockRow->stock, 2, '.', ''), '0'), '.');
        }

        $tspans = '';
        $y      = 26;
        foreach ($lines as $line) {
            $text = htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $tspans .= '<tspan x="16" y="' . $y . '">' . $text . '</tspan>';
            $y += 18;
        }

        $svg = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="420" height="360" viewBox="0 0 420 360">'
            . '<rect x="2" y="2" width="416" height="356" fill="#ffffff" stroke="#222222" stroke-width="2" rx="6"/>'
            . '<text font-family="system-ui, Segoe UI, Arial, sans-serif" font-size="13" fill="#111111">'
            . $tspans
            . '</text>'
            . '<image x="268" y="16" width="136" height="136" xlink:href="data:image/png;base64,' . $qrPngBase64 . '" preserveAspectRatio="xMidYMid meet"/>'
            . '</svg>';

        return $svg;
    }

    protected function wrapLines(string $text, int $width): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') {
            return ['—'];
        }

        return explode("\n", wordwrap($text, $width, "\n", true));
    }

    protected function buildQrPngBase64(string $data): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelMedium())
            ->size(200)
            ->margin(8)
            ->build();

        return base64_encode($result->getString());
    }
}
