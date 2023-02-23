<form accept-charset="utf-8" method="POST" id="file_form" @submit.prevent="send_file_form">
    <fieldset v-bind:disabled="loading">
        <div class="form-group row">
            <div class="col-md-8">
                <input id="field-file" type="file" ref="file_field" name="file_field" required class="form-control" v-on:change="handle_file_upload()">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary btn-block" type="submit">Cargar</button>
            </div>
        </div>
    </fieldset>
</form>
<div id="upload_response"></div>