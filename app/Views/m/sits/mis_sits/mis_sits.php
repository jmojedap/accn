<div id="misSitsApp">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Mis Sits</h1>
            </div>
        </div>
    </div>

    <div v-if="section == 'list'">
        <div class="container">
            <div class="row mb-2">
                <div class="col-12">
                    <a :href="url_mod + 'sits/add'" class="btn btn-primary w120p">
                        <i class="fa fa-plus"></i> Crear
                    </a>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th width="250">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sit in sits">
                        <td>{{ sit.title }}</td>
                        <td>
                            <a :href="url_mod + 'sits/sit/' + sit.id + '/' + sit.slug" class="btn btn-light me-1">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a :href="url_mod + 'sits/edit/' + sit.id" class="btn btn-light me-1">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a :href="url_mod + 'sits/delete/' + sit.id" class="btn btn-light me-1">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        
    </div>
</div>

<?= view('m/sits/mis_sits/vue'); ?>
