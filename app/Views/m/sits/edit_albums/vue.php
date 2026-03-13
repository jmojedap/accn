<script>
var albumsApp = createApp({
    data(){
        return{
            sidId: <?= $row->id ?>,
            loading: false,
            deleting: false,
            entityInfo: {
                singular: 'Álbum',
                plural: 'Álbumes'
            },
            section: 'list',
            fields: {
                id: 0,
                idcode: 0,
                title: 'Probando',
                excerpt: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            },
            albums: <?= json_encode($albums) ?>,
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('albumForm'))
            axios.post(URL_API + 'posts/save/' + this.fields.idcode, formValues)
            .then(response => {
                if ( response.data.savedId > 0 ) {
                    toastr['success']('Guardado')
                    this.fields.idcode = response.data.idcode
                    this.section = 'list'
                    this.getList()
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        setCurrent: function(id, setSection = 'form'){
            this.fields = this.albums.find(album => album.id == id)
            this.section = setSection
        },
        deleteElement: function(){
            this.deleting = true
            axios.delete(URL_API + 'posts/delete/' + this.fields.idcode)
            .then(response => {
                if ( response.data.code == 'DELETED' ) {
                    toastr['info']('Álbum eliminado')
                    this.getList()
                    this.section = 'list'
                    modalSingleDelete.hide()
                } else {
                    toastr['error']('No se pudo eliminar')
                }
                this.deleting = false
            })
            .catch( function(error) {console.log(error)} )
        },
        getList: function(){
            this.loading = true
            axios.get(URL_API + 'sits/albums/' + this.sidId)
            .then(response => {
                this.albums = response.data
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        }
    }
}).mount('#albumsApp')

var modalSingleDelete = new bootstrap.Modal(document.getElementById('modalSingleDelete'))
</script>