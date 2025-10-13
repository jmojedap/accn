<?php
    $options_parent = array();
?>

<form accept-charset="utf-8" id="itemForm" @submit.prevent="handleSubmit">
    <input name="category_id" type="hidden" v-model="currCategory.code">

    <div class="mb-1 row">
        <div class="col-sm-8 offset-4">
            <button class="btn w120p" v-bind:class="appState.buttonClass" type="submit">
                {{ appState.buttonText }}
            </button>
        </div>
    </div>

    <div class="mb-1 row">
        <label for="cod" class="col-md-4 col-form-label text-end">
            Código numérico
        </label>
        <div class="col-md-8">
            <input
                name="code" class="form-control" type="number" min="0"
                placeholder="Código numérico" title="Código numérico"
                required v-model="fields.code"
                >
        </div>
    </div>

    <div class="mb-1 row">
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

    <div class="mb-1 row">
        <label for="description" class="col-md-4 col-form-label text-end">Descripción</label>
        <div class="col-md-8">
            <textarea
                name="description" class="form-control"
                title="Descripción del ítem" required rows="3"
                v-model="fields.description"></textarea>
        </div>
    </div>

    <div class="mb-1 row">
        <label for="abbreviation" class="col-md-4 col-form-label text-end">Abreviatura</label>
        <div class="col-md-8">
            <input name="abbreviation" class="form-control"
                placeholder="" title="Abreviatura de hasta 4 caracteres"
                v-model="fields.abbreviation"
                >
        </div>
    </div>

    <div class="mb-1 row">
        <label for="position" class="col-md-4 col-form-label text-end">Orden</label>
        <div class="col-md-8">
            <input name="position" class="form-control" type="number" min="0"
                v-model="fields.position"
                >
        </div>
    </div>

    <div class="mb-1 row">
        <label for="short_name" class="col-md-4 col-form-label text-end">
            <span class="">Nombre corto</span>
        </label>
        <div class="col-md-8">
            <input
                name="short_name" class="form-control" placeholder=""
                title="Nombre corto" required maxlength="25"
                v-model="fields.short_name"
                >
                <div id="shortNameHelp" class="form-text">Para mostrarse como etiqueta en visualización de gráficos</div>

        </div>
    </div>

    <div class="mb-1 row">
        <label for="long_name" class="col-md-4 col-form-label text-end">
            <span class="">Nombre largo</span>
        </label>
        <div class="col-md-8">
            <input
                name="long_name" class="form-control" placeholder=""
                title="Nombre largo" required maxlength="255"
                v-model="fields.long_name"
                >
        </div>
    </div>

    <div class="mb-1 row">
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

    <div class="mb-1 row">
        <label for="filters" class="col-md-4 col-form-label text-end text-right">Filtros</label>
        <div class="col-md-8">
            <input
                name="filters" type="text" class="form-control"
                title="Filtros" placeholder="-filtro1-filtro-2-etc-"
                v-model="fields.filters"
            >
        </div>
    </div>

    <div class="mb-1 row">
        <label for="label_class" class="col-md-4 col-form-label text-end text-right">Clase CSS</label>
        <div class="col-md-8">
            <input
                name="label_class" type="text" class="form-control"
                title="Clase CSS"
                v-model="fields.label_class"
            >
        </div>
    </div>
</form>