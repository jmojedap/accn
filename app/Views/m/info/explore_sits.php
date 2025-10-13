<div id="sitsApp">
    <div class="center_box_920">
        <div class="row">
            <div class="col-md-4" v-for="sit in sits" :key="sit.id" v-show="sit.status == 'Activo'">
                <div class="card">
                    <img v-bind:src="`<?php echo URL_CONTENT . 'multisits/thumbnails/' ?>${sit.slug}.jpg`" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">{{ sit.name }}</h5>
                        <p class="text-muted">{{ sit.service }}</p>
                        <p class="card-text">{{ sit.description }}</p>
                        <a v-bind:href="sit.url" class="btn btn-primary" target="_blank">Ver más</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var sitsApp = createApp({
    data(){
        return{
            sits: <?php echo $sits; ?>,
            loading: false,
            fields: {},
        }
    },
    methods: {
        getList: function(){
            axios.get(URL_API + 'controlador/funcion/')
            .then(response => {
                if ( response.data.status == 1 ) {
                    
                }
            })
            .catch(function(error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#sitsApp')
</script>