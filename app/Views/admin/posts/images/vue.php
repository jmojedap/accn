<script>
var postImages = createApp({
    data(){
        return{
            loading: false,
            deleting: false,
            entityInfo: {
                singular: 'Imagen',
                plural: 'Imágenes',
                isMale: 0,
            },
            file: null,
            postId: '<?= $row->id ?>',
            images: <?= json_encode($images->getResult()); ?>,
            currentImage: {},
            errors: {
                file_field: ''
            }
        }
    },
    methods: {
        getList: function(){
            axios.get(URL_API + 'posts/images/' + this.postId)
            .then(response => {
                this.images = response.data.images;
            })
            .catch(function (error) { console.log(error) })
        },
        handleSubmit: function(){
            let formData = new FormData();
            formData.append('file_field', this.file)
            formData.append('table_id', '2000')
            formData.append('related_1', this.postId)

            axios.post(URL_API + 'files/upload/', formData, {headers: {'Content-Type': 'multipart/form-data'}})
            .then(response => {
                //Cargar imágenes
                if ( response.data.savedId > 0 ) {
                    this.getList()
                    //Limpiar formulario
                    document.getElementById('field-file').value = null
                    this.file = null
                }
                //Mostrar respuesta html, si existe
                if ( response.data.html ) { $('#upload_response').html(response.data.html); }
            })
            .catch(function (error) { console.log(error) })
        },
        handleFileUpload(){
            this.file = this.$refs.file_field.files[0]
        },
        setCurrent: function(key){
            this.currentImage = this.images[key]
        },
        deleteElement: function(){
            var fileId = this.currentImage.id
            this.deleting = true
            axios.delete(URL_API + 'files/delete/' + fileId)
            .then(response => {
                if ( response.data.deleted ) {
                    toastr['info']('Imagen eliminada correctamente')
                    this.getList()
                }
                //Ocultar modal
                $('#modalSingleDelete').modal('hide')
                this.deleting = false
            })
            .catch(function (error) { console.log(error) })
        },
        setMainImage: function(key){
            this.setCurrent(key)
            var fileId = this.currentImage.id
            axios.get(URL_API + 'posts/set_main_image/' + this.postId + '/' + fileId)
            .then(response => {
                if ( response.data.status == 1 ) {
                    this.getList()
                } else {
                    toastr['warning']('No se cambió la imagen principal')
                }
            })
            .catch(function (error) { console.log(error) })
        },
        updatePosition: function(fileId, newPosition){
            axios.put(URL_API + 'files/update_position/' + fileId + '/' + newPosition)
            .then(response => {
                if ( response.data.status == 1 ) {
                    this.getList()
                } else {
                    toastr['warning']('No se cambió el orden de las imágenes')
                }
            })
            .catch(function(error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList();
    }
}).mount('#postImages')
</script>