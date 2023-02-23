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
                        <a class="nav-link" href="<?= URL_APP ?>accounts/logout" role="button">
                            Salir
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
nav1Elements.forEach(element => {
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
            elements: nav1Elements
        }
    },
    methods: {
        navClick: function(i){
            if ( this.elements[i].subelements.length == 0 )
            {
                this.elements.forEach(element => { element.active = false; });
                this.elements[i].active = true;
                if ( this.elements[i].anchor ) {
                    window.location = URL_CUR + this.elements[i].appSection;
                } else {
                    appSection = this.elements[i].appSection;
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
                window.location = URL_CUR + this.elements[i].subelements[j].appSection;
            } else {
                //Cargando secciones
                appSection = this.elements[i].subelements[j].appSection;
                loadsections('nav_1');
            }
        },
        logout: function(){
            axios.get(URL_API + 'accounts/logout/ajax')
            .then(response => {
                if ( response.data.status == 1 ) {
                    window.location = URL_APP + 'accounts/login'
                }
            })
            .catch(function(error) { console.log(error)} )
        },
    }
}).mount('#navbarApp')
</script>
