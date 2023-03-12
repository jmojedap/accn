<script>
var fields = <?= json_encode($row) ?>;

// VueApp
//-----------------------------------------------------------------------------
var editUser = createApp({
    data() {
        return {
            urlApp: URL_MOD,
            fields: fields,
            validation: {
                emailUnique: -1
            },
            arrRoles: <?= json_encode($arrRoles) ?>,
            arrGenders: <?= json_encode($arrGenders) ?>,
            rowId: 0,
            idCode: '<?= $row->idcode ?>',
            hidePassword: true,
            loading: false,
        }
    },
    methods: {
        validateForm: function() {
            var formValues = new FormData(document.getElementById('editForm'))
            axios.post(URL_API + 'users/validate/', formValues)
                .then(response => {
                    this.validation = response.data.validation
                })
                .catch(function(error) { console.log(error) })
        },
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('editForm'))
            axios.post(URL_API + 'users/validate/', formData)
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
            axios.post(URL_API + 'users/update/' + this.idCode, formData)
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
    }
}).mount('#editUser')
</script>