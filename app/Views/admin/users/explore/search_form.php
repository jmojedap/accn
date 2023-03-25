<div id="searchFilters" style="display: none;">
  <form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="search">
      <input type="hidden" name="numPage" v-model="numPage">
      <input type="hidden" name="perPage" v-model="perPage">
      <input type="hidden" name="q" v-model="filters.q">
      <div class="grid-columns-15rem">
          <div class="mb-2">
              <label for="type" class="form-label">
                  Rol 
                  <i class="fa-solid fa-circle-xmark btn-mini" v-on:click="resetFilter('role__eq')" v-show="filters.role__eq.length > 0"></i>
              </label>
              <select name="role__eq" v-model="filters.role__eq" class="form-select" v-on:change="search" v-bind:class="{'border-info': filters.role__eq.length > 0 }">
                  <option v-for="optionRole in arrRoles" v-bind:value="optionRole.code">
                    {{ optionRole.name }}
                  </option>
              </select>
          </div>
          <div class="mb-2">
              <label for="type" class="form-label">
                  Género
                  <i class="fa-solid fa-circle-xmark btn-mini" v-on:click="resetFilter('gender__eq')" v-show="filters.gender__eq.length > 0"></i>
              </label>
              <select name="gender__eq" v-model="filters.gender__eq" class="form-select" v-on:change="search" v-bind:class="{'border-info': filters.gender__eq.length > 0 }">
                  <option v-for="optionGender in arrGenders" v-bind:value="optionGender.code">
                    {{ optionGender.name }}
                  </option>
              </select>
          </div>
      </div>
  </form>
</div>