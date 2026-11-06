<nav class="border-bottom mb-4" aria-label="Productos">
    <div class="nav nav-tabs border-0">
        <a class="nav-link rounded-0 <?= $productSection === 'explore' ? 'active' : '' ?>" href="<?= esc($productsUrl . 'explore', 'attr') ?>">Explorar</a>
        <a class="nav-link rounded-0 <?= $productSection === 'add' ? 'active' : '' ?>" href="<?= esc($productsUrl . 'add', 'attr') ?>">Crear producto</a>
    </div>
</nav>
