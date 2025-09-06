<?= view('assets/lightbox2') ?>
<div id="infoFileApp">
    <div class="row center_box_920">
        <div class="col-md-4">
            <a v-bind:href="row.url" data-lightbox="image-1">
                <img
                    v-bind:src="row.url"
                    class="rounded w-100 mb-2"
                    v-bind:alt="row.title"
                    onerror="this.src='<?= URL_IMG ?>app/nd.png'"
                >
            </a>
        </div>
        <div class="col-md-8">
            <table class="table bg-white">
                <tbody>
                    <tr>
                        <td class="td-title">Título</td>
                        <td width="80%">{{ row.title }}</td>
                    </tr>
                    <tr>
                        <td class="td-title">Descripción</td>
                        <td>{{ row.description }}</td>
                    </tr>
                    <tr>
                        <td class="td-title">URL</td>
                        <td class="text-break">{{ row.url }}</td>
                    </tr>
                    <tr v-show="row.is_image == 1">
                        <td class="td-title">URL miniatura</td>
                        <td class="text-break">{{ row.url_thumbnail }}</td>
                    </tr>
                    <tr>
                        <td class="td-title">Actualizado</td>
                        <td class="text-break">{{ dateFormat(row.updated_at) }} &middot; {{ ago(row.updated_at) }}</td>
                    </tr>
                    <tr>
                        <td class="td-title">Creado</td>
                        <td class="text-break">{{ dateFormat(row.created_at) }} &middot; {{ ago(row.created_at) }}</td>
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
        ago: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()            
        },
        dateFormat: function(date){
            if (!date) return ''
            return moment(date).format('D MMM YYYY')
        },
    },
}).mount('#infoFileApp')
</script>