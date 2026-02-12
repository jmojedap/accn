<?php
    /**
     * Se construye la información de cada campo para crear el formulario de edición
     * de forma automática.
     */
    $arrFields = [];
    foreach ( $fieldsMeta as $field ) {
        $fieldPlus['name'] = $field->name;
        $fieldPlus['type'] = $field->type;
        $fieldPlus['value'] = $row->{$field->name};
        $fieldPlus['tag'] = 'input';
        $fieldPlus['rows'] = 1;
        if ( in_array($field->type, ['text', 'mediumtext', 'longtext']) ) {
            $fieldPlus['tag'] = 'textarea';
            $fieldPlus['rows'] = 5;
        }

        if ( in_array($field->name, ['id', 'idcode', 'created_at', 'updated_at', 'deleted_at']) ) continue;
        $arrFields[] = $fieldPlus;
    }
?>

<div id="editRowApp">
    <div class="center_box_920 mb-2 text-end">
        <button class="btn btn-light w120p btn-sm" @click="section = 'form'" v-show="section == 'table'">
            Editar
        </button>
        <button class="btn btn-secondary w120p btn-sm" @click="section = 'table'" v-show="section == 'form'">
            Cancelar
        </button>
    </div>
    <div v-show="section == 'table'">
        <?= view('common/bs5/row_details') ?>
    </div>
    <div class="center_box_920" v-show="section == 'form'">
        <div class="card">
            <div class="card-body">
                <form accept-charset="utf-8" method="POST" id="formRowEdit" @submit.prevent="handleSubmit">
                    <fieldset v-bind:disabled="loading">
                        <div class="mb-1 row">
                            <div class="col-md-9 offset-md-3">
                                <button class="btn btn-primary w120p" type="submit">Guardar</button>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?= $row->id ?>">

                        <?php foreach ( $arrFields as $fieldValue ) : ?>
                            <div class="mb-1 row">
                                <label for="<?= $fieldValue['name'] ?>" class="col-md-3 col-form-label text-end"><?= str_replace('_', ' ', $fieldValue['name']) ?></label>
                                <div class="col-md-9">
                                    <?php if ( $fieldValue['tag'] == 'textarea' ) : ?>
                                        <textarea class="form-control form-control-sm" name="<?= $fieldValue['name'] ?>" rows="<?= $fieldValue['rows'] ?>"><?= $fieldValue['value'] ?></textarea>
                                    <?php else : ?>
                                        <input type="text" class="form-control form-control-sm" name="<?= $fieldValue['name'] ?>" value="<?= $fieldValue['value'] ?>">
                                    <?php endif; ?> 
                                </div>
                            </div>
                        <?php endforeach ?>
                
                        <div class="mb-1 row">
                            <div class="col-md-9 offset-md-3">
                                <button class="btn btn-primary w120p" type="submit">Guardar</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>    
</div>

<script>
var editRowApp = createApp({
    data(){
        return{
            section: 'table',
            loading: false,
            formAction: '<?= $formAction ?>',
        }
    },
    methods: {
        handleSubmit: function() {
            this.loading = true
            var formValues = new FormData(document.getElementById('formRowEdit'))
            axios.post(this.formAction, formValues)
            .then(response => {
                if ( response.data.saved ) {
                    toastr['success']('Guardado')
                }
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
    }
}).mount('#editRowApp')
</script>