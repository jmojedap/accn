<!-- Modal -->
<div class="modal fade" id="modalFormCenter" tabindex="-1" role="dialog" aria-labelledby="modalFormCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormCenterTitle">
                    {{ currCategory.name }}:
                    {{ formConfig.title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/items/values/form'); ?>
            </div>
        </div>
    </div>
</div>