<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px"><input type="checkbox" @change="selectAll" v-model="allSelected"></th>
            <th width="10px" class="table-warning">ID</th>
            <th width="60px"></th>
            <th>Nombre</th>
            <th></th>
            <th width="100px"></th>
        </thead>

        <tbody>
            <tr v-for="(element, key) in results" v-bind:id="`row` + element.id" v-bind:class="{'row-selected': selected.includes(element.id) }">
                <td><input type="checkbox" v-model="selected" v-bind:value="element.id"></td>
                <td class="table-warning">{{ element.id }}</td>
                <td>
                    <a v-bind:href="element.url" data-lightbox="image-1" v-if="element.is_image == 1">
                        <img
                            v-bind:src="element.url_thumbnail"
                            class="rounded sqr-60"
                            v-bind:alt="element.title"
                            onerror="this.src='<?= URL_IMG ?>app/nd.png'"
                        >
                    </a>
                    <img
                        v-else v-bind:src="`<?= URL_IMG ?>file_types/file-`+ element.ext +`.png`"
                        class="rounded sqr-60"
                        v-bind:alt="element.title"
                        onerror="this.src='<?= URL_IMG ?>app/nd.png'"
                    >
                </td>
                <td>
                    <a v-bind:href="`<?= URL_ADMIN . 'files/index/' ?>` + element.id">
                        {{ element.title }}
                    </a>
                    <br>
                    <small class="text-muted">{{ element.ext }}</small>
                </td>
                <td>
                    <a v-bind:href="element.url" class="btn btn-sm btn-light me-1" target="_blank">Abrir</a>
                    <a v-bind:href="element.url_thumbnail" class="btn btn-sm btn-light" target="_blank" v-show="element.is_image == 1">Mini</a>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>