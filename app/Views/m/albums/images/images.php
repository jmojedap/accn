<?php
$publicImages = [];
foreach ($images->getResult() as $image) {
    $publicImages[] = [
        'id' => $image->id,
        'title' => $image->title,
        'url_thumbnail' => $image->url_thumbnail,
        'url' => $image->url,
    ];
}
?>

<div id="imagesAlbumApp">
    <div class="album-header">
        <a
            class="album-back-link"
            href="<?= URL_APP ?>sits/albums/<?= $sit->slug ?>"
            title="Volver a los álbumes"
        >
            <i class="fa fa-arrow-left"></i>
        </a>
        <h1 class="album-heading"><?= esc($row->title) ?></h1>
    </div>

    <div class="album-images-grid">
        <button
            v-for="(image, imageKey) in images"
            :key="image.id"
            type="button"
            class="album-image-item"
            data-bs-toggle="modal"
            data-bs-target="#albumImageModal"
            @click="setCurrentImage(imageKey)"
            @contextmenu.prevent
        >
            <img
                class="album-image-thumb"
                :src="image.url_thumbnail"
                :alt="image.title"
                draggable="false"
                @contextmenu.prevent
                @dragstart.prevent
                onerror="this.src='<?= URL_IMG ?>app/nd.png'"
            >
        </button>

        <div v-if="images.length == 0" class="album-images-empty text-center py-5">
            <i class="fa fa-image fa-3x text-muted mb-3"></i>
            <div class="h5 text-muted">No hay im&aacute;genes publicadas</div>
        </div>
    </div>

    <div class="modal fade" id="albumImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-0 border-0">
                <div class="modal-body p-0 bg-dark">
                    <img
                        v-if="currentImage.url"
                        class="album-modal-image"
                        :src="currentImage.url"
                        :alt="currentImage.title"
                        draggable="false"
                        @contextmenu.prevent
                        @dragstart.prevent
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .album-header {
        display: flex;
        align-items: center;
        max-width: 935px;
        margin: 0 auto 1rem;
        gap: 0.75rem;
    }

    .album-back-link {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        color: #212529;
        text-decoration: none;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0;
    }

    .album-back-link:hover {
        color: #000;
        background: #e9ecef;
    }

    .album-heading {
        min-width: 0;
        margin: 0;
        overflow: hidden;
        color: #212529;
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.2;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .album-images-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3px;
        max-width: 935px;
        margin: 0 auto;
    }

    .album-image-item {
        display: block;
        width: 100%;
        padding: 0;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        background: #f8f9fa;
        border: 0;
        border-radius: 0;
        cursor: pointer;
    }

    .album-image-thumb {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease, filter 0.2s ease;
        user-select: none;
        -webkit-user-drag: none;
    }

    .album-image-item:hover .album-image-thumb {
        filter: brightness(0.88);
        transform: scale(1.03);
    }

    .album-images-empty {
        grid-column: 1 / -1;
    }

    .album-modal-image {
        display: block;
        width: 100%;
        max-height: 85vh;
        object-fit: contain;
        user-select: none;
        -webkit-user-drag: none;
    }
</style>

<script>
var imagesAlbumApp = createApp({
    data(){
        return{
            loading: false,
            images: <?= json_encode($publicImages) ?>,
            currentImageKey: null,
            currentImage: {}
        }
    },
    methods: {
        setCurrentImage: function(imageKey){
            this.currentImageKey = imageKey
            this.currentImage = this.images[imageKey]
        },
        showNextImage: function(){
            if (this.images.length == 0 || this.currentImageKey === null) return

            const nextKey = (this.currentImageKey + 1) % this.images.length
            this.setCurrentImage(nextKey)
        },
        showPreviousImage: function(){
            if (this.images.length == 0 || this.currentImageKey === null) return

            const previousKey = (this.currentImageKey - 1 + this.images.length) % this.images.length
            this.setCurrentImage(previousKey)
        },
        handleKeydown: function(event){
            if (!this.currentImage.url_thumbnail) return

            if (event.key == 'ArrowRight') {
                this.showNextImage()
            }

            if (event.key == 'ArrowLeft') {
                this.showPreviousImage()
            }
        }
    },
    mounted(){
        window.addEventListener('keydown', this.handleKeydown)
    },
    beforeUnmount(){
        window.removeEventListener('keydown', this.handleKeydown)
    }
}).mount('#imagesAlbumApp')
</script>
