<?= view('assets/recaptcha') ?>

<div id="signupApp" class="center_box_320">
    <div v-show="savedId == 0">
        <p class="only-lg text-center">
            Crear nueva cuenta de usuario
        </p>

        <div class="text-center mb-2" v-show="loading">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <form id="signupForm" @submit.prevent="handleSubmit" v-show="!loading">
            <!-- Campo para validación Google ReCaptcha V3 -->
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

            <div class="mb-3">
                <label class="sr-only" for="display_name">Tu nombre</label>
                <input
                    class="form-control" name="display_name" v-model="fields.display_name"
                    title="Debe tener al menos cinco letras" placeholder="¿Cómo te llamas?"
                    minlength="5" required autofocus
                    >
            </div>

            <div class="mb-3">
                <label class="sr-only" for="email">Correo electrónico</label>
                <input
                    name="email" type="email" class="form-control" required
                    title="Correo electrónico" placeholder="Correo electrónico"
                    v-on:change="validateForm" v-model="fields.email"
                    v-bind:class="{'is-invalid': validation.emailUnique == 0, 'is-valid': validation.emailUnique == 1}"
                    >
                <div class="invalid-feedback" v-show="validation.emailUnique == 0">
                    Ya existe una cuenta con este correo electrónico
                </div>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-lg w-100" v-bind:disabled="loading == true">Crear</button>
            </div>
        </form>
    </div>

    <!-- Sección si se registró exitosamente -->
    <div v-show="savedId > 0">
        <div class="text-center mb-2">
            <h1>
                <i class="fa fa-check text-success"></i><br/>
                Listo {{ fields.display_name }}
            </h1>
            <p>
                ¡Ya haces parte de <?= APP_NAME ?>!
            </p>
            <p>
                Revisa la bandeja de entrada de tu correo electrónico
                para activar tu cuenta
            </p>
        </div>
    </div>

    <p class="text-center">
        ¿Ya tienes una cuenta? <a href="<?= URL_APP . 'accounts/login/link' ?>">Ingresa</a>
    </p>
</div>

<script>
var signupApp = createApp({
    data(){
        return{
            fields:{
                display_name: 'Juan Pérez',
                email: 'jperez@gmail.co',
            },
            validated: -1,
            validation: {
                emailUnique: -1
            },
            loading: false,
            savedId: 0,
        }
    },
    methods: {
        handleSubmit: function(){
            if ( this.validated )
            {
                this.loading = true
                var payload = new FormData(document.getElementById('signupForm'))
                
                axios.post(URL_API + 'accounts/create/', payload)
                .then(response => {
                    this.loading = false
                    this.savedId = response.data.savedId

                    if ( response.data.recaptcha == -1 ) {
                        toastr['error']('No se realizó la validación recaptcha')
                        setTimeout(() => {
                            window.location = URL_APP + 'accounts/signup'
                        }, 3000);
                    }
                })
                .catch(function (error) { console.log(error) })
            } else {
                toastr['error']('Revisa las casillas en rojo')
            }
        },
        validateForm: function(){
            var payload = new FormData()
            payload.append('email', this.fields.email)
            axios.post(URL_API + 'accounts/validate/', payload)
            .then(response => {
                this.validation = response.data.validation
                this.validated = response.data.status
            })
            .catch(function (error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#signupApp')
</script>