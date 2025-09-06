<div id="loginApp" class="text-center center_box mw360p">
    <p>
        ¿No tienes una cuenta? <a href="<?= URL_APP . 'accounts/signup' ?>">Regístrate</a>
    </p>
    <form accept-charset="utf-8" method="POST" id="loginForm" @submit.prevent="handleSubmit">
        <fieldset v-bind:disabled="loading">
            <p>
                Escribe tu correo electrónico y te enviaremos un link para acceder a
                <span class="text-primary"><?= APP_NAME ?></span>
            </p>
            <div class="mb-3">
                <input class="form-control form-control-lg" name="email" type="email" v-model="email"
                    placeholder="Correo electrónico" required title="Correo electrónico">
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-2"
                    title="Enviar link o enlace para acceder a la aplicación">
                    Enviar link
                </button>
                <div class="mb-3" v-show="loading">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>

    <div class="alert alert-warning" v-if="accessKey">
        El link de acceso ya no es válido.
    </div>

    <p v-show="link">
        <a v-bind:href="link" v-show="link" class="btn btn-lg btn-light">
            INGRESAR
        </a>
    </p>

    <div v-show="status != -1">
        <div class="alert" v-bind:class="alertClass">
            {{ message }}
        </div>
    </div>
</div>

<script>
var loginApp = createApp({
    data() {
        return {
            loading: false,
            message: '',
            alertClass: 'alert-warning',
            status: -1,
            link: null,
            email: '',
            accessKey: <?= isset($accessKey) ? "'{$accessKey}'" : 'null' ?>
        }
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formValues = new FormData(document.getElementById('loginForm'))
            axios.post(URL_API + 'accounts/get_login_link', formValues)
                .then(response => {
                    this.loading = false
                    this.status = response.data.status
                    this.message = response.data.message;
                    this.link = response.data.link || '';
                    if (this.status == 1) {
                        this.alertClass = 'alert-info'
                        this.email = ''
                    }
                })
                .catch(function(error) {
                    console.log(error)
                })
        },
    },
}).mount('#loginApp')
</script>