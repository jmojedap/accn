<?= view('m/sits/mis_sits/style'); ?>

<div id="misSitsApp">
    <div v-if="section == 'list'">
        <div class="row mb-4">
            <div class="col-12">
                <a :href="url_mod + 'sits/add'" class="btn btn-primary w120p">
                    <i class="fa fa-plus"></i> Crear
                </a>
            </div>
        </div>

        <div class="sit-list">
            <div v-for="sit in sits" class="sit-item">
                <!-- Thumbnail -->
                <div class="sit-thumb-container">
                    <a :href="url_mod + 'sits/info/' + sit.slug">
                        <img
                            v-bind:src="sit.url_thumbnail"
                            class="sit-thumb"
                            v-bind:alt="sit.slug"
                            onerror="this.src='<?= URL_IMG ?>app/sits-default.png'"
                        >
                    </a>
                </div>

                <!-- Info -->
                <div class="sit-info">
                    <a class="sit-title d-block text-decoration-none" :href="url_mod + 'sits/info/' + sit.slug">
                        {{ sit.title }}
                    </a>
                    <span class="sit-url">/{{ sit.slug }}</span>
                </div>

                <!-- Actions -->
                <div class="sit-actions">
                    <a :href="url_mod + 'sits/edit/' + sit.id" class="btn-action-sit" title="Editar detalles">
                        <i class="fa fa-edit"></i>
                    </a>
                    <button class="btn-action-sit btn-delete" data-bs-toggle="modal" data-bs-target="#modalSingleDelete" v-on:click="setCurrent(sit.id)" title="Eliminar sitio">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="sits.length == 0" class="text-center py-5">
                <div class="display-6 text-muted mb-3">No tienes sitios</div>
                <a :href="url_mod + 'sits/add'" class="btn btn-outline-primary px-4">¡Crea tu primer sitio!</a>
            </div>
        </div>

        <?= view('common/bs5/modal_single_delete') ?>
    </div>
</div>

<?= view('m/sits/mis_sits/vue'); ?>
