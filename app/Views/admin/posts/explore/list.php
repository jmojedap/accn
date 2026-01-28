<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px"><input type="checkbox" @change="selectAll" v-model="allSelected"></th>
            <th width="10px" class="table-warning">ID</th>
            <th>Titulo</th>
            <th>Tipo</th>
            <th width="100px"></th>
        </thead>

        <tbody>
            <tr v-for="(element, key) in results" v-bind:id="`row` + element.idcode" v-bind:class="{'row-selected': selected.includes(element.idcode) }">
                <td><input type="checkbox" v-model="selected" v-bind:value="element.idcode"></td>
                <td class="table-warning">{{ element.id }}</td>
                <td>
                    <a v-bind:href="`<?= URL_ADMIN . 'posts/info/' ?>` + element.id">
                        {{ element.title }}
                    </a>
                    </td>
                <td><span v-bind:class="`item-type item-type-` + element.type_id"></span></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>