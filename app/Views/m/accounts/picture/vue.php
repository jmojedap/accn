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
            user: {
                id: '<?= $user->id ?>',
                idcode: '<?= $user->idcode ?>',
                image_id: '<?= $user->image_id ?>',
                url_image: '<?= $user->url_image ?>',
                url_thumbnail: '<?= $user->url_thumbnail ?>',
            },
            file: null,
            errors: {
                file_field: ''
            }
        }
    },
    methods: {
        handleSubmit: function(){
            let formData = new FormData();
            formData.append('file_field', this.file)
            formData.append('table_id', '1000')
            formData.append('related_1', this.user.id)

            axios.post(URL_API + 'accounts/set_picture/', formData, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                //Cargar imágenes
                if ( response.data.savedId > 0 ) {
                    //Limpiar formulario
                    document.getElementById('field-file').value = null
                    this.file = null
                    this.user.image_id = response.data.fileRow.id
                    this.user.url_image = response.data.fileRow.url
                    this.user.url_thumbnail = response.data.fileRow.url_thumbnail
                }
                //Mostrar respuesta html, si existe
                if ( response.data.html ) { $('#upload_response').html(response.data.html); }
            })
            .catch(function (error) { console.log(error) })
        },
        handleFileUpload(){
            this.file = this.$refs.file_field.files[0]
        },
        deleteElement: function(){
            this.deleting = true
            const payload = {
                image_id: this.user.image_id,
                user_idcode: this.user.idcode
            }
            axios.delete(URL_API + 'users/delete_picture/', { data: payload })
            .then(response => {
                if ( response.data.deleting_result == true ) {
                    toastr['info']('Imagen eliminada')
                    this.user.image_id = 0
                    this.user.url_image = ''
                    this.user.url_thumbnail = ''
                } else {
                    toastr['warning'](response.data.deleting_result)
                }
                //Ocultar modal
                $('#modalSingleDelete').modal('hide')
                this.deleting = false
            })
            .catch(function (error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList();
    }
}).mount('#userPictureApp')
</script>