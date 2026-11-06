<div id="productSearchFilters" v-show="displayFilters" class="border p-3 mt-3 bg-white">
    <div class="row g-3">
        <div class="col-sm-6 col-lg-2">
            <label for="productFilterStatus" class="form-label">Estado</label>
            <select id="productFilterStatus" class="form-select rounded-0" v-model="filters.status__eq" @change="search(true)">
                <option value="">Todos</option><option value="0">Borrador</option><option value="1">Activo</option><option value="2">Archivado</option>
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label for="productFilterKind" class="form-label">Clase de producto</label>
            <select id="productFilterKind" class="form-select rounded-0" v-model="filters.product_kind__eq" @change="search(true)">
                <option value="">Todas</option><option value="digital">Virtual</option><option value="service">Servicio</option><option value="access">Acceso</option>
                <option v-if="filters.product_kind__eq === 'physical'" value="physical">Físico</option>
            </select>
        </div>
        <div class="col-sm-6 col-lg-2">
            <label for="productFilterCurrency" class="form-label">Moneda</label>
            <input id="productFilterCurrency" type="text" maxlength="3" class="form-control rounded-0" v-model="filters.currency__eq" @change="filters.currency__eq = filters.currency__eq.toUpperCase(); search(true)" placeholder="COP" pattern="[A-Z]{3}">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label for="productFilterMin" class="form-label">Precio mínimo</label>
            <input id="productFilterMin" type="text" inputmode="decimal" class="form-control rounded-0" v-model="filters.base_price__gte" pattern="[0-9]+([.][0-9]{1,2})?" placeholder="0.00">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label for="productFilterMax" class="form-label">Precio máximo</label>
            <input id="productFilterMax" type="text" inputmode="decimal" class="form-control rounded-0" v-model="filters.base_price__lte" pattern="[0-9]+([.][0-9]{1,2})?" placeholder="Sin límite">
        </div>
        <div class="col-sm-6 col-lg-2">
            <label for="productsOrder" class="form-label">Ordenar por</label>
            <select id="productsOrder" class="form-select rounded-0" v-model="sort" @change="applySort">
                <option value="id:DESC">Más recientes</option><option value="id:ASC">Más antiguos</option>
                <option value="name:ASC">Nombre A–Z</option><option value="name:DESC">Nombre Z–A</option>
                <option value="base_price:ASC">Menor precio</option><option value="base_price:DESC">Mayor precio</option>
                <option v-if="!sortOptions.includes(sort)" :value="sort">Orden actual</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-sm btn-primary rounded-0 mt-3" :disabled="loading || deleting">Aplicar filtros</button>
</div>
