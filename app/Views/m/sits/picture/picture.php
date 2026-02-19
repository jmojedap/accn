<?= view('assets/lightbox2') ?>

<?php if ( $section == 'cropping' ) : ?>
    <?= view('common/bs5/cropping'); ?>
<?php endif; ?>

<?php if ( $section == 'form' ) : ?>
    <div id="userPictureApp">
        <div class="card center_box_750 mb-2" v-show="row.image_id == 0">
            <div class="card-body">
                <?= view('common/bs5/upload_file_form') ?>
            </div>
        </div>
        <div class="text-center" v-show="row.image_id > 0">
            <div class="card center_box_450">
                <div class="card-body">
                    <div v-show="row.image_id > 0">
                        <div class="d-flex justify-content-between">
                            <a v-bind:href="'<?= URL_APP ?>sits/picture/cropping'" class="btn btn-light me-2">
                                <i class="fa fa-crop"></i> Recortar
                            </a>
                            <a class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalSingleDelete">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <img
                    v-bind:src="row.url_image" class="card-img-bottom" alt="Imagen del Sit"
                    onerror="this.src='<?= URL_IMG ?>app/sits-default.png'"
                >
            </div>
        </div>
        <?= view('common/bs5/modal_single_delete') ?>
    </div>
    <?= view('m/sits/picture/vue') ?>
<?php endif; ?>