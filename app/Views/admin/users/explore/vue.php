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
            arrRoles: ItemsApp.arrayCategory(58),
            arrGenders: ItemsApp.arrayCategory(59),
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
            
            for (const idCode in results) {
                //Si el resultado es true, se eliminó
                if ( results[idCode] == true ){
                    var elementId = '#row' + idCode
                    $(elementId).hide('slow')
                    qtyDeleted++
                } else {
                    qtyNoDeleted++
                    console.log(idCode, results[idCode])
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
        },
        roleName: function(value = '', field = 'name'){
            var roleName = ''
            var item = this.arrRoles.find(row => row.code == value)
            if ( item != undefined ) roleName = item[field]
            return roleName
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