<?= view('m/sits/edit_albums/style') ?>

<div id="albumsApp">
    <div v-show="section == 'form'" class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="albumForm" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <input type="hidden" name="id" v-model="fields.id">
                    <input type="hidden" name="parent_id" value="<?= $row->id ?>">
                    <input type="hidden" name="type_id" value="5">

                    <div class="mb-3 row">
                        <label for="title" class="col-md-4 col-form-label text-end">Nombre</label>
                        <div class="col-md-8">
                            <input name="title" type="text" class="form-control" required title="Nombre"
                                placeholder="Nombre" v-model="fields.title">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="excerpt" class="col-md-4 col-form-label text-end text-right">Descripción</label>
                        <div class="col-md-8">
                            <textarea name="excerpt" class="form-control" rows="3" title="Descripción"
                                placeholder="Descripción" v-model="fields.excerpt"></textarea>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-md-8 offset-md-4">
                            <button class="btn btn-primary w120p me-1" type="submit">Guardar</button>
                            <button class="btn btn-light w120p" type="button"
                                @click="section = 'list'">Cancelar</button>
                        </div>
                    </div>
                    <fieldset>
            </form>
        </div>
    </div>

    <div v-show="section == 'list'" class="mb-3">
        <button class="btn btn-primary w120p me-1" type="button" @click="resetFields">
            <i class="fa fa-plus"></i> Nuevo
        </button>
    </div>

    <!-- Album list as vertical rows -->
    <div v-show="section == 'list'" class="album-list">
        <div v-for="album in albums" class="album-item">
            <!-- Thumbnail Column -->
            <div class="album-thumb-container">
                <a v-bind:href="'<?= URL_APP ?>albums/images/' + album.id">
                    <img v-bind:src="album.url_image" class="album-thumb" v-bind:alt="album.title"
                        onerror="this.src='<?= URL_IMG ?>app/nd.png'">
                </a>
            </div>

            <!-- Content Column -->
            <div class="album-info">
                <a class="album-title d-block text-decoration-none"
                    v-bind:href="'<?= URL_APP ?>albums/images/' + album.id">
                    {{ album.title }}
                </a>
                <div class="album-excerpt text-muted">
                    {{ album.excerpt }}
                </div>
            </div>

            <!-- Actions Column -->
            <div class="album-actions">
                <a :href="'<?= URL_APP ?>albums/edit_images/' + album.id" class="btn-action" title="Editar detalles">
                    <i class="fa fa-edit"></i>
                </a>
                <button type="button" @click="setCurrent(album.id, 'list')" class="btn-action btn-delete"
                    data-bs-toggle="modal" data-bs-target="#modalSingleDelete" title="Eliminar álbum">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="albums.length == 0" class="text-center py-5">
            <div class="display-6 text-muted mb-2">No tienes álbumes</div>
            <button class="btn btn-outline-primary" @click="resetFields">¡Crea uno nuevo!</button>
        </div>
    </div>
    <?= view('common/bs5/modal_single_delete') ?>
</div>

<?= view('m/sits/edit_albums/vue') ?>