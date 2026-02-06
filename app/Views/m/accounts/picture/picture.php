<?= view('assets/lightbox2') ?>

<div id="userPictureApp">
    <div class="card center_box_750 mb-2" v-show="user.image_id == 0">
        <div class="card-body">
            <?= view('common/bs5/upload_file_form') ?>
        </div>
    </div>
    <div class="text-center" v-show="user.image_id > 0">
        <div class="card center_box_450">
            <div class="card-body">
                <div v-show="user.image_id > 0">
                    <div class="d-flex justify-content-between">
                        <!-- <a class="btn btn-light me-2" id="btn_crop" href="<?= URL_APP . "accounts/edit/cropping" ?>">
                            <i class="fa fa-crop"></i> Recortar
                        </a> -->
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalSingleDelete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <img
                v-bind:src="user.url_image" class="card-img-bottom" alt="user picture"
                onerror="this.src='<?= URL_IMG ?>users/user.png'"
            >
        </div>
    </div>
    <?= view('common/bs5/modal_single_delete') ?>
</div>

<?= view('m/accounts/picture/vue') ?>