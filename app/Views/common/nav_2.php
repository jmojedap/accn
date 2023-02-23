<div id="nav2App" class="mb-2">
    <div class="only-lg">
        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
            <?php if ( isset($back_link) ) : ?>
                <li class="nav-item">
                    <a href="<?= $back_link ?>" class="nav-link"> 
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item" v-for="(element, key) in nav_2">
                <a class="nav-link" href="#"
                    v-bind:class="element.class" v-on:click="activateMenu(key)"
                >
                {{ element.text }}
                </a>
            </li>
        </ul>
    </div>

    <div class="only-sm">
        <div class="d-flex justify-content-between">
            <?php if ( isset($back_link) ) : ?>
                <div class="me-2">
                    <a href="<?= $back_link ?>" class="btn-circle"> 
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
            <?php endif; ?>
            <h2 class="nav_2_title">{{ current.text }}</h2>
            <div class="dropdown">
                <button class="btn btn-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li v-for="(element, key) in nav_2">
                        <a class="dropdown-item" href="#" v-on:click="activateMenu(key)" v-bind:class="element.class">
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
            nav_2: nav_2,  //Elementos contenido del menú
            current: { text: 'Menú' }
        }
    },
    methods: {
        activateMenu: function (key) {
            this.current = this.nav_2[key]
            for ( i in this.nav_2 ){
                this.nav_2[i].class = ''
            }
            this.nav_2[key].class = 'active'   //Elemento actual
            if ( this.nav_2[key].anchor ) {
                window.location = URL_APP + this.nav_2[key].cf
            } else {
                this.loadViewA(key)
            }
        },
        loadViewA: function(key){
            appSection = this.nav_2[key].cf
            getSections('nav_2') //routing.js
        },
        setCurrent: function(){
            this.current = this.nav_2.find(item => item.class == 'active')
        }
    },
    mounted(){
        this.setCurrent()
    }
}).mount('#nav2App')
</script>