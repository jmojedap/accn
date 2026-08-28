<?= view('assets/summernote_bs5') ?>

<div id="editPost" class="container-fluid px-0">
    <form accept-charset="utf-8" method="POST" id="editForm" @submit.prevent="handleSubmit">
        <input name="id" type="hidden" value="<?= esc($row->id) ?>">

        <fieldset v-bind:disabled="loading">
            <div class="card border-0 rounded-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div class="row g-4">
                        <div class="col-12 col-lg-8">
                            <label for="content" class="form-label">Contenido</label>
                            <textarea
                                name="content"
                                id="content"
                                class="form-control summernote"
                                rows="12"
                                title="Contenido"
                            ><?= esc($row->content ?? '') ?></textarea>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input
                                    name="title"
                                    id="title"
                                    type="text"
                                    class="form-control"
                                    required
                                    minlength="3"
                                    maxlength="255"
                                    autocomplete="off"
                                    v-model="fields.title"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label">Slug</label>
                                <input
                                    name="slug"
                                    id="slug"
                                    type="text"
                                    class="form-control"
                                    required
                                    minlength="3"
                                    maxlength="255"
                                    pattern="[a-z0-9\-]+"
                                    autocomplete="off"
                                    v-model="fields.slug"
                                >
                                <div class="form-text">Usa minúsculas, números y guiones.</div>
                            </div>

                            <div class="mb-3">
                                <label for="text_1" class="form-label">key_agente</label>
                                <input
                                    name="text_1"
                                    id="text_1"
                                    type="text"
                                    class="form-control"
                                    required
                                    minlength="3"
                                    maxlength="255"
                                    autocomplete="off"
                                    v-model="fields.text_1"
                                >
                                <div class="form-text"></div>
                            </div>

                            <div class="mb-3">
                                <label for="visibility" class="form-label">Visibilidad</label>
                                <select name="visibility" v-model="fields.visibility" class="form-select form-control" required>
                                    <option v-for="optionVisibility in arrVisibility" v-bind:value="optionVisibility.name">{{ optionVisibility.name }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="keywords" class="form-label">Palabras clave</label>
                                <textarea
                                    name="keywords"
                                    id="keywords"
                                    class="form-control"
                                    rows="3"
                                    v-model="fields.keywords"
                                ></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="excerpt" class="form-label">Resumen</label>
                                <textarea
                                    name="excerpt"
                                    id="excerpt"
                                    class="form-control"
                                    rows="4"
                                    v-model="fields.excerpt"
                                ></textarea>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                                {{ loading ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<?= view('m/sits/edit/vue'); ?>