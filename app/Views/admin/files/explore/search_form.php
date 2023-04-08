<div id="searchFilters" style="display: none;">
  <form accept-charset="utf-8" method="POST" id="searchForm" @submit.prevent="search">
      <input type="hidden" name="numPage" v-model="numPage">
      <input type="hidden" name="perPage" v-model="perPage">
      <input type="hidden" name="q" v-model="filters.q">
  </form>
</div>