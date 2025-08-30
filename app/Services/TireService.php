<?php
namespace App\Services;

use App\Models\Tire;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TireService
{
    protected $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function getTireById(int $id): ?Tire
    {
        return Tire::find($id);
    }

    // private function generateTireCode(array $data): string
    // {
    //     // Solo genera código si la condición es NUEVO o el campo 'code' está vacío
    //     if (
    //         (isset($data['condition']) && strtoupper($data['condition']) === 'NUEVO') ||
    //         empty($data['code'])
    //     ) {
    //         return 'TIRE-' . now()->format('Ymd-Hi');
    //     }

    //     // Devuelve el código original si no cumple las condiciones anteriores
    //     return $data['code'];
    // }


    public function createTire(array $data): Tire
    {
       
        $data['stock'] = 0;
        $tire = Tire::create($data);
        return $tire;
    }

    public function createMultipleTires(array $data, array $codes): array
    {
        // normalizar códigos (trim)
        $codes = array_values(array_filter(array_map(fn($c) => (string) trim($c), $codes), fn($v) => $v !== ''));

        if (empty($codes)) {
            return ['created' => [], 'failed' => []];
        }

        // 1) Pre-check en BD: códigos que ya existen
        $existing = Tire::whereIn('code', $codes)
                    ->whereNull('deleted_at')
                    ->pluck('code')
                    ->unique()
                    ->values()
                    ->all();

        if (!empty($existing)) {
            return ['created' => [], 'failed' => $existing];
        }

        // 2) Crear todos en transacción. Si algo falla, se revierte.
        try {
            $created = DB::transaction(function () use ($data, $codes) {
                $arr = [];
                foreach ($codes as $code) {
                    // doble check dentro de la transacción para reducir race
                    if (Tire::where('code', $code)->whereNull('deleted_at')->exists()) {
                        // si ya existe durante la transacción, lanzar para que se maneje arriba
                        throw new \RuntimeException("Conflict on code: {$code}");
                    }

                    $payload = array_merge($data, ['code' => $code, 'stock' => $data['stock'] ?? 0]);
                    $arr[] = Tire::create($payload);
                }
                return $arr;
            });

            return ['created' => $created, 'failed' => []];
        } catch (QueryException $qe) {
            // excepción de DB: devolvemos mensaje genérico y marca como fallidos todos (no sabemos exacto)
            return ['created' => [], 'failed' => ['db_error' => $qe->getMessage()]];
        } catch (\RuntimeException $re) {
            // runtime que lanzamos cuando detectamos conflicto dentro de la transacción
            // extraemos el código si viene en el mensaje
            $msg = $re->getMessage();
            if (str_starts_with($msg, 'Conflict on code: ')) {
                $code = substr($msg, strlen('Conflict on code: '));
                return ['created' => [], 'failed' => [$code]];
            }
            return ['created' => [], 'failed' => [$msg]];
        } catch (\Throwable $t) {
            // fallback: no creamos nada y devolvemos el error simple
            return ['created' => [], 'failed' => ['error' => $t->getMessage()]];
        }
    }


    public function updateTire(Tire $tire, array $data): Tire
    {
        $filteredData = array_intersect_key($data, $tire->getAttributes());
        $tire->update($filteredData);
        return Tire::find($tire->id);
    }
    public function destroyById($id)
    {
        return Tire::find($id)?->delete() ?? false;
    }

    public function generatecodes(string $base, int $cant): array
    {
        $baseLen = mb_strlen($base, 'UTF-8');
        $totalLength = 12;
        $seqLength = $totalLength - $baseLen;

        if ($seqLength <= 0) {
            throw new \InvalidArgumentException("La base ocupa {$baseLen} caracteres. Debe tener menos de {$totalLength} caracteres.");
        }

        if ($cant <= 0) {
            return [];
        }

        // máximo posible para la secuencia (ej: seqLength=4 => 9999)
        $max = (int) (pow(10, $seqLength) - 1);
        if ($cant > $max) {
            throw new \InvalidArgumentException("La cantidad solicitada ({$cant}) excede el máximo posible ({$max}) para {$seqLength} dígitos.");
        }

        // Programación funcional: range + array_map
        $numbers = range(1, $cant); // siempre inicia en 1
        $codes = array_map(
            fn(int $n) => $this->concatWithSequence($base, $n, $seqLength),
            $numbers
        );

        return $codes;
    }

    private function concatWithSequence(string $base, int $number, int $seqLength): string
    {
        $seq = str_pad((string) $number, $seqLength, '0', STR_PAD_LEFT);
        return $base . $seq;
    }

}
