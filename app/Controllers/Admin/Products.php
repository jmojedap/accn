<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Products extends BaseController
{
    protected ProductModel $productModel;
    protected string $viewsFolder = 'admin/ecommerce/products/';

    public function __construct(?ProductModel $productModel = null)
    {
        $this->productModel = $productModel ?? new ProductModel();
    }

    public function index(): mixed
    {
        return redirect()->to(URL_ADMIN . 'ecommerce/products/explore');
    }

    public function explore(): mixed
    {
        $input = $this->request->getGet() ?? [];
        $search = $this->productModel->search($input);

        if ($search['settings']['numPage'] > $search['settings']['maxPage']) {
            $input['numPage'] = $search['settings']['maxPage'];
            $search = $this->productModel->search($input);
        }

        return $this->renderPage(array_merge($this->pageData('explore', 'Productos'), [
            'search' => $search,
            'viewsFolder' => $this->viewsFolder . 'explore/',
        ]));
    }

    public function add(): mixed
    {
        return $this->renderPage(array_merge($this->pageData('add', 'Crear producto'), [
            'formMode' => 'create',
            'row' => null,
        ]));
    }

    public function info(int $rowId): mixed
    {
        $product = $this->productModel->find($rowId);

        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Producto no encontrado.');
        }

        return $this->renderPage(array_merge($this->pageData('info', 'Información del producto'), [
            'row' => $product,
        ]));
    }

    public function edit(int $rowId): mixed
    {
        $product = $this->productModel->find($rowId);

        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Producto no encontrado.');
        }

        return $this->renderPage(array_merge($this->pageData('edit', 'Editar producto'), [
            'formMode' => 'edit',
            'row' => $product,
        ]));
    }

    protected function pageData(string $section, string $title): array
    {
        return [
            'headTitle' => $title,
            'viewA' => $this->viewsFolder . $section . '/' . $section,
            'nav2' => $this->viewsFolder . 'menus/' . (in_array($section, ['info', 'edit'], true) ? 'menu' : 'general'),
            'nav_3' => null,
            'productSection' => $section,
            'productsUrl' => URL_ADMIN . 'ecommerce/products/',
            'backLink' => URL_ADMIN . 'ecommerce/products/explore',
            'productsApiUrl' => URL_API . 'products/',
            'entityInfo' => ['controller' => 'products', 'singular' => 'Producto', 'plural' => 'Productos', 'isMale' => 1],
        ];
    }

    protected function renderPage(array $data): mixed
    {
        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}
