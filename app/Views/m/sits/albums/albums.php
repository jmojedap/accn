<div id="albumsApp">
    <div class="album-list">
        <a
            v-for="album in albums"
            :key="album.id"
            class="album-item"
            :href="'<?= URL_APP ?>albums/images/' + album.id"
        >
            <img
                :src="album.url_image"
                class="album-thumb"
                :alt="album.title"
                onerror="this.src='<?= URL_IMG ?>app/nd.png'"
            >
            <div class="album-title">{{ album.title }}</div>
        </a>

        <div v-if="albums.length == 0" class="text-center py-5">
            <i class="fa fa-image fa-3x text-muted mb-3"></i>
            <div class="h5 text-muted">No hay &aacute;lbumes publicados</div>
        </div>
    </div>
</div>

<style>
    .album-list {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .album-item {
        display: flex;
        flex-direction: column;
        color: inherit;
        text-decoration: none;
        background: #fff;
        border-radius: 0;
        transition: all 0.2s ease-in-out;
    }

    .album-item:hover {
        color: inherit;
        transform: translateY(-2px);
    }

    .album-thumb {
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        background-color: #f8f9fa;
        border-radius: 0;
    }

    .album-title {
        overflow: hidden;
        padding-top: 0.5rem;
        color: #212529;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.3;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<script>
var albumsApp = createApp({
    data(){
        return{
            loading: false,
            albums: <?= json_encode($albums) ?>
        }
    },
    methods: {
        
    },
    mounted(){
        //this.getList()
    }
}).mount('#albumsApp')
</script>
