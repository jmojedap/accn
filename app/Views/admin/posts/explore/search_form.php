<div id="searchFilters" v-show="displayFilters">
  <form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="search">
      <input type="hidden" name="numPage" v-model="settings.numPage">
      <input type="hidden" name="perPage" v-model="settings.perPage">
      <input type="hidden" name="q" v-model="filters.q">
      <div class="grid-columns-15rem">
          <div class="mb-2">
              <label for="type_id__eq" class="form-label">
                  Tipo 
                  <i class="fa-solid fa-circle-xmark btn-mini" v-on:click="resetFilter('type_id__eq')" v-show="filters.type_id__eq.length > 0"></i>
              </label>
              <select name="type_id__eq" v-model="filters.type_id__eq" class="form-select" v-on:change="search" v-bind:class="{'border-info': filters.type_id__eq.length > 0 }">
                  <option v-for="optionType in arrTypes" v-bind:value="optionType.code">
                    {{ optionType.name }}
                  </option>
              </select>
          </div>
      </div>
  </form>
</div>