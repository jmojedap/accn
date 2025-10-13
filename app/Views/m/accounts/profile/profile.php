<div id="userProfileApp">
    <div class="center_box_750">
        <div class="text-end mb-2">
            <button class="btn btn-light btn-sm w100p" v-on:click="setSection('form')" v-show="section == 'info'">
                Editar
            </button>
            <button class="btn btn-light btn-sm w100p" v-on:click="setSection('info')" v-show="section == 'form'">
                Cancelar
            </button>
        </div>

        <!-- INFORMACIÓN -->
        <table class="table" v-show="section == 'info'">
            <tbody>
                <tr>
                    <td class="td-title">Nombre y apellidos</td>
                    <td>
                        {{ user.first_name }}
                        {{ user.last_name }}
                    </td>
                </tr>
                <tr>
                    <td class="td-title">Mostrar como</td>
                    <td>{{ user.display_name }}</td>
                </tr>
                <tr>
                    <td class="td-title">Username</td>
                    <td>{{ user.username }}</td>
                </tr>
                <tr>
                    <td class="td-title">Correo electrónico</td>
                    <td>{{ user.email }}</td>
                </tr>
                <tr>
                    <td class="td-title">Sexo</td>
                    <td>{{ genderName(user.gender) }}</td>
                </tr>
                <tr>
                    <td class="td-title">Fecha de nacimiento</td>
                    <td>{{ user.birth_date }}</td>
                </tr>
            </tbody>
        </table>

        <!-- FORMULARIO -->
        <div v-show="section == 'form'">
            <?= view('m/accounts/profile/form') ?>
        </div>
    </div>
</div>

<?= view('m/accounts/profile/vue'); ?>