 <div id="syncPanel">
    <table class="table bg-white">
        <thead>
            <th>Nombre</th>
            <th>Título</th>
            <th></th>
            <th></th>
        </thead>
        <tbody>
            <tr v-for="(table, key) in tables">
                <td>{{ table.name }}</td>
                <td>{{ table.title }}</td>
                <td>
                    <ul>
                        <li v-for="file in table.files">
                            <a target="_blank" v-bind:href="`<?= base_url('public')  ?>/` + file">{{ file }}</a>
                        </li>
                    </ul>
                </td>
                <td>
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
                {name: 'items', title: 'Ítems', qty_files: 0, files: []},
                {name: 'users', title: 'Users', qty_files: 0, files: []},
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
            axios.get('<?= URL_SYNC ?>' + 'generate_files/' + this.currentTable.name)
            .then(response => {
                this.currentTable.files = response.data.files
            })
            .catch(function(error) { console.log(error) })
        },
    },
    mounted(){
        //this.getList()
    }
 }).mount('#syncPanel')
 </script>