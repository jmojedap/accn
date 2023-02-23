<div id="editUser">
    <div class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="editForm" @submit.prevent="handleSubmit">
                <input name="id" type="hidden" value="<?= $row->id ?>">
                <fieldset v-bind:disabled="loading">
                    <div class="mb-2 row">
                        <label for="role" class="col-md-4 col-form-label text-end">Rol</label>
                        <div class="col-md-8">
                            <select name="role" v-model="fields.role" class="form-select">
                                <option v-for="optionRole in arrRoles" v-bind:value="optionRole.code">
                                    {{ optionRole.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="first_name" class="col-md-4 col-form-label text-end">Nombres</label>
                        <div class="col-md-4">
                            <input name="first_name" type="text" class="form-control" required title="Nombres"
                                placeholder="Nombres" v-model="fields.first_name">
                        </div>
                        <div class="col-md-4">
                            <input name="last_name" type="text" class="form-control" required title="Apellidos"
                                placeholder="Apellidos" v-model="fields.last_name">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="username" class="col-md-4 col-form-label text-end">Username</label>
                        <div class="col-md-8">
                            <input
                                name="username" type="text" class="form-control" required pattern="^[A-Za-z0-9_.]{6,25}$"
                                v-bind:class="{ 'is-invalid': validation.usernameUnique == 0 }"
                                v-model="fields.username" v-on:change="validateForm"
                            >
                            <span class="invalid-feedback">
                                El valor escrito es usado por otro usuario
                            </span>
                        </div>
                    </div>

                    <div class="mb-2 row">
                        <label for="email" class="col-md-4 col-form-label text-end">Correo electrónico</label>
                        <div class="col-md-8">
                            <input name="email" type="email" class="form-control" required
                                v-bind:class="{ 'is-invalid': validation.emailUnique == 0 }" v-model="fields.email"
                                v-on:change="validateForm">
                            <span class="invalid-feedback">
                                El valor escrito ya fue registrado para otro usuario
                            </span>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="gender" class="col-md-4 col-form-label text-end">Sexo</label>
                        <div class="col-md-8">
                            <select name="gender" v-model="fields.gender" class="form-select form-control" required>
                                <option v-for="optionGender in arrGenders" v-bind:value="optionGender.code">{{ optionGender.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="birth_date" class="col-md-4 col-form-label text-end">Fecha de nacimiento</label>
                        <div class="col-md-8">
                            <input
                                name="birth_date" type="date" class="form-control"
                                v-model="fields.birth_date"
                            >
                        </div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-md-8 offset-md-4">
                            <button class="btn btn-success w120p me-2" type="submit">Guardar</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<?= view('admin/users/edit/vue'); ?>