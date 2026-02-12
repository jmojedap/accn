<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'sits_info',
        text: 'Información',
        appSection: 'sits/info/' + nav2RowId,
        roles: [1,2,3,21],
        anchor: false
    },
    {
        id: 'sits_details',
        text: 'Detalles',
        appSection: 'sits/details/' + nav2RowId,
        roles: [1,2],
        anchor: false
    },
    {
        id: 'sits_images',
        text: 'Imágenes',
        appSection: 'sits/images/' + nav2RowId,
        roles: [1,2,3],
        anchor: false
    },
    {
        id: 'sits_edit',
        text: 'Editar',
        appSection: 'sits/edit/' + nav2RowId,
        roles: [1,2,3,21],
        anchor: false
    }
]
    
//Filter role sections
var nav2 = sections.filter(section => section.roles.includes(parseInt(APP_RID)))

//Set active class
nav2.forEach((section,i) => {
    nav2[i].class = ''
    if ( section.id == sectionId ) nav2[i].class = 'active'
})
</script>

<?= view('common/bs5/nav2');