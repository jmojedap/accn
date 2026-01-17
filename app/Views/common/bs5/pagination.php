<div class="d-flex justify-content-end w-100">
    <div class="py-1 me-2">
        <a class="text-muted">
            {{ paginationText }}
            <span class="text-primary">{{ qtyResults }}</span>
        </a>
    </div>
    <button class="btn-circle" v-on:click="sumPage(-1)" v-bind:disabled="settings.numPage==1">
        <i class="fa fa-chevron-left"></i>
    </button>
    <button class="btn-circle" v-on:click="sumPage(1)" v-bind:disabled="settings.numPage>=settings.maxPage">
        <i class="fa fa-chevron-right"></i>
    </button>
</div>