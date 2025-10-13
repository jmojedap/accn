<div id="profileApp">
    <div class="center_box_750">
        <div class="mb-2">
            <a class="btn btn-light w100p" href="<?= URL_ADMIN . "tools/master_login/{$row->idcode}" ?>">
                Login
            </a>
        </div>
        <table class="table bg-white">
            <tbody>
                <tr>
                    <td>Nombre</td>
                    <td>{{ user.display_name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ user.email }}</td>
                </tr>
                <tr>
                    <td>No. documento</td>
                    <td>
                        {{ documentTypeName(user.document_type) }}
                        {{ user.document_number }}
                    </td>
                </tr>
                <tr>
                    <td>Sexo</td>
                    <td>
                        {{ genderName(user.gender) }}
                    </td>
                </tr>

                <tr>
                    <td>Actualizado</td>
                    <td>{{ dateFormat(user.updated_at) }} &middot;
                        {{ dateAgo(user.updated_at) }}
                    </td>
                </tr>
                <tr>
                    <td>Creado</td>
                    <td>{{ dateFormat(user.created_at) }} &middot;
                        {{ dateAgo(user.created_at) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<script>
var profileApp = createApp({
    data(){
        return{
            loading: true,
            user: <?= json_encode($row) ?>,
            arrDocumentTypes: <?= json_encode($arrDocumentTypes) ?>,
            arrGenders: <?= json_encode($arrGenders) ?>,
        }
    },
    methods: {
        documentTypeName: function(value = '', field = 'abbreviation'){
            var documentTypeName = ''
            var item = this.arrDocumentTypes.find(row => row.code == value)
            if ( item != undefined ) documentTypeName = item[field]
            return documentTypeName
        },
        genderName: function(value = '', field = 'name'){
            var genderName = ''
            var item = this.arrGenders.find(row => row.code == value)
            if ( item != undefined ) genderName = item[field]
            return genderName
        },
        dateAgo: function(date) {
            if (!date) return ''
            return moment(date, 'YYYY-MM-DD HH:mm:ss').fromNow()
        },
        dateFormat: function (date) {
            if (!date) return ''
            return moment(date).format('D MMM YYYY')
        },
    },
}).mount('#profileApp')
</script>