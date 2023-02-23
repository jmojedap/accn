<?php
    $filters_style = ( strlen($filtersStr) > 0 ) ? '' : 'display: none;' ;
    //$filters_style = '';
?>

<form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="search">
    <input type="hidden" name="numPage" v-model="numPage">
    <input type="hidden" name="perPage" value="2">
    <div class="form-group row">
        <div class="col-md-9">
            <div class="input-group mb-2">
                <input
                    type="text" name="q" class="form-control" placeholder="Buscar" autofocus
                    v-model="filters.q"
                    >
                    <button type="button" class="btn btn-light btn-block" v-on:click="toggle_filters" title="Búsqueda avanzada">
                        <i class="fa fa-chevron-up" v-show="displayFilters"></i>
                        <i class="fa fa-chevron-down" v-show="!displayFilters"></i>
                    </button>
            </div>
        </div>
    </div>
    <div id="adv_filters" style="<?= $filters_style ?>">
        <div class="mb-2 row">
            <div class="col-md-9">
                <select name="role__eq" v-model="filters.role__eq" class="form-select" v-on:change="search">
                    <option value="">[ Todos ]</option>
                    <option v-for="optionRole in arrRoles" v-bind:value="optionRole.str_cod">{{ optionRole.name }}</option>
                </select>
            </div>
            <label for="type" class="col-md-3 control-label col-form-label">Tipo</label>
        </div>
    </div>
</form>