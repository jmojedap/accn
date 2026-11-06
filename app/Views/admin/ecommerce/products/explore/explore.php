<div id="productsExplore" v-cloak>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Productos</h1>
            <p class="text-muted mb-0">Gestiona tus productos virtuales, servicios y accesos.</p>
        </div>
        <a href="<?= esc($productsUrl . 'add', 'attr') ?>" class="btn btn-primary rounded-0">Crear producto</a>
    </div>
    <div v-if="error" class="alert alert-danger rounded-0" role="alert">{{ error }}</div>
    <div v-if="notice" class="alert rounded-0" :class="noticeWarning ? 'alert-warning' : 'alert-success'" role="status">{{ notice }}</div>
    <form @submit.prevent="search(true)" class="mb-3">
        <div class="row g-2">
            <div class="col-md-7">
                <label for="productsQuery" class="visually-hidden">Buscar productos</label>
                <div class="input-group">
                    <input id="productsQuery" type="search" class="form-control rounded-0" v-model="filters.q" placeholder="Buscar por nombre, código o descripción">
                    <button class="btn btn-outline-secondary rounded-0" type="submit" :disabled="loading || deleting">Buscar</button>
                </div>
            </div>
            <div class="col-md-5 d-flex gap-2 justify-content-md-end">
                <button class="btn rounded-0" :class="displayFilters ? 'btn-secondary' : 'btn-outline-secondary'" type="button" @click="displayFilters = !displayFilters" :aria-expanded="displayFilters" aria-controls="productSearchFilters">Filtros</button>
                <button class="btn btn-outline-secondary rounded-0" type="button" @click="resetFilters" :disabled="loading || deleting">Limpiar</button>
            </div>
        </div>
        <?= view($viewsFolder . 'search_form') ?>
    </form>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger rounded-0" v-if="selected.length" @click="confirmDelete" :disabled="loading || deleting">Eliminar seleccionados ({{ selected.length }})</button>
            <span v-if="loading" class="text-muted small" role="status">Cargando...</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <label for="productsPerPage" class="visually-hidden">Productos por página</label>
            <select id="productsPerPage" class="form-select form-select-sm rounded-0 w-auto" v-model.number="settings.perPage" @change="search(true)" :disabled="loading || deleting">
                <option :value="12">12 por página</option><option :value="24">24 por página</option><option :value="48">48 por página</option>
                <option v-if="![12,24,48].includes(Number(settings.perPage))" :value="Number(settings.perPage)">{{ settings.perPage }} por página</option>
            </select>
            <span class="small text-muted" aria-live="polite">{{ paginationText }}</span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" @click="sumPage(-1)" :disabled="loading || deleting || settings.numPage <= 1" aria-label="Página anterior">Anterior</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" @click="sumPage(1)" :disabled="loading || deleting || settings.numPage >= settings.maxPage" aria-label="Página siguiente">Siguiente</button>
        </div>
    </div>
    <?= view($viewsFolder . 'list') ?>
    <div class="modal fade" id="productsDeleteModal" tabindex="-1" aria-labelledby="productsDeleteTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="productsDeleteTitle">Eliminar productos</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" :disabled="deleting"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Eliminar {{ selected.length }} producto(s)? Dejarán de aparecer en el catálogo.</p>
                    <p v-if="deleteError" class="text-danger mt-3 mb-0" role="alert">{{ deleteError }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal" :disabled="deleting">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-0" @click="deleteSelected" :disabled="deleting">{{ deleting ? 'Eliminando...' : 'Eliminar' }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view($viewsFolder . 'vue') ?>
