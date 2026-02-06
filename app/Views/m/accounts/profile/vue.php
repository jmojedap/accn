<script>
var userProfileApp = createApp({
    data() {
        return {
            section: 'info',
            user: <?= json_encode($user) ?>,
            fields: <?= json_encode($user) ?>,
            loading: false,
            validation: {
                emailUnique: -1, usernameUnique: -1
            },
            arrGenders: ItemsApp.arrayCategory(59),
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
            var formValues = new FormData(document.getElementById('accountForm'))
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
            axios.post(URL_API + 'accounts/update/', formData)
            .then(response => {
                if ( response.data.saved ) {
                    toastr['success']('Guardado')
                    this.user = this.fields
                } else {
                    toastr['error']('No se guardó')
                }
                this.loading = false
            })
            .catch(function(error) { console.log(error) })
        },
        genderName: function(value = '', field = 'name'){
            return ItemsApp.fieldValue(59, value, field)
        },
        setDisplayName: function(){
            console.log('display_name: ', this.fields.display_name)
            if ( this.fields.display_name.length <= 3 ) {
                this.fields.display_name = this.fields.first_name + ' ' + this.fields.last_name
            }
        },
    },
}).mount('#userProfileApp')
</script>