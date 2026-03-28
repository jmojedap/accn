<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<?= view('assets/lightbox2') ?>
<?= view('m/albums/edit_images/style') ?>

<div id="postImages">
    <div class="card center_box_750 mb-4" v-show="images.length < 50">
        <div class="card-body">
            <?= view('common/bs5/upload_file_form') ?>
        </div>
    </div>
    <div class="text-center my-3">
        <h5 class="text-muted"><strong class="text-primary">{{ images.length }}</strong> imágenes en el álbum</h5>
    </div>
    <div class="text-center my-4" v-show="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="image_gallery" class="d-flex flex-wrap justify-content-center gap-3">
        <div class="card" v-for="(image, imageKey) in images" :key="image.id" :data-id="image.id">
            <div class="image-container">
                <div class="main-image-indicator" v-show="image.is_main == 1">
                    <i class="fas fa-star mr-1"></i> Principal
                </div>
                
                <!-- Dropdown de Opciones -->
                <div class="dropdown-options dropdown">
                    <button class="btn-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item" @click="setMainImage(imageKey)">
                                <i class="fas fa-check-circle text-primary"></i> Establecer Principal
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" :href="`<?= URL_ADMIN . 'files/edit/' ?>` + image.id" target="_blank">
                                <i class="fas fa-pencil-alt"></i> Editar detalles
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger" @click="setCurrent(imageKey)" data-bs-toggle="modal" data-bs-target="#modalSingleDelete">
                                <i class="fas fa-trash"></i> Eliminar imagen
                            </button>
                        </li>
                    </ul>
                </div>

                <a :href="image.url" data-lightbox="image-1">
                    <img class="sqr-180" :alt="image.title" :src="image.url_thumbnail">
                </a>
            </div>
        </div>
    </div>
    <?= view('common/bs5/modal_single_delete') ?>
</div>

<?= view('m/albums/edit_images/vue') ?>