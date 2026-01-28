 <div id="syncPanel">
    <table class="table bg-white">
        <thead>
            <th>Nombre</th>
            <th>Título</th>
            <th>sync_key</th>
            <th></th>
        </thead>
        <tbody>
            <tr v-for="(table, key) in tables">
                <td>{{ table.name }}</td>
                <td>{{ table.title }}</td>
                <td>
                    <span v-show="table.table_key != ''">
                        {{ table.name}}_{{ table.table_key }}_{{ table.qty_files }}
                    </span>
                </td>
                <td width="100px">
                    <button class="btn btn-light" v-on:click="generateFiles(key)">
                        Generar
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
 </div>
 
 <script>
 var syncPanel = createApp({
    data(){
        return{
            loading: false,
            fields: {},
            currentIndex: 0,
            currentTable: {},
            tables: [
                {name: 'items', title: 'Ítems', qty_files: 0, files: [], table_key: ''},
                {name: 'users', title: 'Users', qty_files: 0, files: [], table_key: ''},
                {name: 'posts', title: 'Posts', qty_files: 0, files: [], table_key: ''},
            ]
        }
    },
    methods: {
        setCurrent: function(newIndex){
            this.currentIndex = newIndex;
            this.currentTable = this.tables[newIndex];
        },
        generateFiles: function(tableIndex){
            this.setCurrent(tableIndex)
            axios.get('<?= URL_API ?>' + 'sync/generate_files/' + this.currentTable.name)
            .then(response => {
                this.currentTable.files = response.data.files
                this.currentTable.qty_files = response.data.qty_files
                this.currentTable.table_key = response.data.prefix
            })
            .catch(function(error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList()
    }
 }).mount('#syncPanel')
 </script>