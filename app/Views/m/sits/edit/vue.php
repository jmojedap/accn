<script>
var fields = <?= json_encode($row) ?>;

// VueApp
//-----------------------------------------------------------------------------
var editPost = createApp({
    data() {
        return {
            urlApp: URL_MOD,
            fields: fields,
            arrTypes: ItemsApp.arrayCategory(33),
            loading: false,
        }
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('editForm'))
            axios.post(URL_API + 'posts/update/' + this.fields.idcode, formData)
                .then(response => {
                    console.log(response.data)
                    if (response.data.saved) {
                        toastr['success']('Guardado')
                        this.loading = false
                    } else {
                        toastr['error']('Hay casillas incompletas o incorrectas')
                        this.loading = false
                    }
                })
                .catch(function(error) { console.log(error) })
        },
    }
}).mount('#editPost')
</script>