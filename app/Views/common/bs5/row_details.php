<div class="center_box_920">
    <table class="table bg-white table-sm">
        <tbody>
            <?php foreach ( $row as $fieldName => $fieldValue ) : ?>
                <tr>
                    <td class="td-title text-muted"><?= str_replace('_', ' ', $fieldName) ?></td>
                    <td class="text-break"><?= $fieldValue ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>