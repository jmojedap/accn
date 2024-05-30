<div id="valuesApp">
    <div class="row">
        <div class="col-md-4">
            <div class="row mb-3">
                <label for="scope" class="form-label col-md-4 col-form-label text-end">Ámbito</label>
                <div class="col-md-8">
                    <select name="fe2" v-model="filters.fe2" class="form-select" v-on:change="getCategories">
                        <option v-for="optionScope in scopes" v-bind:value="optionScope.id">{{ optionScope.name }}</option>
                    </select>
                </div>
            </div>
            
            <table class="table bg-white">
                <thead>
                    <th width="50px">ID</th>
                    <th>Variable</th>
                    <th width="10px"></th>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input
                                name="q" type="text" class="form-control" v-on:change="getCategories"
                                title="Categoría" placeholder="Filtrar..."
                                v-model="filters.q"
                            >    
                        </td>
                        <td>
                            <button class="a4" type="button" v-on:click="clearFilters" v-show="filters.q.length > 0">
                                    <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-for="(category, key) in categories"
                        v-bind:class="{'table-warning': categoryKey == key}">
                        <td><small class="text-muted">{{ category.code }}</small></td>
                        <td>{{ category.name }}</td>
                        <td>
                            <button class="a4" v-on:click="setCategory(category.code)">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
        <div class="col col-md-8">
            <div class="mb-2">
                
            </div>
            <div class="card mb-2" v-bind:style="`margin-top: ` + marginValue + `px;`">
                <div class="card-body text-center">
                    <h3>{{ currCategory.name }} <span class="badge bg-primary">{{ list.length }}</span></h3>
                    <p class="lead">{{ currCategory.description }}</p>
                </div>
                <table class="table table-condensed bg-white">
                    <thead>
                        <th width="50px">Num</th>
                        <th v-show="displayFormat == 'article'">
                            Elemento
                        </th>
                        <th width="50px" title="Abreviatura" v-show="displayFormat == 'table'">Abr.</th>
                        <th v-show="displayFormat == 'table'">Nombre</th>
                        <th data-bs-toggle="tooltip" data-bs-placement="top" title="Nombre corto, para uso en tableros de visualización" v-show="displayFormat == 'table'">
                            Corto
                        </th>
                        <th width="40px" v-show="displayFormat == 'table'"></th>
                        <th width="90px" v-show="appRid <= 3" v-show="displayFormat == 'table'">
                            <button class="a4" v-on:click="clearForm" data-bs-toggle="modal" data-bs-target="#modalItemForm">
                                <i class="fa fa-plus"></i>
                            </button>
                        </th>
                    </thead>
                    <tbody>
                        <tr v-for="(row, key) in list" v-bind:class="{'table-warning':rowId == row.id}">
                            <td class="text-center table-warning">{{ row.code }}</td>
                            <td v-show="displayFormat == 'article'">
                                <p>
                                    <b>{{ row.name }}</b>
                                </p>
                                <p>
                                    {{ row.description }}
                                </p>
                                <p>
                                    <span class="text-muted">Abreviatura: </span>
                                    {{ row.abbreviation }}
                                    &middot;
                                    <span class="text-muted">Nombre completo: </span>
                                    {{ row.long_name }}
                                    &middot;
                                    <span class="text-muted">Nombre corto: </span>
                                    {{ row.short_name }}
                                </p>
                            </td>
                            <td class="text-center" v-show="displayFormat == 'table'"><span class="text-muted">{{ row.abbreviation }}</span></td>
                            <td class="" v-show="displayFormat == 'table'">
                                <span class="">
                                    {{ row.name }}
                                </span>
                            </td>
                            <td v-bind:class="{'table-danger': row.short_name.length > 25 }" v-show="displayFormat == 'table'">{{ row.short_name }}</td>
                            <td v-show="displayFormat == 'table'">
                                <button class="a4" data-bs-toggle="modal" data-bs-target="#detailsModal" v-on:click="setCurrent(key)">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                            </td>
                            <td v-show="appRid <= 3" v-show="displayFormat == 'table'">
                                <button class="a4 me-1" v-on:click="loadFormValues(key)" data-bs-toggle="modal" data-bs-target="#modalItemForm">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                                <button v-show="appRid <= 3" class="a4" data-bs-toggle="modal" data-bs-target="#modalSingleDelete" v-on:click="setCurrent(key)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?= view('admin/items/values/details'); ?>
    <?= view('admin/items/values/modal_form'); ?>
    <?= view('common/bs5/modal_single_delete'); ?>
</div>

<?= view('admin/items/values/vue'); ?>