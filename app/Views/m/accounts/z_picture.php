<div id="pictureApp">
    <?= $this->load->view('m/accounts/picture_form') ?>
</div>

<script>
var pictureApp = createApp({
    data(){
        return{
            loading: false,
            fields: {},
        }
    },
    methods: {
        uploadFile(){
            this.loading = true;
            axios.post(base_url('m/accounts/picture'), this.fields)
                .then(response => {
                    this.loading = false;
                    this.fields = response.data;
                })
                .catch(error => {
                    this.loading = false;
                    console.log(error);
                })
        }
    },
    mounted(){
        //this.getList()
    }
}).mount('#pictureApp')
</script>