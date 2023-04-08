<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px"><input type="checkbox" @change="selectAll" v-model="allSelected"></th>
            <th width="10px" class="table-warning">ID</th>
            <th width="60px"></th>
            <th>Nombre</th>
            <th width="100px"></th>
        </thead>

        <tbody>
            <tr v-for="(element, key) in results" v-bind:id="`row` + element.id" v-bind:class="{'row-selected': selected.includes(element.id) }">
                <td><input type="checkbox" v-model="selected" v-bind:value="element.id"></td>
                <td class="table-warning">{{ element.id }}</td>
                <td>
                    <img
                        v-bind:src="element.url_thumbnail"
                        class="rounded sqr-60"
                        v-bind:alt="element.title"
                        onerror="this.src='<?= URL_IMG ?>app/nd.png'"
                    >
                </td>
                <td>
                    <a v-bind:href="`<?= URL_ADMIN . 'files/index/' ?>` + element.id">
                        {{ element.title }}
                    </a>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>