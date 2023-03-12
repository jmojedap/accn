<?php
    $random = rand(100,999);
?>

<script>
var newUser = {
    role: 21,
    first_name: 'Nuevo',
    last_name: 'Eliminar',
    email: 'user<?= $random ?>@example.co',
    username: 'jmojedap',
    password: 'Probando123'
}

// VueApp
//-----------------------------------------------------------------------------
var addUser = createApp({
    data() {
        return {
            entityInfo: <?= json_encode($entityInfo) ?>,
            urlMod: URL_MOD,
            user: newUser,
            validation: {
                emailUnique: -1
            },
            arrRoles: <?= json_encode($arrRoles) ?>,
            rowId: 0,
            idCode: 0,
            hidePassword: true,
            loading: false,
        }
    },
    methods: {
        validateForm: function() {
            var formValues = new FormData(document.getElementById('addForm'))
            axios.post(URL_API + 'users/validate/', formValues)
                .then(response => {
                    this.validation = response.data.validation
                })
                .catch(function(error) { console.log(error) })
        },
        handleSubmit: function() {
            this.loading = true
            var formData = new FormData(document.getElementById('addForm'))
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
            axios.post(URL_API + 'users/create/', formData)
                .then(response => {
                    if (response.data.savedId > 0) {
                        this.rowId = response.data.savedId
                        this.idCode = response.data.idcode
                        this.resetForm()
                        createdModal.show()
                    } else {
                        toastr['error']('No se guardó');
                    }
                    this.loading = false
                })
                .catch(function(error) {
                    console.log(error)
                })
        },
        resetForm: function() {
            for (key in this.user) this.user[key] = ''
            this.validation.emailUnique = -1
        },
        goToCreated: function(){
            window.location = URL_MOD + `users/profile/` + this.idCode
        },
    }
}).mount('#addUser')

var createdModal = new bootstrap.Modal(document.getElementById('createdModal'))
</script>