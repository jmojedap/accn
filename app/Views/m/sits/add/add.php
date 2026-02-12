<div id="addSitApp">
    <?= view('m/sits/add/form') ?>
</div>

<script>
var addSitApp = createApp({
    data(){
        return{
            loading: false,
            fields: {
                type_id: '101',
                title: 'Probando',
                content: 'Contenido de prueba',
            },
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true;
            let formData = new FormData();
            formData.append('type_id', this.fields.type_id);
            formData.append('title', this.fields.title);
            formData.append('content', this.fields.content);
            axios.post(URL_API + 'posts/create', formData)
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    this.loading = false;
                    console.log(error);
                }); 
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#addSitApp')
</script>