<script>
var misSitsApp = createApp({
    data(){
        return{
            url_mod: URL_MOD,
            section: 'list',
            loading: false,
            deleting: false,
            sits: <?= json_encode($sits) ?>,
            currentSit: {},
            entityInfo: {
                singular: 'Sit'
            },
            fields: {},
        }
    },
    methods: {
        getList(){
            this.loading = true;
            axios.get(URL_API + 'sits/my_sits')
                .then(response => {
                    this.sits = response.data;
                    this.loading = false;
                })
                .catch(error => {
                    console.log(error);
                    this.loading = false;
                });
        },
        setCurrent: function(sitId){
            this.currentSit = this.sits.find(sit => sit.id == sitId);
        },
        deleteElement: function(){
            axios.get(URL_API + 'sits/delete/' + this.currentSit.id)
            .then(response => {
                console.log(response.data);
            })
            .catch(function(error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList()
    }
}).mount('#misSitsApp')
</script>