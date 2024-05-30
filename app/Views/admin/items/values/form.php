<?php
    $options_parent = array();
?>

<form accept-charset="utf-8" id="itemForm" @submit.prevent="handleSubmit">
    <input name="category_id" type="hidden" v-model="currCategory.code">

    <div class="mb-3 row">
        <div class="col-sm-8 offset-4">
            <button class="btn w120p" v-bind:class="appState.buttonClass" type="submit">
                {{ appState.buttonText }}
            </button>
        </div>
    </div>

    <div class="mb-3 row">
        <label for="cod" class="col-md-4 col-form-label text-end">
            Código numérico
        </label>
        <div class="col-md-8">
            <input
                name="code"
                class="form-control"
                placeholder="Código numérico"
                title="Código numérico"
                required
                v-model="fields.code"
                >
        </div>
    </div>

    <div class="mb-3 row">
        <label for="name" class="col-md-4 col-form-label text-end">
            <span class="">Nombre</span>
        </label>
        <div class="col-md-8">
            <input
                name="name" class="form-control" title="Nombre del parámetro"
                required
                v-model="fields.name" v-on:change="autocomplete"
                >
        </div>
    </div>

    <div class="mb-3 row">
        <label for="description" class="col-md-4 col-form-label text-end">Descripción</label>
        <div class="col-md-8">
            <textarea
                name="description" class="form-control"
                title="Descripción del ítem" required rows="3"
                v-model="fields.description"></textarea>
        </div>
    </div>

    <div class="mb-3 row">
        <label for="abbreviation" class="col-md-4 col-form-label text-end">Abreviatura</label>
        <div class="col-md-8">
            <input name="abbreviation" class="form-control"
                placeholder="" title="Abreviatura de hasta 4 caracteres"
                v-model="fields.abbreviation"
                >
        </div>
    </div>

    <div class="mb-3 row">
        <label for="short_name" class="col-md-4 col-form-label text-end">
            <span class="">Nombre corto</span>
        </label>
        <div class="col-md-8">
            <input
                name="short_name" class="form-control" placeholder=""
                title="Nombre corto" required maxlength="25"
                v-model="fields.short_name"
                >
                <div id="emailHelp" class="form-text">Para mostrarse como etiqueta en visualización de gráficos</div>

        </div>
    </div>

    <div class="mb-3 row">
        <label for="slug" class="col-md-4 col-form-label text-end">
            <span class="">Slug</span>
        </label>
        <div class="col-md-8">
            <input
                name="slug"
                class="form-control"
                placeholder=""
                title="Sin espacios y acentos"
                required
                v-model="fields.slug"

                >
        </div>
    </div>
</form>