<script>
 var syncPanel = createApp({
    data(){
        return{
            loading: false,
            currentIndex: 0,
            currentTable: {},
            tables: <?= json_encode($tables) ?>,
            env: '<?= ENV ?>'
            //env: 'production'
        }
    },
    methods: {
        setCurrent: function(newIndex){
            this.currentIndex = newIndex;
            this.currentTable = this.tables[newIndex];
        },
        generateFiles: function(tableIndex){
            this.setCurrent(tableIndex)
            axios.post('<?= URL_API ?>' + 'sync/generate_files/' + this.currentTable.name)
            .then(response => {
                this.currentTable.files = response.data.files
                this.currentTable.qty_files = response.data.qty_files
                this.currentTable.table_key = response.data.prefix
                toastr['success'](response.data.message)
            })
            .catch(function(error) { console.log(error) })
        },
        getServerRowsCount: function(tableIndex){
            this.setCurrent(tableIndex)
            axios.get('<?= URL_SYNC ?>' + 'db_tables_status/')
            .then(response => {
                response.data.forEach(table => {
                    console.log(table.name, table.num_rows)
                    let index = this.tables.findIndex(t => t.name == table.name)
                    if (index !== -1) {
                        this.tables[index].server_row_count = table.num_rows
                    }
                })
            })
            .catch(function(error) { console.log(error) })
        },
        updateRowsCount: function(){
            axios.post('<?= URL_API ?>' + 'sync/update_rows_count')
            .then(response => {
                console.log(response.data)
            })
            .catch(function(error) { console.log(error) })
        },
        syncTable: function(tableIndex){
            this.setCurrent(tableIndex)
            this.currentTable.status = 'updating';
            axios.post('<?= URL_API ?>' + 'sync/sync_table/' + this.currentTable.name)
            .then(response => {
                this.currentTable.inserted = response.data.inserted
                this.currentTable.updated = response.data.updated
                toastr['success'](response.data.message)
                this.currentTable.status = 'updated';
            })
            .catch(function(error) { console.log(error) })
        },
        ago: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()            
        },
        dateFormat: function(date){
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YY h:mm A')            
        },
    },
    mounted(){
        this.getServerRowsCount()
        //this.getList()
    }
 }).mount('#syncPanel')
 </script>
