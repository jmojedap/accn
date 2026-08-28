<aside class="main-sidebar" id="nav1App">
    <section class="sidebar">
        <ul class="sidebar-menu">
            <li class="nav-item">
                <a href="<?= URL_APP ?>">
                    <i class="fa-fw fa fa-circle-left"></i> App
                </a>
            </li>
            <li v-for="(element, i) in elements" v-bind:class="{ treeview: element.subelements.length, 'active': element.active }">
                <a href="#" v-on:click="nav_1_click(i)">
                    <i class="fa-fw" v-bind:class="element.icon"></i>
                    {{ element.text }}
                    <i class="fa fa-angle-left float-end" v-if="element.subelements.length > 0"></i>
                </a>
                <ul class="treeview-menu" v-if="element.subelements.length > 0">
                    <li v-for="(subelement, j) in element.subelements" v-bind:class="{ 'active': subelement.active }">
                        <a href="#" v-on:click="nav_1_click_sub(i,j)">
                            <i class="fa-fw" v-bind:class="subelement.icon"></i>
                            {{ subelement.text }}
                        </a>
                    </li>
                </ul>
            </li>
            <li class="only-sm">
                <a href="#">
                    <i class="fa-fw fa fa-user"></i>Mi cuenta
                    <i class="fa fa-angle-left float-end"></i>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="<?= URL_APP ?>accounts/profile">
                            <i class="fa-fw fa fa-gear"></i> Configurar
                        </a>
                    </li>
                    <li>
                        <!-- Logout usa POST para que un enlace externo no pueda cerrar la sesión. -->
                        <form method="post" action="<?= URL_APP ?>accounts/logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link text-start w-100">
                                <i class="fa-fw fa fa-solid fa-right-from-bracket"></i>Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </section>
</aside>

<script>
/**
 * Limita la navegación visible del editor a contenido, archivos e ítems.
 * La seguridad real sigue siendo aplicada por los filtros de Routes.php.
 */
function navigationForRole(elements, role) {
    if (role !== 3) {
        return elements;
    }

    return elements.reduce((allowedElements, element) => {
        if (['Posts', 'Archivos'].includes(element.text)) {
            allowedElements.push(element);
        }

        if (element.text === 'Ajustes') {
            const itemsSettings = {
                ...element,
                subelements: element.subelements.filter(subelement => subelement.text === 'Ítems'),
            };
            allowedElements.push(itemsSettings);
        }

        return allowedElements;
    }, []);
}

// Prepara el menú según el rol antes de calcular sus elementos activos.
nav1Elements = navigationForRole(nav1Elements, APP_RID);

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

// VueApp nav_1
//-----------------------------------------------------------------------------
var nav1App = createApp({
    data(){
        return{
            urlMod: URL_MOD,
            elements: nav1Elements
        }
    },
    methods: {
        nav_1_click: function(i){
            if ( this.elements[i].subelements.length == 0 )
            {
                if ( this.elements[i].anchor ) {
                    window.location = this.urlMod + this.elements[i].cf;
                } else {
                    this.elements.forEach(element => { element.active = false })
                    this.elements[i].active = true;
                    $('.treeview-menu').slideUp();
                    appSection = this.elements[i].cf;
                    getSections('nav1')
                    this.hide_sidebar()
                }
            }
        },
        nav_1_click_sub: function(i,j){
            if ( this.elements[i].subelements[j].anchor ) {
                window.location = this.urlMod + this.elements[i].subelements[j].cf;
            } else {
                //Activando elemento
                this.elements.forEach(element => { element.active = false; });
                this.elements[i].active = true;
    
                //Activando subelemento
                this.elements[i].subelements.forEach(subelement => { subelement.active = false; });
                this.elements[i].subelements[j].active = true;
    
                //Cargando secciones
                appSection = this.elements[i].subelements[j].cf;
                getSections('nav1')
                this.hide_sidebar()
            }
        },
        hide_sidebar: function(){
            setTimeout(() => {
                //Enable hide menu when clicking on the content-wrapper on small screens
                if ($(window).width() <= 767 && $("body").hasClass("sidebar-open")) {
                    $("body").removeClass('sidebar-open');
                }
            }, 150);
        },
    },
}).mount('#nav1App')
</script>
