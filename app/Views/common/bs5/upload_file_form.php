<form accept-charset="utf-8" method="POST" id="fileForm" @submit.prevent="handleSubmit">
    <fieldset v-bind:disabled="loading">
        <div class="form-group row">
            <div class="col-md-8">
                <input id="field-file" type="file" ref="file_field" name="file_field" required class="form-control" v-on:change="handleFileUpload()">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100" type="submit">Cargar</button>
            </div>
        </div>
    </fieldset>
</form>
<div id="uploadResponse"></div>