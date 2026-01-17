<div id="sitsApp" class="sits-wrapper">
    <!-- Geometric Background Elements -->


    <div class="center_box_920 relative-content">
        <div class="header-section">
            <h2 class="section-title">Explora Sits</h2>
            <div class="title-underline"></div>
        </div>

        <div class="sits-grid">
            <div class="sit-card" v-for="sit in sits" :key="sit.id" v-show="sit.status == 'Activo'">
                <div class="card-image-wrapper">
                    <img v-bind:src="`<?php echo URL_CONTENT . 'multisits/thumbnails/' ?>${sit.slug}.jpg`" class="sit-image" alt="...">
                    <div class="card-overlay">
                        <a v-bind:href="sit.url" class="btn-explore" target="_blank">
                            <span>Ver detalles</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="card-content">
                    <span class="sit-category">{{ sit.service }}</span>
                    <h3 class="sit-title">{{ sit.name }}</h3>
                    <p class="sit-description">{{ sit.description }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('m/info/explore_sits/style'); ?>

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