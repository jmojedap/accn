<div id="exploreApp">
    <div class="row">
        <div class="col-md-5">
            <?= view($viewsFolder . 'search_form') ?>
        </div>
        <div class="col-md-3">
            <button class="btn btn-light"
                title="Eliminar elementos seleccionados"
                data-bs-toggle="modal" data-bs-target="#modalDeleteSelected"
                v-show="selected.length > 0"
                >
                <i class="fa-solid fa-trash text-danger"></i>
            </button>
            <button class="btn" v-show="loading">
                <i class="fa-solid fa-spin fa-spinner"></i>
            </button>
        </div>
    </div>
    <?= view($viewsFolder . 'list') ?>
    <?= view('common/bs5/modal_delete_selected') ?>
</div>

<?= view($viewsFolder . 'vue') ?>
