<div id="addPost">
    <div class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="addForm" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <div class="mb-2 row">
                        <label for="type_id" class="col-md-4 col-form-label text-end">Tipo</label>
                        <div class="col-md-8">
                            <select name="type_id" v-model="post.type_id" class="form-select">
                                <option v-for="optionType in arrTypes" v-bind:value="optionType.code">
                                    {{ optionType.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="title" class="col-md-4 col-form-label text-end">Título</label>
                        <div class="col-md-8">
                            <input name="title" type="text" class="form-control" required title="Título"
                                placeholder="Título" v-model="post.title">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="content" class="col-md-4 col-form-label text-end">Contenido</label>
                        <div class="col-md-8">
                            <textarea name="content" class="form-control" required title="Contenido" rows="5"
                                placeholder="Contenido" v-model="post.content"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-2 row">
                        <div class="col-md-8 offset-md-4">
                            <button class="btn btn-success w120p me-2" type="submit">Guardar</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="createdModal" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="text-center">
                        <i class="fa fa-check-circle text-success fa-2x"></i>
                    </p>
                    <div class="d-flex flex-column">
                        <button type="button" class="btn btn-primary btn-sm mb-2" v-on:click="goToCreated">
                            Abrir {{ entityInfo.singular.toLowerCase() }}
                        </button>
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Crear otro</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/posts/add/vue'); ?>