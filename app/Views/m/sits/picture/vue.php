<script>
var userPictureApp = createApp({
    data(){
        return{
            loading: false,
            deleting: false,
            entityInfo: {
                singular: 'Foto',
                plural: 'Fotos',
                isMale: 0,
            },
            row: {
                id: '<?= $row->id ?>',
                image_id: '<?= $row->image_id ?>',
                url_image: '<?= $row->url_image ?>',
                url_thumbnail: '<?= $row->url_thumbnail ?>',
            },
            file: null,
            errors: {
                file_field: ''
            }
        }
    },
    methods: {
        handleSubmit: function(){
            this.clearErrors()
            let formData = new FormData();
            formData.append('file_field', this.file)
            formData.append('table_id', '2000')
            formData.append('related_1', this.row.id)

            axios.post(URL_API + 'sits/set_picture', formData, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                //Cargar imágenes
                if ( response.data.savedId > 0 ) {
                    //Limpiar formulario
                    document.getElementById('field-file').value = null
                    this.file = null
                    this.row.image_id = response.data.fileRow.id
                    this.row.url_image = response.data.fileRow.url
                    this.row.url_thumbnail = response.data.fileRow.url_thumbnail
                } else {
                    toastr['error']('No se cargó la imagen')
                    this.errors = response.data.errors
                }
            })
            .catch(function (error) { console.log(error) })
        },
        handleFileUpload(){
            this.file = this.$refs.file_field.files[0]
        },
        deleteElement: function(){
            this.clearErrors()
            this.deleting = true
            axios.delete(URL_API + 'files/delete/' + this.row.image_id)
            .then(response => {
                if ( response.data.deleting_result === true ) {
                    toastr['info']('Imagen eliminada')
                    this.row.image_id = 0
                    this.row.url_image = ''
                    this.row.url_thumbnail = ''
                } else {
                    toastr['warning'](response.data.deleting_result)
                }
                //Ocultar modal
                $('#modalSingleDelete').modal('hide')
                this.deleting = false
            })
            .catch(function (error) { console.log(error) })
        },
        clearErrors: function(){
            this.errors = {
                file_field: ''
            }
        },
    },
    mounted(){
        //this.getList();
    }
}).mount('#userPictureApp')
</script>