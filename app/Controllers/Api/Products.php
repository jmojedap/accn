<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;
use JsonException;

class Products extends BaseController
{
    protected ProductModel $productModel;

    public function __construct(?ProductModel $productModel = null)
    {
        $this->productModel = $productModel ?? new ProductModel();
    }

    public function index(): ResponseInterface
    {
        return $this->search();
    }

    public function search(): ResponseInterface
    {
        $input = $this->inputData(true);

        if ($input instanceof ResponseInterface) {
            return $input;
        }

        return $this->response->setJSON($this->productModel->search($input));
    }

    public function info(string $idCode): ResponseInterface
    {
        $product = $this->productModel->where('idcode', $idCode)->first();

        return $product === null
            ? $this->notFound($idCode)
            : $this->response->setJSON(['row' => $product]);
    }

    /**
     * Valida la creacion o, si se proporciona id, la edicion de ese producto.
     */
    public function validateForm(): ResponseInterface
    {
        $input = $this->inputData();

        if ($input instanceof ResponseInterface) {
            return $input;
        }

        $id = $input['id'] ?? 0;
        $creating = $id === 0 || $id === '0' || $id === '' || $id === null;

        if (! $creating) {
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id === false) {
                return $this->response->setStatusCode(400)->setJSON(['code' => 'INVALID_ID']);
            }

            if ($this->productModel->find($id) === null) {
                return $this->notFound((string) $id);
            }
        }

        $row = $this->productModel->inputToRow($input, $this->currentUserId(), $creating);
        $errors = $this->inputErrors($row);

        if ($errors === []) {
            if (! $creating) {
                $row['id'] = $id;
            }

            $valid = $this->productModel->cleanRules(! $creating)->validate($row);
            $errors = $valid ? [] : $this->productModel->errors();
            $this->productModel->cleanRules(true);
        }

        return $this->response->setJSON([
            'status' => $errors === [] ? 1 : 0,
            'error' => $errors === [] ? null : implode(' ', $errors),
            'errors' => $errors,
        ]);
    }

    public function create(): ResponseInterface
    {
        return $this->persist();
    }

    public function update(string $idCode): ResponseInterface
    {
        $product = $this->productModel->where('idcode', $idCode)->first();

        return $product === null ? $this->notFound($idCode) : $this->persist($product);
    }

    public function save(string $idCode = '0'): ResponseInterface
    {
        return $idCode === '0' ? $this->create() : $this->update($idCode);
    }

    public function delete(string $idCode): ResponseInterface
    {
        $result = $this->deleteProduct($idCode);
        $status = $result['code'] === 'NOT_FOUND' ? 404 : ($result['code'] === 'DELETED' ? 200 : 500);

        return $this->response->setStatusCode($status)->setJSON($result);
    }

    public function deleteSelected(): ResponseInterface
    {
        $input = $this->inputData();

        if ($input instanceof ResponseInterface) {
            return $input;
        }

        $ids = $input['ids'] ?? [];

        if (! is_array($ids) || $ids === [] || count($ids) > 500) {
            return $this->response->setStatusCode(400)->setJSON(['code' => 'INVALID_IDS']);
        }

        foreach ($ids as $idCode) {
            if ((! is_string($idCode) && ! is_int($idCode)) || trim((string) $idCode) === '' || mb_strlen((string) $idCode) > 80) {
                return $this->response->setStatusCode(400)->setJSON(['code' => 'INVALID_IDS']);
            }
        }

        $results = [];

        foreach (array_unique(array_map('strval', $ids)) as $idCode) {
            $results[] = $this->deleteProduct($idCode);
        }

        $deleted = count(array_filter($results, static fn ($result) => $result['code'] === 'DELETED'));

        return $this->response->setJSON([
            'summary' => [
                'total' => count($results),
                'deleted' => $deleted,
                'not_deleted' => count($results) - $deleted,
            ],
            'deleteResults' => $results,
        ]);
    }

    protected function persist(?object $product = null): ResponseInterface
    {
        $input = $this->inputData();

        if ($input instanceof ResponseInterface) {
            return $input;
        }

        $creating = $product === null;
        $row = $this->productModel->inputToRow($input, $this->currentUserId(), $creating);
        $errors = $this->inputErrors($row);
        $savedId = 0;

        if ($errors === []) {
            if ($creating) {
                $savedId = $this->productModel->insert($row) ?: 0;
            } elseif ($this->productModel->update($product->id, $row)) {
                $savedId = $product->id;
            }

            if (! $savedId) {
                $errors = $this->productModel->errors();
            }
        }

        return $this->response->setStatusCode($savedId ? ($creating ? 201 : 200) : 422)->setJSON([
            'saved' => (bool) $savedId,
            'savedId' => (int) $savedId,
            'idcode' => $savedId ? ($row['idcode'] ?? $product->idcode) : null,
            'errors' => $errors,
        ]);
    }

    protected function deleteProduct(string $idCode): array
    {
        $product = $this->productModel->where('idcode', $idCode)->first();

        if ($product === null) {
            return ['idcode' => $idCode, 'code' => 'NOT_FOUND'];
        }

        return [
            'idcode' => $idCode,
            'code' => $this->productModel->delete($product->id) ? 'DELETED' : 'DELETE_FAILED',
        ];
    }

    protected function currentUserId(): ?int
    {
        $id = filter_var(service('session')->get('user_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    protected function inputErrors(array $row): array
    {
        $errors = [];

        foreach ($row as $field => $value) {
            if ($value !== null && ! is_scalar($value)) {
                $errors[$field] = 'El campo debe contener un valor simple.';
            }
        }

        return $errors;
    }

    protected function notFound(string $idCode): ResponseInterface
    {
        return $this->response->setStatusCode(404)->setJSON(['idcode' => $idCode, 'code' => 'NOT_FOUND', 'savedId' => 0]);
    }

    /**
     * Admite formularios y objetos JSON enviados mediante Axios.
     */
    protected function inputData(bool $includeQuery = false): array|ResponseInterface
    {
        if ($includeQuery && strtolower($this->request->getMethod()) === 'get') {
            return $this->request->getGet() ?? [];
        }

        if (stripos($this->request->getHeaderLine('Content-Type'), 'json') !== false) {
            $body = trim($this->request->getBody());

            try {
                $input = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return $this->response->setStatusCode(400)->setJSON(['code' => 'INVALID_JSON']);
            }

            if (! is_array($input) || substr($body, 0, 1) !== '{') {
                return $this->response->setStatusCode(400)->setJSON(['code' => 'INVALID_PAYLOAD']);
            }

            return $input;
        }

        return ($includeQuery ? $this->request->getPostGet() : $this->request->getPost()) ?? [];
    }
}
