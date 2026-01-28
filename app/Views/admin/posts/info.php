<div id="infoApp">
    <div class="center_box_750">
        <table class="table bg-white">
            <tbody>
                <tr>
                    <td>Nombre</td>
                    <td>{{ post.title }}</td>
                </tr>
                <tr>
                    <td>Slug</td>
                    <td>{{ post.slug }}</td>
                </tr>
                <tr>
                    <td>Contenido</td>
                    <td>
                        {{ post.content }}
                    </td>
                </tr>

                <tr>
                    <td>Actualizado</td>
                    <td>{{ dateFormat(post.updated_at) }} &middot;
                        {{ dateAgo(post.updated_at) }}
                    </td>
                </tr>
                <tr>
                    <td>Creado</td>
                    <td>{{ dateFormat(post.created_at) }} &middot;
                        {{ dateAgo(post.created_at) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<script>
var infoApp = createApp({
    data(){
        return{
            loading: true,
            post: <?= json_encode($post) ?>,
            arrTypes: ItemsApp.arrayCategory(33),
        }
    },
    methods: {
        typeName: function(value = '', field = 'name'){
            var typeName = ''
            var item = this.arrTypes.find(row => row.code == value)
            if ( item != undefined ) typeName = item[field]
            return typeName
        },
        dateAgo: function(date) {
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()
        },
        dateFormat: function (date) {
            if (!date) return ''
            return moment(date).format('D MMM YYYY')
        },
    },
}).mount('#infoApp')
</script>