<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class MakeCrud extends Command
{

    protected $signature = 'make:crud {name} {fields*}';
    protected $description = 'Genera CRUD completo con Swagger: modelo, controlador, requests, service, resource, ruta, migración y seeder';

    protected $group = "Taller";
    public function handle()
    {

        $name = Str::studly($this->argument('name'));
        $lowerName = Str::lower($name);
        $fields = $this->argument('fields');

        $fillableFields = collect($fields)
            ->map(fn($field) => "'" . explode(':', $field)[0] . "'")
            ->push("'created_at'")
            ->implode(', ');

        $this->generateModel($name, $fillableFields);
        $this->generateMigration($name, $fields);
        $this->generateSeeder($name, $fields);
        $this->generateResource($name, $fields);
        $this->generateService($name);
        $this->generateRequests($name, $fields);
        $this->generateController($name);
        $this->generateRouteFile($name);

        $this->info("\n✅ CRUD generado para el modelo $name");
    }

    protected function generateModel($name, $fillable)
    {
        $hidden = "'updated_at',\n        'deleted_at'";
        $filters = "'name'=> 'like',\n        'state'=> '=',\n        'created_at'=> 'date'";
        $sorts = "'id' => 'desc'";

        $content = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$name} extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected \$fillable = [
        {$fillable}
    ];

    protected \$hidden = [
        {$hidden}
    ];

    const filters = [
        {$filters}
    ];

    const sorts = [
        {$sorts}
    ];
}
PHP;
        File::put(app_path("Models/{$name}.php"), $content);
    }

    protected function generateMigration($name, $fields)
    {
        $tableName = Str::snake(Str::pluralStudly($name));
        $columns = collect($fields)->map(function ($field) {
            [$fname, $ftype] = explode(':', $field);
            return match ($ftype) {
                'string' => "\$table->string('$fname')->nullable();",
                'boolean' => "\$table->boolean('$fname')->default(1)->nullable();",
                'integer' => "\$table->integer('$fname')->nullable();",
                'date' => "\$table->date('$fname')->nullable();",
                'datetime' => "\$table->dateTime('$fname')->nullable();",
                'text' => "\$table->text('$fname')->nullable();",
                'json' => "\$table->json('$fname')->nullable();",
                'decimal' => "\$table->decimal('$fname', 8, 2)->nullable();",
                default => "\$table->string('$fname')->nullable();"
            };
        })->implode("\n            ");

        $migrationContent = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->id();
            {$columns}
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('$tableName');
    }
};
PHP;

        $timestamp = now()->format('Y_m_d_His');
        File::put(database_path("migrations/{$timestamp}_create_{$tableName}_table.php"), $migrationContent);
    }


    protected function generateSeeder($name, $fields)
    {
        $tableName = Str::snake(Str::pluralStudly($name));
        $fieldsArray = collect($fields)->map(function ($field) {
            [$fname, $ftype] = explode(':', $field);
            $value = match ($ftype) {
                'string' => "'Example $fname'",
                'boolean' => 'true',
                'integer' => '1',
                'date', 'datetime' => 'now()',
                default => "'Sample'"
            };
            return "'$fname' => $value";
        })->implode(",\n            ");

        $seederContent = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$name}Seeder extends Seeder
{
    public function run(): void
    {
        DB::table('$tableName')->insert([
            [
                {$fieldsArray},
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
PHP;

        File::put(database_path("seeders/{$name}Seeder.php"), $seederContent);
    }

    protected function generateResource($name, $fields)
    {
        $propertiesArray = collect($fields)->map(function ($field) {
            $nameField = explode(':', $field)[0];
            return "'$nameField' => \$this->$nameField ?? null";
        });

        $docProps = collect($fields)->map(function ($field) {
            [$nameField, $type] = explode(':', $field);
            return " *     @OA\\Property(property=\"$nameField\", type=\"$type\")";
        })->implode("\n");

        // Construcción segura del array
        $arrayItems = collect([
            "'id' => \$this->id ?? null",
            ...$propertiesArray,
            "'created_at' => \$this->created_at?->format('Y-m-d H:i:s')"
        ])->implode(",\n            ");

        $content = <<<PHP
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="{$name}Resource",
{$docProps}
 * )
 */
class {$name}Resource extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            {$arrayItems}
        ];
    }
}
PHP;

        File::put(app_path("Http/Resources/{$name}Resource.php"), $content);
    }



    protected function generateService($name)
    {
        $content = <<<PHP
<?php

namespace App\Services;

use App\Models\\$name;

class {$name}Service
{
    public function get{$name}ById(int \$id): ?$name
    {
        return $name::find(\$id);
    }

    public function create{$name}(array \$data): $name
    {
        return $name::create(\$data);
    }

     public function update{$name}($name \$instance, array \$data): $name
    {
        \$filteredData = array_intersect_key(\$data, \$instance->getAttributes());
        \$instance->update(\$filteredData);
        return \$instance;
    }

    public function destroyById(\$id)
    {
        return $name::find(\$id)?->delete() ?? false;
    }
}
PHP;

        File::put(app_path("Services/{$name}Service.php"), $content);
    }

    protected function generateRequests($name, $fields)
    {
        $rules = collect($fields)->map(function ($field) {
            [$fname, $ftype] = explode(':', $field);
            return "'$fname' => ['required', '$ftype'],";
        })->implode("\n            ");

        $optionalRules = str_replace('required', 'nullable', $rules);

        $messages = collect($fields)->map(function ($field) {
            [$fname,] = explode(':', $field);
            return [
                "'$fname.required' => 'El campo $fname es obligatorio.'",
                "'$fname.$fname' => 'El formato del campo $fname es inválido.'"
            ];
        })->flatten()->implode(",\n            ");

        $docProps = collect($fields)->map(function ($field) {
            [$fname, $ftype] = explode(':', $field);
            return " *     @OA\\Property(property=\"$fname\", type=\"$ftype\")";
        })->implode("\n");

        $required = collect($fields)->map(fn($f) => '"' . explode(':', $f)[0] . '"')->implode(', ');

        File::ensureDirectoryExists(app_path("Http/Requests/{$name}Request"));

        File::put(app_path("Http/Requests/{$name}Request/Store{$name}Request.php"), <<<PHP
<?php

namespace App\Http\Requests\\{$name}Request;

use App\Http\Requests\StoreRequest;

/**
 * @OA\Schema(
 *     schema="Store{$name}Request",
 *     required={{$required}},
{$docProps}
 * )
 */
class Store{$name}Request extends StoreRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            {$rules}
        ];
    }

    public function messages()
    {
        return [
            {$messages}
        ];
    }
}
PHP);

        File::put(app_path("Http/Requests/{$name}Request/Update{$name}Request.php"), <<<PHP
<?php

namespace App\Http\Requests\\{$name}Request;

use App\Http\Requests\UpdateRequest;

/**
 * @OA\Schema(
 *     schema="Update{$name}Request",
{$docProps}
 * )
 */
class Update{$name}Request extends UpdateRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            {$optionalRules}
        ];
    }

    public function messages()
    {
        return [
            {$messages}
        ];
    }
}
PHP);

        File::put(app_path("Http/Requests/{$name}Request/Index{$name}Request.php"), <<<PHP
<?php

namespace App\Http\Requests\\{$name}Request;

use App\Http\Requests\IndexRequest;

class Index{$name}Request extends IndexRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            {$optionalRules}
        ];
    }

    public function messages()
    {
        return [
            {$messages}
        ];
    }
}
PHP);
    }


    protected function generateController($name)
    {
        $namespace = "App\\Http\\Controllers\\{$name}";
        $indexRequest = "App\\Http\\Requests\\{$name}Request\\Index{$name}Request";
        $storeRequest = "App\\Http\\Requests\\{$name}Request\\Store{$name}Request";
        $updateRequest = "App\\Http\\Requests\\{$name}Request\\Update{$name}Request";
        $resource = "App\\Http\\Resources\\{$name}Resource";
        $model = "App\\Models\\{$name}";
        $service = "App\\Services\\{$name}Service";

        $content = <<<PHP
<?php

namespace App\Http\Controllers\\{$this->group};

use App\Http\Controllers\Controller;
use {$indexRequest};
use {$storeRequest};
use {$updateRequest};
use {$resource};
use {$model};
use {$service};

class {$name}Controller extends Controller
{
    protected \$service;

    public function __construct({$name}Service \$service)
    {
        \$this->service = \$service;
    }

    public function list(Index{$name}Request \$request)
    {
        return \$this->getFilteredResults(
            {$name}::class,
            \$request,
            {$name}::filters,
            {$name}::sorts,
            {$name}Resource::class
        );
    }

    public function show(\$id)
    {
        \$item = \$this->service->get{$name}ById(\$id);
        if (!\$item) return response()->json(['error' => '{$name} no encontrado'], 404);
        return new {$name}Resource(\$item);
    }

    public function store(Store{$name}Request \$request)
    {
        \$item = \$this->service->create{$name}(\$request->validated());
        return new {$name}Resource(\$item);
    }

    public function update(Update{$name}Request \$request, \$id)
    {
        \$item = \$this->service->get{$name}ById(\$id);
        if (!\$item) return response()->json(['error' => '{$name} no encontrado'], 404);
        \$item = \$this->service->update{$name}(\$item, \$request->validated());
        return new {$name}Resource(\$item);
    }

    public function destroy(\$id)
    {
        \$item = \$this->service->get{$name}ById(\$id);
        if (!\$item) return response()->json(['error' => '{$name} no encontrado'], 404);
        \$this->service->destroyById(\$id);
        return response()->json(['message' => '{$name} eliminado'], 200);
    }
}
PHP;

        File::ensureDirectoryExists(app_path("Http/Controllers/{$this->group}"));
        File::put(app_path("Http/Controllers/{$this->group}/{$name}Controller.php"), $content);
    }

    protected function generateRouteFile($name)
    {
        $lower = strtolower($name);
        $routes = <<<PHP
<?php

use App\Http\Controllers\\{$this->group}\\{$name}Controller;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('$lower', [{$name}Controller::class, 'list']);
    Route::get('$lower/{id}', [{$name}Controller::class, 'show']);
    Route::post('$lower', [{$name}Controller::class, 'store']);
    Route::put('$lower/{id}', [{$name}Controller::class, 'update']);
    Route::delete('$lower/{id}', [{$name}Controller::class, 'destroy']);
});
PHP;

        File::put(base_path("routes/api/{$name}Api.php"), $routes);
    }
}
