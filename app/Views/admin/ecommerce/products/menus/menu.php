<div class="d-flex flex-wrap align-items-end gap-2 border-bottom mb-4">
    <a href="<?= esc($backLink, 'attr') ?>" class="btn btn-link text-secondary text-decoration-none rounded-0">&larr; Productos</a>
    <nav class="nav nav-tabs border-0" aria-label="Producto">
        <a class="nav-link rounded-0 <?= $productSection === 'info' ? 'active' : '' ?>"
            href="<?= esc($productsUrl . 'info/' . (int) $row->id, 'attr') ?>"
            <?= $productSection === 'info' ? 'aria-current="page"' : '' ?>>Información</a>
        <a class="nav-link rounded-0 <?= $productSection === 'edit' ? 'active' : '' ?>"
            href="<?= esc($productsUrl . 'edit/' . (int) $row->id, 'attr') ?>"
            <?= $productSection === 'edit' ? 'aria-current="page"' : '' ?>>Editar</a>
    </nav>
</div>
