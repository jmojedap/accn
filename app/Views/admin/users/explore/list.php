<div class="table-responsive">
    <table class="table bg-white">
        <thead>
            <th width="10px"><input type="checkbox" @change="selectAll" v-model="allSelected"></th>
            <th width="10px" class="table-warning">ID</th>
            <th>Nombre</th>
            <th>Rol</th>
            <th width="100px"></th>
        </thead>

        <tbody>
            <tr v-for="(element, key) in results" v-bind:id="`row` + element.idcode" v-bind:class="{'row-selected': selected.includes(element.idcode) }">
                <td><input type="checkbox" v-model="selected" v-bind:value="element.idcode"></td>
                <td class="table-warning">{{ element.id }}</td>
                <td>
                    <a v-bind:href="`<?= URL_ADMIN . 'users/edit/' ?>` + element.idcode">
                        {{ element.display_name }}
                    </a>
                    <br>
                    <span class="text-muted">{{ element.email }}</span>
                </td>
                <td>{{ roleName(element.role) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>