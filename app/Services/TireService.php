<?php
namespace App\Services;

use App\Models\DocAlmacen;
use App\Models\Tire;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
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

        // 2) Crear todos en transacción (neumáticos + doc almacén + detalles)
        try {
            $result = DB::transaction(function () use ($data, $codes) {
                $createdTires = [];

                // Crear neumáticos
                foreach ($codes as $code) {
                    if (Tire::where('code', $code)->whereNull('deleted_at')->exists()) {
                        throw new \RuntimeException("Conflict on code: {$code}");
                    }

                    $payload = array_merge($data, [
                        'code' => $code,
                        'stock' => $data['stock'] ?? 0
                    ]);

                    $createdTires[] = Tire::create($payload);
                }

                // Crear documento de almacén (concepto fijo = 2, ingreso)
                $docAlmacen = DocAlmacen::create([
                    'concept_id' => 2,
                    'type' => 'INGRESO',
                    'movement_date' => now()->toDateString(),
                    'note' => 'Compra de neumáticos',
                    'user_id' => Auth::id(),
                ]);

                // Crear un detalle por cada neumático
                foreach ($createdTires as $tire) {
                    $docAlmacen->details()->create([
                        'tire_id' => $tire->id,
                        'quantity' => 1,
                        'note' => 'Ingreso automático por compra',
                        'previous_quantity' => 0,
                        'new_quantity' => 1,
                        'unit_price' => $tire->precioventa ?? 0,
                        'total_value' => ($tire->precioventa ?? 0) * 1,
                    ]);

                    // actualizar stock del neumático
                    $tire->stock = 1;
                    $tire->save();
                }

                return ['created' => $createdTires, 'failed' => []];
            });

            return $result;

        } catch (QueryException $qe) {
            return ['created' => [], 'failed' => ['db_error' => $qe->getMessage()]];
        } catch (\RuntimeException $re) {
            $msg = $re->getMessage();
            if (str_starts_with($msg, 'Conflict on code: ')) {
                $code = substr($msg, strlen('Conflict on code: '));
                return ['created' => [], 'failed' => [$code]];
            }
            return ['created' => [], 'failed' => [$msg]];
        } catch (\Throwable $t) {
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
