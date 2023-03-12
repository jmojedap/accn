<div id="nav2App" class="mb-2">
    <div class="only-lg">
        <ul class="nav nav-tabs" role="tablist">
            <?php if ( isset($backLink) ) : ?>
                <li class="nav-item">
                    <a href="<?= $backLink ?>" class="nav-link"> 
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item" v-for="(element, key) in nav2">
                <a class="nav-link pointer"
                    v-bind:class="element.class" v-on:click="activateMenu(key)"
                >
                {{ element.text }}
                </a>
            </li>
        </ul>
    </div>

    <div class="only-sm">
        <div class="d-flex justify-content-between">
            <h2 class="nav2_title">{{ current.text }}</h2>
            <div class="dropdown">
                <button class="btn btn-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li v-for="(element, key) in nav2">
                        <a class="dropdown-item pointer" v-on:click="activateMenu(key)" v-bind:class="element.class">
                            {{ element.text }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
var nav2App = createApp({
    data(){
        return{
            nav2: nav2,  //Elementos contenido del menú
            current: { text: 'Menú' }
        }
    },
    methods: {
        activateMenu: function (key) {
            this.current = this.nav2[key]
            for ( i in this.nav2 ){
                this.nav2[i].class = ''
            }
            this.nav2[key].class = 'active'   //Elemento actual
            if ( this.nav2[key].anchor ) {
                window.location = URL_MOD + this.nav2[key].appSection
            } else {
                this.loadViewA(key)
            }
        },
        loadViewA: function(key){
            appSection = this.nav2[key].appSection
            getSections('nav2') //routing.js
        },
        setCurrent: function(){
            this.current = this.nav2.find(item => item.class == 'active')
        }
    },
    mounted(){
        this.setCurrent()
    }
}).mount('#nav2App')
</script>