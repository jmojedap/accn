<link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/auth.css">

<section id="signupApp" class="auth-page" aria-labelledby="auth-title" v-cloak>
    <header class="auth-header">
        <h1 id="auth-title" class="auth-title">Crea tu cuenta</h1>
        <p class="auth-description">Regístrate para hacer parte de <?= esc(APP_NAME) ?>.</p>
    </header>

    <form v-show="savedId === 0" id="signupForm" accept-charset="utf-8" method="post"
        @submit.prevent="handleSubmit" :aria-busy="loading">
        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
        <fieldset :disabled="loading">
            <legend class="visually-hidden">Datos de registro</legend>
            <div class="mb-3">
                <label class="form-label" for="signup-name">Tu nombre</label>
                <input id="signup-name" class="form-control rounded-0" name="display_name"
                    v-model.trim="fields.display_name" autocomplete="name" minlength="5"
                    title="Escribe al menos cinco caracteres" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label" for="signup-email">Correo electrónico</label>
                <input id="signup-email" class="form-control rounded-0" name="email" type="email"
                    v-model.trim="fields.email" autocomplete="email" autocapitalize="none" spellcheck="false" required
                    @input="emailUnique = -1; messages = []"
                    :class="{'is-invalid': emailUnique === 0}"
                    :aria-invalid="emailUnique === 0" aria-describedby="signup-email-error">
                <div id="signup-email-error" class="invalid-feedback">
                    Ya existe una cuenta con este correo electrónico.
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-0 auth-submit" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                {{ loading ? 'Creando cuenta…' : 'Crear cuenta' }}
            </button>
        </fieldset>
    </form>

    <div class="mt-3" aria-live="polite" aria-atomic="true">
        <div v-for="(message, index) in messages" :key="index" class="alert alert-warning rounded-0" role="alert">
            {{ message }}
        </div>
        <div v-if="savedId > 0" class="auth-success" role="status">
            <h2 class="h4">¡Listo, {{ fields.display_name }}!</h2>
            <p>¡Ya haces parte de <?= esc(APP_NAME) ?>!</p>
            <p class="auth-description">Revisa tu correo electrónico para activar tu cuenta.</p>
        </div>
    </div>

    <footer class="auth-footer">
        <p>¿Ya tienes una cuenta? <a href="<?= URL_APP ?>accounts/login/link">Ingresa</a></p>
    </footer>
</section>

<?= view('assets/recaptcha') ?>

<script>
var signupApp = createApp({
    data() {
        return {
            fields: { display_name: '', email: '' },
            emailUnique: -1,
            loading: false,
            savedId: 0,
            messages: []
        };
    },
    methods: {
        async handleSubmit() {
            if (this.loading) return;

            const payload = new FormData(document.getElementById('signupForm'));
            this.loading = true;
            this.messages = [];

            try {
                if (!await this.validateForm()) return;

                // Renueva el token al enviar: puede caducar mientras se completa el formulario.
                const token = await new Promise((resolve, reject) => {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(<?= json_encode(K_RCSK) ?>, { action: 'homepage' }).then(resolve, reject);
                    });
                });
                payload.set('g-recaptcha-response', token);

                const { data } = await axios.post(URL_API + 'accounts/create/', payload);
                this.savedId = Number(data.savedId) || 0;
                if (this.savedId === 0) {
                    this.messages = data.errors && data.errors.length
                        ? data.errors : [data.message || 'No pudimos crear tu cuenta. Intenta de nuevo.'];
                }
            } catch (error) {
                this.messages = ['No pudimos completar el registro. Intenta de nuevo.'];
            } finally {
                this.loading = false;
            }
        },
        async validateForm() {
            const payload = new FormData();
            payload.append('email', this.fields.email);
            const { data } = await axios.post(URL_API + 'accounts/validate/', payload);
            this.emailUnique = Number(data.validation.emailUnique);
            if (Number(data.status) !== 1) {
                this.messages = data.errors && data.errors.length ? data.errors : ['Revisa los datos del formulario.'];
                return false;
            }
            return true;
        }
    }
}).mount('#signupApp');
</script>