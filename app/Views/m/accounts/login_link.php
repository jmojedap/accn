<link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/auth.css">

<section id="loginApp" class="auth-page" aria-labelledby="auth-title" v-cloak>
    <header class="auth-header">
        <h1 id="auth-title" class="auth-title">Ingresa con un enlace</h1>
        <p class="auth-description">
            Escribe tu correo electrónico y te enviaremos un enlace para acceder a <?= esc(APP_NAME) ?>.
        </p>
    </header>

    <form id="loginForm" accept-charset="utf-8" method="post" @submit.prevent="handleSubmit" :aria-busy="loading">
        <fieldset :disabled="loading">
            <legend class="visually-hidden">Solicitar enlace de acceso</legend>
            <div class="mb-4">
                <label class="form-label" for="login-email">Correo electrónico</label>
                <input id="login-email" class="form-control rounded-0" name="email" type="email"
                    v-model.trim="email" autocomplete="email" autocapitalize="none" spellcheck="false" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary rounded-0 auth-submit" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                {{ loading ? 'Enviando…' : 'Enviar enlace' }}
            </button>
        </fieldset>
    </form>

    <div class="mt-3" aria-live="polite" aria-atomic="true">
        <div v-if="accessKey && status === -1" class="alert alert-warning rounded-0" role="alert">
            El enlace de acceso ya no es válido. Solicita uno nuevo.
        </div>
        <div v-if="message" class="alert rounded-0" :class="status === 1 ? 'alert-info' : 'alert-warning'" role="status">
            {{ message }}
        </div>
    </div>
    <a v-if="link" :href="link" class="btn btn-light rounded-0 w-100 mt-3">Ingresar</a>

    <footer class="auth-footer">
        <p><a href="<?= URL_APP ?>accounts/login">Ingresar con contraseña</a></p>
        <p>¿No tienes una cuenta? <a href="<?= URL_APP ?>accounts/signup">Regístrate</a></p>
    </footer>
</section>

<script>
var loginApp = createApp({
    data() {
        return {
            loading: false,
            message: '',
            status: -1,
            link: '',
            email: '',
            accessKey: <?= json_encode($accessKey ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
    },
    methods: {
        async handleSubmit() {
            if (this.loading) return;

            const payload = new FormData(document.getElementById('loginForm'));
            this.loading = true;
            this.message = '';
            this.link = '';

            try {
                const { data } = await axios.post(URL_API + 'accounts/get_login_link', payload);
                this.status = Number(data.status);
                this.message = data.message || 'No pudimos enviar el enlace. Intenta de nuevo.';
                if (this.status === 1) {
                    this.link = data.link || '';
                    this.email = '';
                }
            } catch (error) {
                this.status = 0;
                this.message = 'No pudimos enviar el enlace. Intenta de nuevo.';
            } finally {
                this.loading = false;
            }
        }
    }
}).mount('#loginApp');
</script>