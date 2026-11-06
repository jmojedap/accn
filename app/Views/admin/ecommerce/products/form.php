<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1"><?= $formMode === 'create' ? 'Crear producto' : 'Editar producto' ?></h1>
        <p class="text-muted mb-0"><?= $formMode === 'create' ? 'Agrega un producto virtual, servicio o acceso a tu catálogo.' : 'Actualiza la información y las condiciones de venta del producto.' ?></p>
    </div>
    <a class="btn btn-outline-secondary rounded-0" href="<?= esc($productsUrl . 'explore', 'attr') ?>">Volver a productos</a>
</div>
<div v-if="error" class="alert alert-danger rounded-0" role="alert">
    <p class="mb-0">{{ error }}</p>
    <ul v-if="Object.keys(errors).length" class="mb-0 mt-2">
        <li v-for="(message, field) in errors" :key="field">{{ message }}</li>
    </ul>
</div>
<div v-if="savedNotice" class="alert alert-success rounded-0" role="status">{{ savedNotice }}</div>
<form id="productForm" method="post" accept-charset="utf-8" @submit.prevent="handleSubmit">
    <fieldset :disabled="loading">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card rounded-0 h-100">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-4">Información del producto</h2>
                        <div class="mb-3">
                            <label for="productName" class="form-label">Nombre <span class="text-danger" aria-hidden="true">*</span></label>
                            <input id="productName" name="name" type="text" class="form-control rounded-0" :class="{'is-invalid': errors.name}" :aria-invalid="Boolean(errors.name)" v-model.trim="fields.name" maxlength="255" required autocomplete="off">
                            <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="productKind" class="form-label">Clase de producto</label>
                                <select id="productKind" name="product_kind" class="form-select rounded-0" :class="{'is-invalid': errors.product_kind}" v-model="fields.product_kind" required>
                                    <option value="digital">Virtual</option><option value="service">Servicio</option><option value="access">Acceso</option>
                                    <option v-if="fields.product_kind === 'physical'" value="physical">Físico</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="productStatus" class="form-label">Estado</label>
                                <select id="productStatus" name="status" class="form-select rounded-0" :class="{'is-invalid': errors.status}" v-model="fields.status" required>
                                    <option value="0">Borrador</option><option value="1">Activo</option><option value="2">Archivado</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Descripción</label>
                            <textarea id="productDescription" name="description" class="form-control rounded-0" :class="{'is-invalid': errors.description}" v-model="fields.description" rows="7" placeholder="Describe el producto y qué incluye."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="productMetaDescription" class="form-label">Resumen para buscadores</label>
                            <textarea id="productMetaDescription" name="meta_description" class="form-control rounded-0" :class="{'is-invalid': errors.meta_description}" v-model="fields.meta_description" maxlength="512" rows="2"></textarea>
                        </div>
                        <div>
                            <label for="productImage" class="form-label">URL de la imagen</label>
                            <input id="productImage" name="image_url" type="url" class="form-control rounded-0" :class="{'is-invalid': errors.image_url}" v-model.trim="fields.image_url" maxlength="500" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card rounded-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-4">Precio e impuestos</h2>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Precio base <span class="text-danger" aria-hidden="true">*</span></label>
                            <input id="productPrice" name="base_price" type="text" inputmode="decimal" class="form-control rounded-0" :class="{'is-invalid': errors.base_price}" :aria-invalid="Boolean(errors.base_price)" v-model.trim="fields.base_price" pattern="[0-9]+([.][0-9]{1,2})?" title="Importe no negativo, con punto y hasta dos decimales." required>
                            <div class="form-text">Antes de impuestos. Usa punto para los decimales.</div>
                            <div v-if="errors.base_price" class="invalid-feedback">{{ errors.base_price }}</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="productCurrency" class="form-label">Moneda</label>
                                <input id="productCurrency" name="currency" type="text" class="form-control rounded-0" :class="{'is-invalid': errors.currency}" v-model.trim="fields.currency" @input="fields.currency = fields.currency.toUpperCase()" maxlength="3" pattern="[A-Z]{3}" required>
                            </div>
                            <div class="col-6">
                                <label for="productTax" class="form-label">Impuesto (%)</label>
                                <input id="productTax" name="tax_percent" type="number" class="form-control rounded-0" :class="{'is-invalid': errors.tax_percent}" v-model="fields.tax_percent" min="0" max="100" step="0.01" required>
                            </div>
                        </div>
                        <div>
                            <label for="productCost" class="form-label">Costo de referencia</label>
                            <input id="productCost" name="cost" type="text" inputmode="decimal" class="form-control rounded-0" :class="{'is-invalid': errors.cost}" v-model.trim="fields.cost" pattern="[0-9]+([.][0-9]{1,2})?" title="Importe no negativo, con punto y hasta dos decimales." required>
                            <div class="form-text">Puedes dejarlo en cero si no aplica.</div>
                        </div>
                    </div>
                </div>
                <div class="card rounded-0">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-4">Identificación</h2>
                        <div class="mb-3">
                            <label for="productCode" class="form-label">Código del producto</label>
                            <input id="productCode" name="idcode" type="text" class="form-control rounded-0" :class="{'is-invalid': errors.idcode}" :aria-invalid="Boolean(errors.idcode)" v-model.trim="fields.idcode" maxlength="80" :required="!creating">
                            <div v-if="creating" class="form-text">Se genera al guardar si lo dejas vacío.</div>
                            <div v-if="errors.idcode" class="invalid-feedback">{{ errors.idcode }}</div>
                        </div>
                        <div>
                            <label for="productSlug" class="form-label">Nombre en la URL</label>
                            <input id="productSlug" name="slug" type="text" class="form-control rounded-0" :class="{'is-invalid': errors.slug}" :aria-invalid="Boolean(errors.slug)" v-model.trim="fields.slug" maxlength="180" :required="!creating">
                            <div v-if="creating" class="form-text">Se genera a partir del nombre si lo dejas vacío.</div>
                            <div v-if="errors.slug" class="invalid-feedback">{{ errors.slug }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 my-4">
            <button class="btn btn-primary rounded-0 px-4" type="submit">{{ loading ? 'Guardando...' : (creating ? 'Crear producto' : 'Guardar cambios') }}</button>
            <a class="btn btn-outline-secondary rounded-0" href="<?= esc($productsUrl . 'explore', 'attr') ?>">Cancelar</a>
        </div>
    </fieldset>
</form>
<?php if ($formMode === 'create') : ?>
    <div class="modal fade" id="productCreatedModal" tabindex="-1" aria-labelledby="productCreatedTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-0">
                <div class="modal-body p-4">
                    <h2 class="h5" id="productCreatedTitle">Producto creado</h2>
                    <p class="text-muted">El producto se guardó correctamente.</p>
                    <div class="d-grid gap-2">
                        <a class="btn btn-primary rounded-0" :href="createdInfoUrl">Ver producto</a>
                        <button type="button" class="btn btn-outline-secondary rounded-0" @click="createAnother">Crear otro</button>
                        <a class="btn btn-link" href="<?= esc($productsUrl . 'explore', 'attr') ?>">Volver a productos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
