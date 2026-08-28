<div id="addSitApp">
    <?= view('m/sits/add/form') ?>
</div>

<script>
var addSitApp = createApp({
    data(){
        return{
            loading: false,
            fields: {
                title: '',
                content: '',
            },
        }
    },
    methods: {
        handleSubmit: function(){
            const form = document.getElementById('sitForm');

            if (!form.reportValidity()) {
                return;
            }

            this.loading = true;
            const formData = new FormData(form);
            formData.set('type_id', '101');

            axios.post(URL_API + 'posts/create', formData)
                .then(response => {
                    if (response.data.savedId > 0) {
                        toastr['success']('Sit creado');
                        window.location = URL_MOD + 'sits/mis_sits/';
                        return;
                    }

                    toastr['error']('No se pudo crear el Sit');
                })
                .catch(error => {
                    console.log(error);
                    toastr['error']('Ocurrió un error al crear el Sit');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#addSitApp')
</script>