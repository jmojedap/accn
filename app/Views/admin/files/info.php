<div id="infoFileApp">
    <div class="row center_box_920">
        <div class="col-md-4">
            <img
                v-bind:src="row.url"
                class="rounded w-100"
                v-bind:alt="row.title"
                onerror="this.src='<?= URL_IMG ?>app/nd.png'"
            >
            <img
                v-bind:src="row.url_thumbnail"
                class="sqr-120 rounded"
                v-bind:alt="row.title"
                onerror="this.src='<?= URL_IMG ?>app/nd.png'"
            >
        </div>
        <div class="col-md-8">
            <table class="table bg-white">
                <tbody>
                    <tr>
                        <td>Título</td>
                        <td>{{ row.title }}</td>
                    </tr>
                    <tr>
                        <td>URL</td>
                        <td>{{ row.url }}</td>
                    </tr>
                    <tr>
                        <td>URL miniatura</td>
                        <td>{{ row.url_thumbnail }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
var infoFileApp = createApp({
    data(){
        return{
            row: <?= json_encode($row) ?>,
            loading: false,
        }
    },
    methods: {
        
    },
    mounted(){
        //this.getList()
    }
}).mount('#infoFileApp')
</script>