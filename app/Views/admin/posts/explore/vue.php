<script>
// VueApp
//-----------------------------------------------------------------------------
var exploreApp = createApp({
    data(){
        return{
            loading: false,
            deleting: false,
            filters: <?= json_encode($search['filters']) ?>, 
            results: <?= json_encode($search['results']) ?>,
            qtyResults: <?= $search['qtyResults'] ?>,
            entityInfo: <?= json_encode($entityInfo) ?>,
            settings: <?= json_encode($search['settings']) ?>,
            selected: [],
            allSelected: false,
            displayFilters: false,
            arrTypes: ItemsApp.arrayCategory(33),
            selected_row_class: 'table-info',
        }
    },
    methods: {
        search: function(){
            this.loading = true
            var searchFormValues = new FormData(document.getElementById('searchForm'))
            axios.post(URL_API + this.entityInfo.controller + '/search/', searchFormValues)
            .then(response => {
                this.results = response.data.results
                this.settings = response.data.settings
                this.qtyResults = response.data.qtyResults
                history.pushState(null, null, URL_MOD + this.entityInfo.controller + '/explore/?' + response.data.getString);
                this.allSelected = false
                this.selected = []
                this.loading = false
            })
            .catch(function (error) { console.log(error) })
        },
        selectAll: function() {
            this.selected = this.allSelected ? this.results.map(element => element.idcode) : [];
        },
        sumPage: function(sum){
            this.settings.numPage = Pcrn.limit_between(this.settings.numPage + sum, 1, this.settings.maxPage)
            setTimeout(() => {
                this.search()
            }, 50);
        },
        deleteSelected() {
            this.deleting = true
            axios.delete(
                URL_API + this.entityInfo.controller + '/delete_selected',
                { data: { ids: this.selected} }
            )
            .then(response => {
                modalDeleteSelected.hide()
                this.hideDeleted(response.data.deleteResults)
                toastr['info'](response.data.summary.deleted + ' registros eliminados')
                if (response.data.summary.not_deleted > 0) {
                    toastr['warning'](response.data.summary.not_deleted + ' registros no pudieron ser eliminados')
                }
            })
            .catch(error => {
                console.error(error)
            })
            .finally(() => {
                this.deleting = false
            })
        },
        hideDeleted: function(deletedResults){
            deletedResults.forEach(result => {
                if (result.code === 'DELETED') {
                    var elementId = '#row' + result.idcode
                    $(elementId).hide('slow')
                }
            });
        },
        resetFilter: function(filterName){
            this.filters[filterName] = ''
            setTimeout(() => {
                this.search()
            }, 100);
        },
        setCurrent: function(key){
            this.element = this.results[key];
        },
        toggleFilters: function(){
            this.displayFilters = !this.displayFilters;
        },
        typeName: function(value = '', field = 'name'){
            var typeName = ''
            var item = this.arrTypes.find(row => row.code == value)
            if ( item != undefined ) typeName = item[field]
            return typeName
        },
    },
    computed:{
        paginationText: function(){
            var startRow = (this.settings.numPage - 1) * this.settings.perPage + 1
            var endRow = this.settings.numPage * this.settings.perPage
            if (this.qtyResults < endRow ) endRow = this.qtyResults
            return startRow + '-' + endRow + ' de '
        },
    },
}).mount('#exploreApp')

var modalDeleteSelected = new bootstrap.Modal(document.getElementById('modalDeleteSelected'))
</script>