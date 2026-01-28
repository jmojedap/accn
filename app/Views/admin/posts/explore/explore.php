<div id="exploreApp">
    <div class="row mb-2">
        <div class="col-md-4">
            <div class="input-group mb-2">
                <input type="text" name="q" class="form-control" placeholder="Buscar" v-model="filters.q" v-on:change="search">
                <button type="button" class="btn btn-light w40p" title="Borrar" v-on:click="resetFilter('q')" v-show="filters.q.length > 0">
                    <i class="fa fa-times"></i>
                </button>
                <button type="button" class="btn w40p" title="Búsqueda avanzada" v-on:click="toggleFilters"
                    v-bind:class="{'btn-light': !displayFilters, 'btn-info': displayFilters }">
                    <i class="fa fa-sliders"></i>
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <button class="btn btn-light"
                title="Eliminar elementos seleccionados"
                data-bs-toggle="modal" data-bs-target="#modalDeleteSelected"
                v-show="selected.length > 0"
                >
                <i class="fa-solid fa-trash text-danger"></i>
            </button>
            <span v-show="loading">
                <i class="fa-solid fa-spin fa-spinner"></i>
            </span>
        </div>
        <div class="col-md-4 text-end">
            <?= view('common/bs5/pagination') ?>
        </div>
    </div>
    <?= view($viewsFolder . 'search_form') ?>
    <?= view($viewsFolder . 'list') ?>
    <?= view('common/bs5/modal_delete_selected') ?>
</div>

<?= view($viewsFolder . 'vue') ?>
