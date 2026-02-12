<?= view('m/sits/style') ?>

<div id="sitApp">
    <div class="container sit">
        <div class="center_box_750">
            <div class="row">
                <div class="col-12 col-md-4">
                    <img :src="sit.image" v-bind:alt="sit.title">
                </div>
                <div class="col-12 col-md-8">
                    <h1 class="title">{{ sit.title }}</h1>
                    <div class="excerpt" v-html="sit.excerpt"></div>
                </div>
            </div>
        </div>
        <div class="center_box_750">
        <div class="row">
            <div class="col-12">
                
                
                <div v-html="sit.content"></div>


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