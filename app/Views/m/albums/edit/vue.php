<script>
var albumApp = createApp({
    data(){
        return{
            section: 'form',
            loading: false,
            fields: <?= json_encode($row) ?>,
            arrStatus: ['Privado', 'Oculto', 'Público']
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            const form = document.getElementById('albumForm')
            const formData = new FormData(form)
            formData.append('id', this.fields.id)
            formData.append('title', this.fields.title)
            formData.append('description', this.fields.description)
            formData.append('status', this.fields.status)
            axios.post('/api/posts/save', formData)
                .then(response => {
                    this.loading = false
                    this.fields = response.data
                })
                .catch(error => {
                    this.loading = false
                    console.log(error)
                })
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#albumApp')
</script>