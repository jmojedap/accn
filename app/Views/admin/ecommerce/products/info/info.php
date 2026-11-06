<div id="productInfoApp">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div class="text-break">
            <h1 class="h4 mb-2">{{ product.name }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge rounded-0" :class="statusClass">{{ statusName }}</span>
                <span class="text-muted">{{ kindName }}</span>
            </div>
        </div>
        <a class="btn btn-primary rounded-0" href="<?= esc($productsUrl . 'edit/' . (int) $row->id, 'attr') ?>">Editar producto</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card rounded-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Descripción</h2>
                    <p class="text-break mb-0" style="white-space: pre-wrap">{{ product.description || 'Este producto aún no tiene descripción.' }}</p>
                </div>
            </div>
            <div class="card rounded-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Identificación</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">Código del producto</dt>
                        <dd class="col-sm-8 text-break">{{ product.idcode }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Nombre en la URL</dt>
                        <dd class="col-sm-8 text-break">{{ product.slug }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Resumen para buscadores</dt>
                        <dd class="col-sm-8 text-break mb-0">{{ product.meta_description || 'No especificado' }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card rounded-0">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Registro</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">ID</dt><dd class="col-sm-8">{{ product.id }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Fecha de creación</dt><dd class="col-sm-8">{{ dateText(product.created_at) }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Última actualización</dt><dd class="col-sm-8 mb-0">{{ dateText(product.updated_at) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card rounded-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Precio e impuestos</h2>
                    <p class="h3 mb-1">{{ money(product.base_price) }}</p>
                    <p class="text-muted small mb-4">{{ product.currency }} · Precio base antes de impuestos</p>
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted fw-normal">Impuesto</dt><dd class="col-6 text-end">{{ product.tax_percent }} %</dd>
                        <dt class="col-6 text-muted fw-normal">Costo de referencia</dt><dd class="col-6 text-end mb-0">{{ money(product.cost) }}</dd>
                    </dl>
                </div>
            </div>
            <div class="card rounded-0">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Imagen</h2>
                    <img v-if="imageUrl && !imageFailed" :src="imageUrl" :alt="product.name" class="img-fluid rounded-0 w-100" @error="imageFailed = true">
                    <p v-else class="text-muted mb-0">{{ imageFailed ? 'No fue posible cargar la imagen.' : 'Sin imagen.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= view('admin/ecommerce/products/info/vue') ?>
