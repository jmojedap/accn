<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'files_info',
        text: 'Información',
        appSection: 'files/info/' + nav2RowId,
        roles: [1,2,3],
        anchor: false
    },
    {
        id: 'files_details',
        text: 'Detalles',
        appSection: 'files/details/' + nav2RowId,
        roles: [1,2],
        anchor: false
    },
    {
        id: 'files_edit',
        text: 'Editar',
        appSection: 'files/edit/' + nav2RowId,
        roles: [1,2,3],
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