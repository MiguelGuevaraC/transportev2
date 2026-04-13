<?php
namespace App\Traits;

use App\Utils\Constants;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    protected function applyFilters($query, $request, $filters)
    {
        foreach ($filters as $filter => $operator) {
            $paramName = str_replace('.', '$', $filter);
            $value     = $request->query($paramName);

            // Si el filtro usa 'between', verificamos la existencia de 'from' y 'to'
            if ($operator === 'between') {
                $from = $request->query('from');
                $to   = $request->query('to');

                if ($from || $to) {
                    $this->applyFilterCondition($query, $filter, $operator, compact('from', 'to'));
                    continue; // Saltamos al siguiente filtro ya que se ha aplicado el between
                }
            }

            // Cadenas vacías en query (?branch_office_id=) no deben filtrar (antes daban 0 filas).
            if ($value !== null && $value !== '') {
                if (strpos($filter, '.') !== false) {
                    [$relation, $relationFilter] = explode('.', $filter);
                    $query->whereHas($relation, function ($q) use ($relationFilter, $operator, $value) {
                        $this->applyFilterCondition($q, $relationFilter, $operator, $value);
                    });
                } else {
                    $this->applyFilterCondition($query, $filter, $operator, $value);
                }
            }
        }

        return $query;
    }

    protected function applyFilterCondition($query, $filter, $operator, $value)
    {
        if ($operator === 'between' && is_array($value)) {
            $from = $value['from'] ?? null;
            $to   = $value['to'] ?? null;

            if ($from && $to) {
                $query->whereBetween($filter, [$from, $to]);
            } elseif ($from) {
                $query->where($filter, '>=', $from);
            } elseif ($to) {
                $query->where($filter, '<=', $to);
            }
            return;
        }

        switch ($operator) {
            case 'like':
                $query->where($filter, 'like', '%' . $value . '%');
                break;
            case '>':
                $query->where($filter, '>', $value);
                break;
                case 'in':
         
                    $query->whereIn($filter, (array) $value);
                    break;
                
     
            case '<':
                $query->where($filter, '<', $value);
                break;
            case '>=':
                $query->where($filter, '>=', $value);
                break;
            case '<=':
                $query->where($filter, '<=', $value);
                break;
            case '=':
                $query->where($filter, '=', $value);
                break;
            case 'date': // Nuevo operador para filtrar por una fecha exacta
                $query->whereDate($filter, '=', $value);
                break;
            default:
                break;
        }
    }

    protected function applySorting($query, $request, $sorts)
    {
        $sortField = $request->query('sort');
        $sortOrder = $request->query('direction', 'desc');
  
        if ($sortField !== null && array_key_exists($sortField, $sorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('id', $sortOrder);
        }
        return $query;
    }

    protected function getFilteredResults($modelOrQuery, $request, $filters, $sorts, $resource)
    {
        if ($modelOrQuery instanceof Builder) {
            $query = $modelOrQuery;
        } else {
            $query = $modelOrQuery::query();
        }

        $query = $this->applyFilters($query, $request, $filters);
        $query = $this->applySorting($query, $request, $sorts);

        $all = $request->query('all', false) === 'true';
        if ($all) {
            $results = $query->take(1000)->get();

            return response()->json($resource::collection($results));
        }

        $perPage = $request->query('per_page');
        if ($perPage === null || $perPage === '') {
            $perPage = $request->query('row');
        }
        if ($perPage === null || $perPage === '') {
            $perPage = Constants::DEFAULT_PER_PAGE;
        }
        $perPage = (int) $perPage;
        if ($perPage < 1) {
            $perPage = Constants::DEFAULT_PER_PAGE;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        $results = $query->paginate($perPage);

        return $resource::collection($results);
    }
}
