<div class="table-responsive border bg-white" :aria-busy="loading">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col" class="text-center" style="width: 42px">
                    <input type="checkbox" class="form-check-input rounded-0" :checked="allSelected" @change="selectAll($event.target.checked)" :disabled="loading || deleting || !results.length" aria-label="Seleccionar todos los productos de esta página">
                </th>
                <th scope="col">Producto</th><th scope="col">Clase</th><th scope="col">Estado</th>
                <th scope="col" class="text-end">Precio base</th><th scope="col" class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="element in results" :key="element.id" :class="{'table-active': selected.includes(element.idcode)}">
                <td class="text-center">
                    <input type="checkbox" class="form-check-input rounded-0" v-model="selected" :value="element.idcode" :disabled="loading || deleting" :aria-label="'Seleccionar ' + element.name">
                </td>
                <td>
                    <a :href="infoUrl(element.id)" class="fw-semibold text-decoration-none">{{ element.name }}</a>
                    <div class="text-muted small text-break">{{ element.idcode }}</div>
                </td>
                <td>{{ kindName(element.product_kind) }}</td>
                <td><span class="badge rounded-0" :class="statusClass(element.status)">{{ statusName(element.status) }}</span></td>
                <td class="text-end text-nowrap">{{ money(element.base_price, element.currency) }} <span class="text-muted small">{{ element.currency }}</span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-secondary rounded-0" :href="editUrl(element.id)">Editar</a></td>
            </tr>
            <tr v-if="!results.length">
                <td colspan="6" class="text-center py-5">
                    <div v-if="loading" class="text-muted">Cargando productos...</div>
                    <template v-else>
                        <p class="mb-1">No se encontraron productos.</p>
                        <p class="text-muted small mb-3">Prueba con otros filtros o crea un producto.</p>
                        <a class="btn btn-sm btn-primary rounded-0" href="<?= esc($productsUrl . 'add', 'attr') ?>">Crear producto</a>
                    </template>
                </td>
            </tr>
        </tbody>
    </table>
</div>
