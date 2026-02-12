<div id="editFileApp">
    <div class="center_box_750 mb-2" v-show="fields.is_image == 1">
        <div class="mb-2">
            <img v-bind:src="fields.url_thumbnail" alt="Miniatura imagen" class="sqr-120 rounded">
        </div>
    </div>
    <div class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="fileForm" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <input type="hidden" name="id" value="<?= $row->id ?>">
                    <div class="mb-3 row">
                        <label for="title" class="col-md-4 col-form-label text-end">Título</label>
                        <div class="col-md-8">
                            <input
                                name="title" type="text" class="form-control"
                                required
                                title="Título" placeholder="Título"
                                v-model="fields.title"
                            >
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="description" class="col-md-4 col-form-label text-end">Descripción</label>
                        <div class="col-md-8">
                            <textarea
                                name="description" type="text" class="form-control" rows="3"
                                title="Descripción" placeholder="Descripción"
                                v-model="fields.description"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="keywords" class="col-md-4 col-form-label text-end">Palabras clave</label>
                        <div class="col-md-8">
                            <textarea
                                name="keywords" class="form-control" rows="2"
                                title="Palabras clave" placeholder="Palabras clave"
                                v-model="fields.keywords"
                            ></textarea>
                        </div>
                    </div>

                    
                    <div class="mb-3 row">
                        <div class="col-md-8 offset-md-4">
                            <button class="btn btn-primary w120p" type="submit">Guardar</button>
                        </div>
                    </div>
                <fieldset>
            </form>
        </div>
    </div>
</div>

<script>
var editFileApp = createApp({
    data(){
        return{
            loading: false,
            fields: <?= json_encode($row) ?>,
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('fileForm'))
            axios.post(URL_API + 'files/update/' + this.fields.id, formValues)
            .then(response => {
                if ( response.data.savedId > 0 ) {
                    toastr['success']('Guardado')
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#editFileApp')
</script>