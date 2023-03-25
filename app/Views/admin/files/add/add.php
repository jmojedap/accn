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
        }
    },
    methods: {
        handleSubmit: function(){
            var formData = new FormData();
            formData.append('file_field', this.file);

            this.loading = true
            axios.post(URL_API + 'files/upload/', formData, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                console.log(response.data);
                //Ir a la vista de la imagen
                if ( response.data.status == 1 ) {
                    window.location = URL_MOD + 'files/info/' + response.data.row.id;
                }
                //Mostrar respuesta html, si existe
                if ( response.data.html ) { $('#uploadResponse').html(response.data.html); }
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