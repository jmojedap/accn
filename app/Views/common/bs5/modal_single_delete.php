<div class="modal" tabindex="-1" role="dialog" id="modalSingleDeleteNo">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Confirma que desea eliminar este elemento?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" v-on:click="deleteElement" data-dismiss="modal">
                    Eliminar
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSingleDelete" tabindex="-1" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-center">¿Confirmas la eliminación?</p>
                <div class="d-flex flex-column" v-show="!deleting">
                    <button type="button" class="btn btn-outline-danger btn-sm mb-2" v-on:click="deleteElement" data-dismiss="modal" v-bind:disabled="deleting">
                        <span ><i class="fa fa-spin fa-spinner" v-show="deleting"></i></span>
                        <span v-show="!deleting">Eliminar {{ entityInfo.singular.toLowerCase() }}</span>
                    </button>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>