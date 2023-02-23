
<script>
// VueApp
//-----------------------------------------------------------------------------
const exploreApp = createApp({
    data(){
        return{
            loading: false,
            deleting: false,
            settings: <?= json_encode($settings) ?>,
            cf: '<?= $cf; ?>',
            controller: '<?= $controller; ?>',
            numPage: <?= $settings['numPage'] ?>,
            maxPage: <?= $maxPage ?>,
            qtyResults: <?= $qtyResults ?>,
            list: <?= json_encode($list) ?>,
            element: [],
            selected: [],
            filters:{
                role__eq:'<?= $input['role__eq'] ?>',
            },
            allSelected: false,
            displayFilters: false,
            arrRoles: <?= json_encode($arrRoles) ?>,
            selected_row_class: 'table-info',
        }
    },
    methods: {
        search: function(){
            this.loading = true
            var searchFilters = new FormData(document.getElementById('searchForm'))
            axios.post(URL_API + this.controller + '/search/', searchFilters)
            .then(response => {
                this.list = response.data.list
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
            this.selected = [];
            if (this.allSelected) {
                for (element in this.list) {
                    this.selected.push(this.list[element].idcode);
                }
            }
        },
        sumPage: function(sum){
            this.numPage = Pcrn.limit_between(this.numPage + sum, 1, this.maxPage);
            this.search();
        },
        deleteSelected: function(){
            this.deleting = true
            var payload = new FormData();
            payload.append('selected', this.selected);
            
            axios.post(URL_API + this.controller + '/delete_selected', payload)
            .then(response => {
                this.deleting = false
                modalDeleteSelected.hide()
                this.hideDeleted(response.data.results)
            })
            .catch(function (error) {
                console.log(error)
                this.deleting = true
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
        set_current: function(key){
            this.element = this.list[key];
        },
        toggle_filters: function(){
            this.displayFilters = !this.displayFilters;
            $('#adv_filters').toggle('fast');
        }
    },
    mounted(){
        //this.search()
    }
}).mount('#exploreApp')

var modalDeleteSelected = new bootstrap.Modal(document.getElementById('modalDeleteSelected'))
</script>