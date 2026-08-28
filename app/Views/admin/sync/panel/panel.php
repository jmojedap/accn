 <div id="syncPanel">
    <div class="mb-3">
        <button class="btn btn-success me-2" v-on:click="updateRowsCount()">
            Actualizar filas
        </button>
    </div>
    <table class="table bg-white">
        <thead>
            <th>Nombre</th>
            <th>Filas Server</th>
            

            <!-- COLUMNAS PRODUCCION -->
            <th v-show="env == 'production'">Generar</th>
            <th v-show="env == 'production'">sync_key</th>
            <th v-show="env == 'production'">Generación</th>

            <!-- COLUMNAS LOCAL --> 
            <th v-show="env == 'development'">Filas Local</th>
            <th v-show="env == 'development'">Diferencia</th>
            <th v-show="env == 'development'">Sincronizar</th>
            <th v-show="env == 'development'">Insertados</th>
            <th v-show="env == 'development'">Actualizados</th>
            <th v-show="env == 'development'">Fecha sincronización</th>
            <th v-show="env == 'development'">Hace</th>
        </thead>
        <tbody>
            <tr v-for="(table, key) in tables">
                <td>{{ table.name }}</td>
                <td>{{ table.server_row_count }}</td>

                <!-- COLUMNAS PRODUCCION -->
                <td width="100px" v-show="env == 'production'">
                    <button class="btn btn-light btn-sm" v-on:click="generateFiles(key)" v-bind:disabled="table.status == 'loading'">
                        <span v-show="table.status == 'loading'">Generando...</span>
                        <span v-show="table.status != 'loading'">Generar</span>
                    </button>
                </td>
                <td v-show="env == 'production'">
                    <span v-show="table.table_key != ''">
                        {{ table.name }}-{{ table.table_key }}-{{ table.qty_files }}
                    </span>
                </td>
                <td v-show="env == 'production'">
                    <span v-show="table.qty_files > 0" class="badge badge-success">
                        {{ table.qty_files }} archivos
                    </span>
                    <span v-show="table.qty_rows > 0" class="badge badge-primary">
                        {{ table.qty_rows }} registros
                    </span>
                </td>
                
                <!-- COLUMNAS LOCAL -->
                <td v-show="env == 'development'">
                    <span v-show="table.local_row_count != null">
                        {{ table.local_row_count }}
                    </span>
                </td>

                <!-- DIFERENCIA -->
                <td v-show="env == 'development'">
                    <span v-show="table.local_row_count != null && table.server_row_count != null">
                        {{ table.server_row_count - table.local_row_count }}
                    </span>
                </td>
                    
                <!-- BOTÓN SINCRONIZAR -->
                <td width="100px" v-show="env == 'development'">
                    <button class="btn btn-primary btn-sm" v-on:click="syncTable(key)" v-bind:disabled="table.status == 'updating'">
                        <span v-show="table.status == 'updating'">Sincronizando...</span>
                        <span v-show="table.status != 'updating'">Sincronizar</span>
                    </button>
                </td>
                <td v-show="env == 'development'">
                    <span class="badge badge-success" v-show="table.inserted != null">
                        {{ table.inserted }}
                    </span>
                </td>
                <td v-show="env == 'development'">
                    <span class="badge badge-success" v-show="table.updated_at != null">
                        {{ table.updated_at }}
                    </span>
                </td>
                <td v-show="env == 'development'">
                    {{ dateFormat(table.last_sync_at) }}
                </td>
                <td v-show="env == 'development'">
                    {{ ago(table.last_sync_at) }}
                </td>
            </tr>
        </tbody>
    </table>
 </div>

 <?= view('admin/sync/panel/panel_vue') ?>