<script>
var misSitsApp = createApp({
    data(){
        return{
            url_mod: URL_MOD,
            section: 'list',
            loading: false,
            deleting: false,
            sits: <?= json_encode($sits) ?>,
            currentSit: {},
            entityInfo: {
                singular: 'Sit'
            },
            fields: {},
        }
    },
    methods: {
        getList(){
            this.loading = true;
            axios.get(URL_API + 'sits/my_sits')
                .then(response => {
                    this.sits = response.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.log(error);
                    this.loading = false;
                });
        },
        setCurrent: function(sitId){
            this.currentSit = this.sits.find(sit => sit.id == sitId);
        },
        deleteElement: function(){
            const idCode = this.currentSit.idcode;
            this.deleting = true;

            axios.delete(URL_API + 'sits/delete/' + idCode)
                .then(response => {
                    if (response.data.code === 'DELETED') {
                        this.sits = this.sits.filter(sit => sit.idcode != idCode);
                        toastr['info']('Sit eliminado');
                    } else {
                        toastr['warning']('No se pudo eliminar el Sit');
                    }

                    $('#modalSingleDelete').modal('hide');
                })
                .catch(error => {
                    console.log(error);
                    toastr['error']('Ocurrió un error al eliminar el Sit');
                })
                .finally(() => {
                    this.deleting = false;
                });
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#misSitsApp')
</script>