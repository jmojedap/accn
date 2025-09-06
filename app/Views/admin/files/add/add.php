<div id="addFileApp">
    <div class="card center_box_750">
        <div class="card-body">
            <?= view('common/bs5/upload_file_form') ?>
        </div>
    </div>
</div>

<script>
var addFileApp = createApp({
    data(){
        return{
            file: '',
            loading: false,
            fileRow: {url: ''},
            errors: {
                file_field: ''
            }
        }
    },
    methods: {
        handleSubmit: function(){
            var formData = new FormData();
            formData.append('file_field', this.file);
            formData.append('table_id', 26);

            this.loading = true
            axios.post(URL_API + 'files/upload/', formData, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                console.log(response.data);
                //Ir a la vista de la imagen
                if ( response.data.savedId > 0 ) {
                    toastr['success']('Archivo cargado');
                    window.location = URL_MOD + 'files/info/' + response.data.row.id;
                } else {
                    toastr['error'](response.data.errors.file_field)
                    this.errors = response.data.errors
                }
                
                //Limpiar formulario
                $('#field-file').val(''); 

                this.loading = false
            })
            .catch(function (error) { console.log(error) })
        },
        handleFileUpload(){
            this.file = this.$refs.file_field.files[0];
        },
    }
}).mount('#addFileApp')
</script>