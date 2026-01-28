<div id="infoPostApp">
    <div class="center_box_750 mb-2">
        <ul class="nav nav-pills justify-content-center">
            <li class="nav-item">
                <button type="button" class="nav-link" :class="{ active: section == 'content' }" @click="section = 'content'">
                    Contenido
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" :class="{ active: section == 'meta' }" @click="section = 'meta'">
                    Detalles
                </button>
            </li>
        </ul>
    </div>
    <div class="center_box_750">
        <div class="card" v-show="section == 'content'">
            <img v-show="post.url_image" v-bind:src="post.url_image" class="card-img-top" v-bind:alt="post.title">
            <div class="card-body p-5">
                <h2 class="mb-3 text-center">{{ post.title }}</h2>
                <p class="lead">{{ post.excerpt }}</p>
                <hr>
                <div v-html="post.content"></div>
            </div>
        </div>
        <div class="card" v-show="section == 'meta'">
            <div class="card-body p-5">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-end">Tipo</td>
                        <td>{{ itemName(post.type_id) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end">Estado</td>
                        <td>{{ post.status }}</td>
                    </tr>
                    <tr>
                        <td class="text-end">Fecha de creación</td>
                        <td>{{ post.created_at }}</td>
                    </tr>
                    <tr>
                        <td class="text-end">Fecha de actualización</td>
                        <td>{{ post.updated_at }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
var infoPostApp = createApp({
    data(){
        return{
            section: 'content',
            post: <?= json_encode($row) ?>,
            arrTypes: ItemsApp.arrayCategory(33),
        }
    },
    methods: {
        itemName: function(value = '', field = 'name'){
            var itemName = '-'
            var item = this.arrTypes.find(row => row.code == value)
            if ( item != undefined ) itemName = item[field]
            return itemName
        },
    }, 
}).mount('#infoPostApp')
</script>