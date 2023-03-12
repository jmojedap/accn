<div class="modal fade" id="modalDeleteSelected" tabindex="-1" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-center">¿Confirmas la eliminación?</p>
                <div class="d-flex flex-column" v-show="!deleting">
                    <button type="button" class="btn btn-outline-danger btn-sm mb-2" v-on:click="deleteSelected" data-dismiss="modal" v-bind:disabled="deleting">
                        <span ><i class="fa fa-spin fa-spinner" v-show="deleting"></i></span>
                        <span v-show="!deleting">Eliminar {{ selected.length }} {{ entityInfo.plural.toLowerCase() }}</span>
                    </button>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>