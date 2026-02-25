<?= view('m/sits/style') ?>

<div id="sitApp">
    <div class="container sit">
        <div class="center_box_750">
            <div class="row">
                <div class="col-12 col-md-3 text-center">
                    <img :src="sit.url_thumbnail" v-bind:alt="sit.title" class="img-fluid rounded rounded-circle">
                </div>
                <div class="col-12 col-md-9">
                    <h1 class="title">{{ sit.title }}</h1>
                    <div class="excerpt" v-html="sit.excerpt"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var sitApp = createApp({
    data(){
        return{
            loading: false,
            fields: {},
            sit: <?= json_encode($row) ?>,
        }
    },
    methods: {
        
    },
    mounted(){
        //this.getList()
    }
}).mount('#sitApp')
</script>