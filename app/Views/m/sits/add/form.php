<div class="card center_box_750">
    <div class="card-body">
        <form accept-charset="utf-8" method="POST" id="sitForm" @submit.prevent="handleSubmit">
            <fieldset v-bind:disabled="loading">
                
                <div class="mb-1 row">
                    <label for="title" class="col-md-4 col-form-label text-end">Título</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="title" name="title" v-model="fields.title">
                    </div>
                </div>

                <div class="mb-1 row">
                    <label for="content" class="col-md-4 col-form-label text-end">Contenido</label>
                    <div class="col-md-8">
                        <textarea class="form-control" id="content" name="content" v-model="fields.content"></textarea>
                    </div>
                </div>
                
                <div class="mb-1 row">
                    <div class="col-md-8 offset-md-4">
                        <button class="btn btn-success w120p" type="submit">Guardar</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>