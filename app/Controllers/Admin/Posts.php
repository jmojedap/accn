<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PostModel;

class Posts extends BaseController
{
    protected PostModel $postModel;
    protected string $viewsFolder;
    protected string $backLink;
    protected array $entityInfo;

    public function __construct()
    {
        $this->postModel = new PostModel();
        $this->viewsFolder = 'admin/posts/';
        $this->backLink = URL_ADMIN . 'posts/explore';
        $this->entityInfo = [
            'controller' => 'posts',
            'singular' => 'Post',
            'plural' => 'Posts',
            'isMale' => 1,
        ];
    }

    public function index(): mixed
    {
        return redirect()->to('admin/posts/explore');
    }

// Exploración de posts
//-----------------------------------------------------------------------------

    /**
     * HTML
     * Listado de exploración y búsqueda de posts
     * 2023-03-05
     */
    public function explore(): mixed
    {
        //Filtros y detalle de la búsqueda
        $input = $this->request->getPostGet();
        $data = [];
        $data['search'] = $this->postModel->search($input);
        
        $data['entityInfo'] = $this->entityInfo;
        $data['headTitle'] = $this->entityInfo['plural'];
        $data['viewsFolder'] = $this->viewsFolder . 'explore/';
        $data['viewA'] = $this->viewsFolder . 'explore/explore';
        $data['nav2'] = $this->viewsFolder . 'menus/general';

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Detalle del registro en la tabla posts
     * @param int $idCode :: posts.idcode
     * 2024-05-24
     */
    public function details(int $idCode): mixed
    {
        $post = $this->postModel->getRow($idCode, 'admin');
        $data = $this->postModel->basic($post);

        $fieldsMeta = $this->postModel->getFieldData('posts');
        $data['fieldsMeta'] = $fieldsMeta;

        $data['row'] = $post;
        $data['viewA'] = 'common/bs5/row_edit';
        $data['formAction'] = URL_API . 'posts/update/' . $post->idcode;
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
    /**
     * HTML VIEW
     * Vista perfil del post
     * 2023-01-28
     */
    public function info(int $rowId): mixed
    {
        $post = $this->postModel->getRow($rowId, 'edition');
        $data = $this->postModel->basic($post);

        $data['viewA'] = $this->viewsFolder . 'info/info';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML FORM
     * Vista con formulario para crear un nuevo post
     * 2026-01-19
     */
    public function add(): mixed
    {
        $data = [];
        $data['headTitle'] = 'Crear post';
        $data['viewA'] = $this->viewsFolder . 'add/add';
        $data['nav2'] = $this->viewsFolder . 'menus/general';
        $data['entityInfo'] = $this->entityInfo;

        $data['arrTypes'] = $this->itemModel->arrOptions('category_id = 33');

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

    /**
     * HTML VIEW
     * Formulario para la edición de posts
     * 2026-01-19
     */
    public function edit(int $rowId): mixed
    {
        $post = $this->postModel->getRow($rowId, 'edition');
        $data = $this->postModel->basic($post);

        $data['arrTypes'] = $this->itemModel->arrOptions('category_id = 33');

        $data['viewA'] = $this->viewsFolder . 'edit/edit';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }

// POST IMAGES
//-----------------------------------------------------------------------------

    /**
     * HTML VIEW
     * Vista, gestión de imágenes de un post
     * 2026-01-28
     */
    public function images(int $rowId): mixed
    {
        $post = $this->postModel->getRow($rowId, 'edition');
        $data = $this->postModel->basic($post);

        $data['images'] = $this->postModel->images($rowId);

        $data['viewA'] = $this->viewsFolder . 'images/images';
        $data['nav2'] = $this->viewsFolder . 'menus/menu';
        $data['backLink'] = $this->backLink;

        return $this->pml->view(TPL_ADMIN . 'main', $data);
    }
}