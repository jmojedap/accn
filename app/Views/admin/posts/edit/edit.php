<?= view('assets/summernote_bs5') ?>

<div id="editPost">
    <form accept-charset="utf-8" method="POST" id="editForm" @submit.prevent="handleSubmit">
        <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    
                        <input name="id" type="hidden" value="<?= $row->id ?>">
                        <fieldset v-bind:disabled="loading">
                            <div class="mb-2 row">
                                <div class="col-md-8 offset-md-4">
                                    <button class="btn btn-success w120p me-2" type="submit">Guardar</button>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label for="type_id" class="col-md-4 col-form-label text-end">Tipo</label>
                                <div class="col-md-8">
                                    <select name="type_id" v-model="fields.type_id" class="form-select">
                                        <option v-for="optionType in arrTypes" v-bind:value="optionType.code">
                                            {{ optionType.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label for="title" class="col-md-4 col-form-label text-end">Título</label>
                                <div class="col-md-8">
                                    <input name="title" type="text" class="form-control" required title="Título"
                                        placeholder="Título" v-model="fields.title">
                                </div>
                                    </div>

                            <div class="mb-2 row">
                                <label for="keywords" class="col-md-4 col-form-label text-end">Palabras clave</label>
                                <div class="col-md-8">
                                    <textarea name="keywords" class="form-control" required title="Palabras clave" rows="3"
                                        placeholder="Palabras clave" v-model="fields.keywords"></textarea>
                                </div>
                            </div>

                            <div class="mb-2 row">
                                <label for="excerpt" class="col-md-4 col-form-label text-end">Resumen</label>
                                <div class="col-md-8">
                                    <textarea name="excerpt" class="form-control" required title="Resumen" rows="4"
                                        placeholder="Resumen" v-model="fields.excerpt"></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="published_at" class="col-md-4 col-form-label text-end text-right">Fecha de publicación</label>
                                <div class="col-md-8">
                                    <input
                                        name="published_at" type="date" class="form-control"
                                        v-model="fields.published_at"
                                    >
                                </div>
                            </div>  
                        </fieldset>
                    
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="bg-white mw750p">
                <textarea name="content" class="form-control summernote" title="Contenido"><?= $row->content ?></textarea>
            </div>
        </div>
    </div>
    </form>
</div>

<?= view('admin/posts/edit/vue'); ?>