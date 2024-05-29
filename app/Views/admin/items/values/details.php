<!-- Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">{{ item.name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless table-sm">
                    <tbody>
                        <tr>
                            <td class="td-title">Variable parámetro</td>
                            <td><strong class="text-primary">{{ currCategory.name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="td-title">Código numérico</td>
                            <td>{{ item.code }}</td>
                        </tr>
                        <tr>
                            <td class="td-title">Abreviatura</td>
                            <td>{{ item.abbreviation }}</td>
                        </tr>
                        <tr>
                            <td class="td-title">Nombre</td>
                            <td>{{ item.item_name }}</td>
                        </tr>
                        <tr>
                            <td class="td-title">Nombre completo</td>
                            <td>{{ item.long_name }}</td>
                        </tr>
                        <tr>
                            <td class="td-title">Nombre corto</td>
                            <td>{{ item.short_name }}</td>
                        </tr>
                        <tr>
                            <td class="td-title">Descripción</td>
                            <td>{{ item.description }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>