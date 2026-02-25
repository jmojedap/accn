<div id="photoAlbumsApp">
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
                        <input
                            name="title" type="text" class="form-control"
                            required
                            title="Nombre" placeholder="Nombre"
                            v-model="fields.title"
                        >
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="excerpt" class="col-md-4 col-form-label text-end text-right">Descripción</label>
                    <div class="col-md-8">
                        <textarea
                            name="excerpt" class="form-control" rows="3"
                            title="Descripción" placeholder="Descripción"
                            v-model="fields.excerpt"
                        ></textarea>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-md-8 offset-md-4">
                        <button class="btn btn-primary w120p me-1" type="submit">Guardar</button>
                        <button class="btn btn-light w120p" type="button" @click="section = 'list'">Cancelar</button>
                    </div>
                </div>
            <fieldset>
        </form>
        </div>
    </div>

    <div v-show="section == 'list'" class="mb-2">
        <button class="btn btn-primary w120p me-1" type="button" @click="section = 'form'">
            <i class="fa fa-plus"></i> Nuevo
        </button>
    </div>

    <table class="table table-bordered table-hover" v-show="section == 'list'">
        <thead>
            <tr>
                <th>Título</th>
                <th>Slug</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="album in albums">
                <td>{{ album.title }}</td>
                <td>{{ album.slug }}</td>
                <td><img :src="album.url_image" :alt="album.title" width="100"></td>
                <td>
                    <button type="button" @click="setCurrent(album.id)" class="btn btn-light btn-sm me-1">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button type="button" @click="setCurrent(album.id, 'list')" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalSingleDelete">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
    <?= view('common/bs5/modal_single_delete') ?>
</div>

<?= view('m/sits/edit_photo_albums/vue') ?>