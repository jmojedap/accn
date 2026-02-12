<script>
var misSitsApp = createApp({
    data(){
        return{
            url_mod: URL_MOD,
            section: 'list',
            loading: false,
            sits: <?= json_encode($sits) ?>,
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
        }
    },
    mounted(){
        //this.getList()
    }
}).mount('#misSitsApp')
</script>