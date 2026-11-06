<link rel="stylesheet" href="<?= URL_RESOURCES ?>templates/easypml/auth.css">

<section id="loginApp" class="auth-page" aria-labelledby="auth-title" v-cloak>
    <header class="auth-header">
        <h1 id="auth-title" class="auth-title">Inicia sesión</h1>
        <p class="auth-description">Ingresa a tu cuenta de <?= esc(APP_NAME) ?>.</p>
    </header>

    <form id="loginForm" accept-charset="utf-8" method="post" @submit.prevent="handleSubmit" :aria-busy="loading">
        <fieldset :disabled="loading">
            <legend class="visually-hidden">Datos de acceso</legend>
            <div class="mb-3">
                <label class="form-label" for="login-username">Correo electrónico o usuario</label>
                <input id="login-username" class="form-control rounded-0" name="username" type="text"
                    autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="login-password">Contraseña</label>
                <input id="login-password" class="form-control rounded-0" name="password" type="password"
                    autocomplete="current-password" required>
            </div>
            <div class="mb-4 form-check">
                <input id="login-remember" class="form-check-input rounded-0" name="remember" type="checkbox" value="1">
                <label class="form-check-label" for="login-remember">Recordarme</label>
            </div>
            <button type="submit" class="btn btn-primary rounded-0 auth-submit" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                {{ loading ? 'Ingresando…' : 'Ingresar' }}
            </button>
        </fieldset>
    </form>

    <div class="mt-3" aria-live="polite" aria-atomic="true">
        <div v-if="message" class="alert alert-warning rounded-0" role="alert">{{ message }}</div>
    </div>

    <footer class="auth-footer">
        <p><a href="<?= URL_APP ?>accounts/login/link">¿Olvidaste tu contraseña? Ingresa con un enlace</a></p>
        <p>¿No tienes una cuenta? <a href="<?= URL_APP ?>accounts/signup">Regístrate</a></p>
    </footer>
</section>

<script>
var loginApp = createApp({
    data() {
        return {
            loading: false,
            message: ''
        };
    },
    methods: {
        async handleSubmit() {
            if (this.loading) return;

            const payload = new FormData(document.getElementById('loginForm'));
            this.loading = true;
            this.message = '';

            try {
                const { data } = await axios.post(URL_API + 'accounts/validate_login', payload);
                if (Number(data.status) === 1) {
                    localStorage.setItem('jwtToken', data.token);
                    window.location.href = URL_MOD + 'accounts/logged';
                } else {
                    this.message = data.message || 'Revisa tus datos e intenta de nuevo.';
                }
            } catch (error) {
                this.message = 'No pudimos iniciar sesión. Intenta de nuevo.';
            } finally {
                this.loading = false;
            }
        }
    }
}).mount('#loginApp');
</script>