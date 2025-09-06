<script>
var userProfileApp = createApp({
    data() {
        return {
            section: 'form',
            user: <?= json_encode($user) ?>,
            fields: <?= json_encode($user) ?>,
            loading: false,
            urlApp: URL_MOD,
            validation: {
                emailUnique: -1
            },
            arrGenders: <?= json_encode($arrGenders) ?>,
            rowId: 0,
            idCode: '<?= $user->idcode ?>',
            hidePassword: true,
        }
    },
    methods: {
        setSection: function(newSection) {
            this.section = newSection
        },
        validateForm: function() {
            var formValues = new FormData(document.getElementById('editForm'))
            axios.post(URL_API + 'accounts/validate/', formValues)
                .then(response => {
                    this.validation = response.data.validation
                })
                .catch(function(error) { console.log(error) })
        },
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('accountForm'))
            axios.post(URL_API + 'accounts/validate/', formData)
                .then(response => {
                    if (response.data.status == 1) {
                        this.submitForm(formData)
                    } else {
                        toastr['error']('Hay casillas incompletas o incorrectas')
                        this.loading = false
                    }
                })
                .catch(function(error) { console.log(error) })
        },
        submitForm: function(formData) {
            axios.post(URL_API + 'accounts/update/' + this.idCode, formData)
                .then(response => {
                    if ( response.data.saved ) {
                        toastr['success']('Guardado')
                    } else {
                        toastr['error']('No se guardó')
                    }
                    this.loading = false
                })
                .catch(function(error) { console.log(error) })
        },
    },
    mounted() {
        //this.getList()
    }
}).mount('#userProfileApp')
</script>