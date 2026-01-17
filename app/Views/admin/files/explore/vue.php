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
            maxPage: <?= $search['maxPage'] ?>,
            qtyResults: <?= $search['qtyResults'] ?>,
            entityInfo: <?= json_encode($entityInfo) ?>,
            settings: <?= json_encode($search['settings']) ?>,
            selected: [],
            allSelected: false,
            displayFilters: false,
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
                this.maxPage = response.data.maxPage
                this.qtyResults = response.data.qtyResults
                //history.pushState(null, null, URL_API + this.cf + this.numPage +'/?' + response.data.filtersStr);
                this.allSelected = false
                this.selected = []
                this.loading = false
            })
            .catch(function (error) { console.log(error) })
        },
        selectAll: function() {
            this.selected = this.allSelected ? this.results.map(element => element.id) : [];
        },
        sumPage: function(sum){
            this.numPage = Pcrn.limit_between(this.numPage + sum, 1, this.maxPage)
            setTimeout(() => {
                this.search()
            }, 50);
        },
        deleteSelected: function(){
            this.deleting = true
            var payload = new FormData();
            payload.append('selected', this.selected);
            
            axios.post(URL_API + this.entityInfo.controller + '/delete_selected', payload)
            .then(response => {
                this.deleting = false
                modalDeleteSelected.hide()
                this.hideDeleted(response.data.results)
            })
            .catch(function (error) {
                console.log(error)
                this.deleting = false
            })
        },
        hideDeleted: function(results){
            var qtyDeleted = 0
            var qtyNoDeleted = 0
            
            for (const rowId in results) {
                //Si el resultado es true, se eliminó
                if ( results[rowId] == true ){
                    var elementId = '#row' + rowId
                    $(elementId).hide('slow')
                    qtyDeleted++
                } else {
                    qtyNoDeleted++
                    console.log(rowId, results[rowId])
                }
            }
            
            if ( qtyDeleted > 0 ) toastr['info'](qtyDeleted + ' registros eliminados')
            if ( qtyNoDeleted > 0 ) toastr['warning'](qtyNoDeleted + ' registros NO eliminados')
            this.selected = []
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
            $('#searchFilters').toggle('fast');
        },
    },
    computed:{
        paginationText: function(){
            var startRow = (this.numPage - 1) * this.perPage + 1
            var endRow = this.numPage * this.perPage
            if (this.qtyResults < endRow ) endRow = this.qtyResults
            return startRow + '-' + endRow + ' de '
        },
    },
}).mount('#exploreApp')

var modalDeleteSelected = new bootstrap.Modal(document.getElementById('modalDeleteSelected'))
</script>