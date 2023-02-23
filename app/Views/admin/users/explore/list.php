<p>
    <strong>{{ qtyResults }}</strong> resultados
</p>
<div class="table-responsive bg-white">
    <table class="table">
        <thead>
            <th width="10px"><input type="checkbox" @change="selectAll" v-model="allSelected"></th>
            <th width="10px">ID</th>
            <th>Nombre</th>
            <th>E-mail</th>
            <th width="100px"></th>
        </thead>

        <tbody>
            <tr v-for="(element, key) in list" v-bind:id="`row` + element.idcode" v-bind:class="{'selected': selected.includes(element.idcode) }">
                <td><input type="checkbox" v-model="selected" v-bind:value="element.idcode"></td>
                <td class="text-muted">{{ element.id }}</td>
                <td>
                    <a v-bind:href="`<?= URL_ADMIN . 'users/edit/' ?>` + element.idcode">
                        {{ element.display_name }}
                    </a>
                </td>
                <td>{{ element.email }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>