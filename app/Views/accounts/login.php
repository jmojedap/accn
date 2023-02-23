<div id="loginApp" class="text-center center_box mw360p">
    <p>
        ¿No tienes una cuenta? <a href="<?= URL_APP . 'accounts/signup' ?>">Regístrate</a>
    </p>
    <form accept-charset="utf-8" method="POST" id="loginForm" @submit.prevent="handleSubmit">
        <fieldset v-bind:disabled="loading">            
            <div class="mb-3">
                <input
                    class="form-control form-control-lg" name="username" value="admin"
                    placeholder="Correo electrónico" required
                    title="Correo electrónico">
            </div>
            <div class="mb-3">
                <input type="password" class="form-control form-control-lg" name="password" placeholder="Contraseña" required value="malotv252">
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
            </div>
            
            <div class="mb-3">
                <a href="<?= URL_APP . 'accounts/recovery' ?>">¿Olvidaste tu contraseña?</a>
            </div>
        </fieldset>
    </form>
    
    <div id="messages" v-show="status == 1">
        <div class="alert alert-warning" v-for="message in messages">
            {{ message }}
        </div>
    </div>
</div>

<script>
var loginApp = createApp({
    data(){
        return{
            loading: false,
            messages: [],
            status: -1
        }
    },
    methods: {
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('loginForm'))
            axios.post(URL_API + 'accounts/validate_login', formValues)
            .then(response => {
                this.loading = false
                if ( response.data.status == 1 )
                {
                    this.saveToken(response.data.token)
                } else {
                    this.messages = response.data.messages;
                    this.status = response.data.status;
                }
            })
            .catch(function (error) { console.log(error) })
        },
        saveToken: function(jwtToken){
            console.log('Guardano token')
            localStorage.setItem("jwtToken", jwtToken);
            window.location = URL_APP + 'accounts/logged';
        },
    },
}).mount('#loginApp')
</script>
