<div id="navbarApp">
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img class="d-block" src="<?= URL_BRAND ?>logo-navbar.png" alt="Logo App" style="height: 30px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item" v-for="(element, i) in elements" v-bind:class="{'dropdown': element.subelements.length }">
                        <a v-if="element.subelements.length == 0" class="nav-link" href="#"
                                v-bind:class="{'active': element.active }"
                                v-on:click="navClick(i)">
                            {{ element.text }}
                        </a>
                        <a v-else v-on:click="navClick(i)"
                            class="nav-link dropdown-toggle" href="#" role="button"
                            v-bind:class="{'active': element.active }"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            >
                            {{ element.text }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownNav" v-if="element.subelements.length > 0">
                            <li v-for="(subelement, j) in element.subelements">
                                <a class="dropdown-item" href="#" 
                                    v-bind:class="{ 'active': subelement.active }"
                                    v-on:click="navClickSub(i,j)">
                                    {{ subelement.text }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav -2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_APP ?>accounts/login_link" role="button">
                            Ingresar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_APP ?>accounts/signup" role="button">
                            Registrarme
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<script>
//Activación inicial de elementos actuales
//-----------------------------------------------------------------------------
nav_1_elements.forEach(element => {
    //Activar elemento actual, si está en las secciones
    if ( element.sections.includes(appSection) ) { element.active = true }
    //Activar subelemento actual, si está en las secciones
    if ( element.subelements )
    {
        element.subelements.forEach(subelement => {
            if ( subelement.sections.includes(appSection) )
            {
                element.active = true
                subelement.active = true
            }
        })
    }
});

// VueApp
//-----------------------------------------------------------------------------
const navbarApp = createApp({
    data(){
        return{
            elements: nav_1_elements
        }
    },
    methods: {
        navClick: function(i){
            if ( this.elements[i].subelements.length == 0 )
            {
                this.elements.forEach(element => { element.active = false; });
                this.elements[i].active = true;
                if ( this.elements[i].anchor ) {
                    window.location = URL_APP + this.elements[i].cf;
                } else {
                    appSection = this.elements[i].cf;
                    loadSections('nav_1');
                }
            }
        },
        navClickSub: function(i,j){
            //Activando elemento
            this.elements.forEach(element => { element.active = false; });
            this.elements[i].active = true;

            //Activando subelemento
            this.elements[i].subelements.forEach(subelement => { subelement.active = false; });
            this.elements[i].subelements[j].active = true;

            if ( this.elements[i].subelements[j].anchor ) {
                window.location = URL_APP + this.elements[i].subelements[j].cf;
            } else {
                //Cargando secciones
                appSection = this.elements[i].subelements[j].cf;
                loadsections('nav_1');
            }
        }
    }
}).mount('#navbarApp')
</script>
