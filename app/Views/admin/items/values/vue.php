<script>
// Variables
//-----------------------------------------------------------------------------
var appStates = {
    add: { buttonText: 'Agregar', buttonClass: 'btn-success'},
    edit: { buttonText: 'Actualizar', buttonClass: 'btn-primary'},
    saved: { buttonText: 'Guardado', buttonClass: 'btn-success' },
    inserted: { buttonText: 'Guardado', buttonClass: 'btn-success'},
    updated: { buttonText: 'Actualizado', buttonClass: 'btn-success'}
};

var baseUrl = '<?= base_url() ?>';
var categoryCode = <?= $categoryCode?>;
var categories = <?= json_encode($arrCategories) ?>;
var startCategory = categories.find(category => category.code == categoryCode);
var scope = '<?= $scope ?>';

// VueApp
//-----------------------------------------------------------------------------
var valuesApp = createApp({
    data(){
        return{
            loading: false,
            deleting: false,
            entityInfo: <?= json_encode($entityInfo) ?>,
            filters: {'q':'','scope':scope},
            categories: categories,
            categoryKey: 0,
            currCategory: startCategory,
            rowKey: 0,
            rowId: 0,
            list: [],
            item: {},
            marginValue: '50',
            fields: {
                name: '',
                code: '',
                abbreviation: '',
                filters: '',
                slug: '',
                description: '',
                long_name: '',
                short_name: ''
            },
            formConfig: {
                title: 'Nuevo elemento',
                buttonText: 'Agregar',
                buttonClass: 'btn-primary'
            },
            appState: appStates.add,
            scopes: <?= json_encode($arrScopes) ?>,
            appRid: APP_RID,
            displayFormat: 'table',
        }
    },
    methods: {
        getCategories: function(){
            this.loading = true
            var formValues = new FormData()
            formValues.append('q', this.filters.q)
            formValues.append('category_id__eq', 0)
            formValues.append('filters__like', this.filters.scope)
            axios.post(URL_API + 'items/get_list/', formValues)
            .then(response => {
                this.categories = response.data
                this.setFirstCategory()
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        setFirstCategory: function(){
            if ( this.categories.length > 0 ) {
                this.currCategory = this.categories[0]
                this.getList()
                this.setMarginValue(0)
            }
        },
        clearFilters: function(){
            this.filters.q = ''
            this.getCategories()
        },
        getList: function (){
            this.loading = true
            var formValues = new FormData()
            formValues.append('category_id__eq', this.currCategory.code)
            axios.post(URL_API + 'items/get_list/', formValues)
            .then(response => {
                this.list = response.data
                history.pushState(null, null, URL_MOD + 'items/values/' + this.currCategory.code + '/' + this.filters.scope);
                this.loading = false
            })
            .catch( function(error) {console.log(error)} )
        },
        //Cargar el formulario con datos de un elemento (key) de la list
        loadFormValues: function (key){
            this.appState = appStates.edit
            this.rowId = this.list[key].id
            for ( field in this.fields ) { this.fields[field] = this.list[key][field] }
            this.fields.parent_id = '0' + this.list[key].parent_id
            this.formConfig.title = 'Editar: ' + this.list[key].name
        },
        //Establece un elemento como el actual
        setCurrent: function(key) {
            this.rowId = this.list[key].id
            this.rowKey = key
            this.item = this.list[key]
            console.log(this.rowId)
        },
        setCategory: function(categoryCode){
            var categoryKey = this.categories.findIndex(row => row.code == categoryCode)
            if ( categoryKey < 0 ) categoryKey = 0

            this.categoryKey = categoryKey
            this.currCategory = this.categories[categoryKey]
            this.setMarginValue()
            
            this.getList()
        },
        setMarginValue: function(category_key){
            this.marginValue = 0 + category_key * 42
        },
        handleSubmit: function(){
            this.loading = true
            var formValues = new FormData(document.getElementById('itemForm'))
            axios.post(URL_API + 'items/save/' + this.rowId, formValues)
            .then(response => {
                if ( response.data.savedId > 0 ) 
                {
                    appState = appStates.saved
                    toastr['success']('Registro guardado')
                    
                    if ( this.rowId > 0 ) {
                        this.appState = appStates.updated
                    } else {
                        this.appState = appStates.inserted
                        for ( key in this.fields ) this.fields[key] = ''
                    }
                    
                    this.getList()
                    this.rowId = response.data.savedId
                    modalItemForm.hide()
                }
                this.loading = false
            })
            .catch(function (error) {
                console.log(error)
                this.loading = false
                modalItemForm.hide()
            })
        },
        autocomplete: function(){
            this.setNames()
            if ( this.fields.abbreviation.length == 0 ) { this.setAbbreviation() }
            if ( this.fields.slug.length == 0 ) { this.setSlug() }
        },
        deleteElement: function(){
            this.deleting = true
            axios.get(URL_API + 'items/delete_row/' + this.rowId + '/' + this.currCategory.code)
            .then(response => {
                console.log(response.data.result)
                if ( response.data.result === true )
                {
                    this.getList()
                    toastr['info'](this.entityInfo.singular + ' eliminado')
                }
                this.deleting = false
                modalSingleDelete.hide()
            })
            .catch(function (error) {
                console.log(error)
                this.deleting = false
                modalSingleDelete.hide()
            })
        },
        clearForm: function() {
            this.rowId = 0;
            this.rowKey = 0;
            for ( key in this.fields ) { this.fields[key] = ''; }
            this.formConfig.title = 'Nuevo elemento';
            this.appState = appStates.add;
        },
        setNames: function(){
            if ( this.fields.description.length == 0 ) { 
                this.fields.description = this.currCategory.name + ', ' + this.fields.name
            }
            if ( this.fields.short_name.length == 0 ) { this.fields.short_name = this.fields.name }
            if ( this.fields.long_name.length == 0 ) { this.fields.long_name = this.fields.name }
            if ( this.fields.position == null ) { this.fields.position = this.fields.code }
        },
        setAbbreviation: function() {
            var abbreviation = '';
            abbreviation = this.fields.name.substr(0,3);
            abbreviation = abbreviation.toLowerCase();
            this.fields.abbreviation = abbreviation;
        },
        //Establecer item.slug
        setSlug: function() {
            const formValues = new FormData();
            formValues.append('text', this.fields.name);
            formValues.append('table', 'items');
            formValues.append('field', 'slug');
            
            axios.post(baseUrl + 'tools/get_unique_slug/', formValues)
            .then(response => {
                console.log(response.data)
                this.fields.slug = response.data
            })
            .catch(function (error) { console.log(error) })
        },
    },
    mounted(){
        //this.getCategories()
        this.setCategory(categoryCode)
    }
}).mount('#valuesApp')

var modalItemForm = new bootstrap.Modal(document.getElementById('modalItemForm'));
var modalSingleDelete = new bootstrap.Modal(document.getElementById('modalSingleDelete'));
</script>