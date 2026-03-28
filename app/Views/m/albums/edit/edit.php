<div id="albumApp">
    <div v-show="section == 'form'" class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="albumForm" @submit.prevent="handleSubmit">
                <div class="mb-3">
                    <label for="title" class="form-label">Título</label>
                    <input type="text" class="form-control" id="title" name="title" v-model="fields.title" required>
                </div>
                <div class="mb-3">
                    <label for="excerpt" class="form-label">Descripción</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" v-model="fields.excerpt"></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status" v-model="fields.status">
                        <option v-for="(item, i) in arrStatus" :value="item">{{ item }}</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('m/albums/edit/vue') ?>