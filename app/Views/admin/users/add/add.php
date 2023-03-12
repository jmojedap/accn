<div id="addUser">
    <div class="card center_box_750">
        <div class="card-body">
            <form accept-charset="utf-8" method="POST" id="addForm" @submit.prevent="handleSubmit">
                <fieldset v-bind:disabled="loading">
                    <div class="mb-2 row">
                        <label for="role" class="col-md-4 col-form-label text-end">Rol</label>
                        <div class="col-md-8">
                            <select name="role" v-model="user.role" class="form-select">
                                <option v-for="optionRole in arrRoles" v-bind:value="optionRole.code">
                                    {{ optionRole.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="first_name" class="col-md-4 col-form-label text-end">Nombres</label>
                        <div class="col-md-4">
                            <input name="first_name" type="text" class="form-control" required title="Nombres"
                                placeholder="Nombres" v-model="user.first_name">
                        </div>
                        <div class="col-md-4">
                            <input name="last_name" type="text" class="form-control" required title="Apellidos"
                                placeholder="Apellidos" v-model="user.last_name">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="email" class="col-md-4 col-form-label text-end">Correo electrónico</label>
                        <div class="col-md-8">
                            <input name="email" type="email" class="form-control" required
                                v-bind:class="{ 'is-invalid': validation.emailUnique == 0 }" v-model="user.email"
                                v-on:change="validateForm">
                            <span class="invalid-feedback">
                                El valor escrito ya fue registrado para otro usuario
                            </span>
                        </div>
                    </div>

                    <div class="mb-2 row">
                        <label class="col-md-4 col-form-label text-end" for="password">Contraseña</label>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input name="password" v-bind:type="hidePassword ? 'password' : 'text'"
                                    class="form-control" placeholder="Nueva contraseña para el usuario" required
                                    pattern="(?=.*\d)(?=.*[a-z]).{8,}"
                                    title="8 caractéres o más, al menos un número y una letra minúscula"
                                    v-model="user.password">
                                <button class="btn btn-light" type="button" v-on:click="hidePassword = !hidePassword">
                                    <i class="far fa-eye-slash" v-show="!hidePassword"></i>
                                    <i class="far fa-eye" v-show="hidePassword"></i>
                                </button>
                            </div>
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

    <!-- Modal -->
    <div class="modal fade" id="createdModal" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="text-center">
                        <i class="fa fa-check-circle text-success fa-2x"></i>
                    </p>
                    <div class="d-flex flex-column">
                        <button type="button" class="btn btn-primary btn-sm mb-2" v-on:click="goToCreated">
                            Abrir {{ entityInfo.singular.toLowerCase() }}
                        </button>
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Crear otro</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/users/add/vue'); ?>