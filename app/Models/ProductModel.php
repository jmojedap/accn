<?php

namespace App\Models;

use App\Libraries\DbUtils;
use App\Libraries\Search;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * CRUD de products y buscador con filtros, orden y paginacion.
 * Los productos eliminados se conservan con deleted_at para mantener el historial.
 */
class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'idcode',
        'name',
        'slug',
        'status',
        'type_id',
        'product_kind',
        'description',
        'meta_description',
        'currency',
        'cost',
        'base_price',
        'tax_percent',
        'weight_grams',
        'width_cm',
        'height_cm',
        'depth_cm',
        'image_url',
        'creator_id',
        'updater_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'idcode' => 'required|max_length[80]|is_unique[products.idcode,id,{id}]',
        'name' => 'required|max_length[255]',
        'slug' => 'required|max_length[180]|is_unique[products.slug,id,{id}]',
        'status' => 'if_exist|required|in_list[0,1,2]',
        'type_id' => 'if_exist|required|is_natural_no_zero|less_than_equal_to[65535]',
        'product_kind' => 'if_exist|required|in_list[physical,digital,service,access]',
        'description' => 'permit_empty|string',
        'meta_description' => 'permit_empty|max_length[512]',
        'currency' => 'if_exist|required|regex_match[/^[A-Z]{3}$/]',
        'cost' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'base_price' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'tax_percent' => 'if_exist|required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'weight_grams' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'width_cm' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'height_cm' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'depth_cm' => 'if_exist|required|numeric|greater_than_equal_to[0]',
        'image_url' => 'permit_empty|max_length[500]|valid_url',
        'creator_id' => 'permit_empty|is_natural_no_zero|less_than_equal_to[2147483647]',
        'updater_id' => 'permit_empty|is_natural_no_zero|less_than_equal_to[2147483647]',
    ];

    protected $validationMessages = [
        'idcode' => [
            'required' => 'El código del producto es obligatorio.',
            'is_unique' => 'El código del producto ya está registrado.',
        ],
        'name' => [
            'required' => 'El nombre del producto es obligatorio.',
        ],
        'slug' => [
            'required' => 'El slug del producto es obligatorio.',
            'is_unique' => 'El slug del producto ya está registrado.',
        ],
        'base_price' => [
            'greater_than_equal_to' => 'El precio base no puede ser negativo.',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Prepara formularios de productos; la auditoria procede de la sesion.
     */
    public function inputToRow(array $input, ?int $userId = null, bool $creating = false): array
    {
        $fields = array_diff($this->allowedFields, ['creator_id', 'updater_id']);
        $row = array_intersect_key($input, array_flip($fields));
        $row['updater_id'] = $userId;

        if ($creating) {
            $row['creator_id'] = $userId;

            if (! isset($row['idcode']) || $row['idcode'] === '') {
                $row['idcode'] = 'P' . bin2hex(random_bytes(12));
            }

            if ((! isset($row['slug']) || $row['slug'] === '') && is_string($row['name'] ?? null)) {
                $row['slug'] = DbUtils::uniqueSlug($row['name'], $this->table);
            }
        }

        return $row;
    }

// FUNCIONES BUSCADOR DE PRODUCTOS
//-----------------------------------------------------------------------------

    /**
     * Campos del buscador, con los mismos formatos utilizados en PostModel.
     */
    public function selectSegment(string $format = 'default'): string
    {
        $formats = [
            'default' => 'id, idcode, name, slug, status, type_id, product_kind, currency, '
                . 'base_price, tax_percent, image_url, creator_id, updater_id, created_at, updated_at',
            'basic' => 'id, idcode, name, slug, status, type_id, product_kind, currency, base_price, image_url',
            'edition' => '*',
            'admin' => '*',
        ];

        return $formats[$format] ?? $formats['default'];
    }

    /**
     * Devuelve filtros, configuracion, resultados paginados y total de productos.
     */
    public function search(array $input): array
    {
        $filters = $this->searchFilters($input);
        $settings = $this->searchSettings($input);
        $qtyResults = $this->searchBuilder($filters)->countAllResults();
        $queryInput = array_intersect_key(
            array_merge($filters, array_diff_key($settings, ['offset' => true])),
            $input
        );
        $settings['maxPage'] = $qtyResults > 0 ? (int) ceil($qtyResults / $settings['perPage']) : 1;

        return [
            'settings' => $settings,
            'filters' => $filters,
            'results' => $this->searchResults($filters, $settings),
            'getString' => http_build_query(array_filter($queryInput, static fn ($value) => $value !== '')),
            'qtyResults' => $qtyResults,
        ];
    }

    /**
     * Lista de objetos. El primer argumento contiene filtros, no SQL sin procesar.
     */
    public function searchResults(array $filters, array $searchSettings): array
    {
        $settings = $this->searchSettings($searchSettings);
        $builder = $this->searchBuilder($this->searchFilters($filters));
        $builder->select($this->selectSegment($settings['selectFormat']))
            ->orderBy($settings['orderField'], $settings['orderType']);

        if ($settings['orderField'] !== 'id') {
            $builder->orderBy('id', $settings['orderType']);
        }

        return $builder->limit($settings['perPage'], $settings['offset'])->get()->getResult();
    }

    protected function searchFilters(array $input): array
    {
        $filters = Search::filters($input, [
            'q', 'status__eq', 'type_id__eq', 'product_kind__eq', 'currency__eq',
            'creator_id__eq', 'base_price__gte', 'base_price__lte',
        ]);

        foreach ($filters as $key => $value) {
            $filters[$key] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $filters;
    }

    protected function searchSettings(array $input): array
    {
        $perPage = max(1, min(500, filter_var($input['perPage'] ?? 12, FILTER_VALIDATE_INT) ?: 12));
        $numPage = max(1, filter_var($input['numPage'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $orderFields = [
            'id', 'idcode', 'name', 'status', 'type_id', 'product_kind',
            'currency', 'base_price', 'created_at', 'updated_at',
        ];
        $orderType = is_string($input['orderType'] ?? null) ? strtoupper($input['orderType']) : 'DESC';

        return Search::settings([
            'selectFormat' => in_array($input['selectFormat'] ?? '', ['default', 'basic', 'edition', 'admin'], true)
                ? $input['selectFormat'] : 'default',
            'numPage' => min($numPage, intdiv(PHP_INT_MAX, $perPage)),
            'perPage' => $perPage,
            'orderField' => in_array($input['orderField'] ?? '', $orderFields, true) ? $input['orderField'] : 'id',
            'orderType' => in_array($orderType, ['ASC', 'DESC'], true) ? $orderType : 'DESC',
        ]);
    }

    /**
     * Usa la misma conexion y condiciones para el conteo y el listado.
     */
    protected function searchBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db->table($this->table)->where($this->deletedField, null);
        $conditions = [
            'status__eq' => 'status',
            'type_id__eq' => 'type_id',
            'product_kind__eq' => 'product_kind',
            'currency__eq' => 'currency',
            'creator_id__eq' => 'creator_id',
            'base_price__gte' => 'base_price >=',
            'base_price__lte' => 'base_price <=',
        ];

        foreach ($conditions as $filter => $field) {
            if ($filters[$filter] !== '') {
                $builder->where($field, $filters[$filter]);
            }
        }

        foreach (Search::words($filters['q']) as $word) {
            if ($word === '') {
                continue;
            }

            $builder->groupStart();

            foreach (['idcode', 'name', 'description', 'meta_description'] as $field) {
                $builder->orLike($field, $word);
            }

            $builder->groupEnd();
        }

        return $builder;
    }

    /**
     * CI 4.3.2 no incorpora el ID de update() al validar los campos únicos.
     * El identificador de la operación prevalece sobre cualquier ID del payload.
     */
    public function update($id = null, $data = null): bool
    {
        if ($data !== null) {
            $data = $this->transformDataToArray($data, 'update');
            unset($data[$this->primaryKey]);

            if (is_int($id) || is_string($id)) {
                $data[$this->primaryKey] = $id;
            }
        }

        return parent::update($id, $data);
    }
}
