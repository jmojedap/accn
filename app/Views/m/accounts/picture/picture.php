<?= view('assets/lightbox2') ?>

<div id="userPictureApp">
    <div class="card center_box_750 mb-2" v-show="user.image_id == 0">
        <div class="card-body">
            <?= view('common/bs5/upload_file_form') ?>
        </div>
    </div>
    <div class="text-center" v-show="user.image_id > 0">
        <a v-bind:href="user.url_image" data-lightbox="image-1" data-title="">
            <img v-bind:src="user.url_image" class="sqr-180 cursor-pointer">
        </a>
    </div>
    <?= view('common/bs5/modal_single_delete') ?>
</div>

<?= view('m/accounts/picture/vue') ?>