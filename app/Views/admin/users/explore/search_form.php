<form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="search">
    <input type="hidden" name="numPage" v-model="numPage">
    <input type="hidden" name="perPage" v-model="perPage">
    <div class="form-group row">
        <div class="col-md-9">
            <div class="input-group mb-2">
                <input
                    type="text" name="q" class="form-control" placeholder="Buscar" autofocus
                    v-model="filters.q"
                    >
                    <button type="button" class="btn btn-light btn-block" data-bs-toggle="modal" data-bs-target="#filtersModal" title="Búsqueda avanzada">
                        <i class="fa fa-sliders"></i>
                    </button>
            </div>
        </div>
    </div>
    <div id="adv_filters">
        <div class="mb-2 row">
            <div class="col-md-9">
                <select name="role__eq" v-model="filters.role__eq" class="form-select" v-on:change="search">
                    <option value="">[ Todos ]</option>
                    <option v-for="optionRole in arrRoles" v-bind:value="optionRole.code">{{ optionRole.name }}</option>
                </select>
            </div>
            <label for="type" class="col-md-3 control-label col-form-label">Tipo</label>
        </div>
    </div>
</form>

<!-- Modal -->
<div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filtersModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>