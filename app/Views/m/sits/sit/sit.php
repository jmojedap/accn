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
            <div class="my-3 row">
                <div class="col-12 col-md-3"></div>
                <div class="col-12 col-md-9">
                    <button class="btn btn-light w240p" @click="createConversation">
                        Enviar mensaje
                    </button>
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
        createConversation: function(){
            this.loading = true;
            // payload en formato json
            let payload = {
                agent_id: this.sit.id,
                type:'ai-chat',
            };
            axios.post(URL_API + 'conversations/create/', {data: payload})
            .then(response => {
                if ( response.data.conversation_id > 0 ) {
                    toastr['success']('Guardado')
                    window.location.href = URL_MOD + 'chat/conversation/' + response.data.conversation_id
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },  
    },
    mounted(){
        //this.getList()
    }
}).mount('#sitApp')
</script>