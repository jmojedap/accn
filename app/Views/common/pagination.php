<div class="input-group w120p">
    <div class="input-group-prepend">
        <button class="btn btn-light" v-on:click="sum_page(-1)" title="Página anterior">
            <i class="fa fa-caret-left"></i>
        </button>
    </div>
    <input
        name="num_page" class="form-control" type="number" min="1"
        v-bind:max="max_page"
        v-model="num_page"
        v-on:change="sum_page(0)"
        v-bind:title="`${max_page} páginas en total`"
        >
    <div class="input-group-append">
        <button class="btn btn-light" v-on:click="sum_page(1)" title="Página siguiente">
            <i class="fa fa-caret-right"></i>
        </button>
    </div>
</div>