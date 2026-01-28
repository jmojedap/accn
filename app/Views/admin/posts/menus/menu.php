<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'posts_info',
        text: 'Información',
        appSection: 'posts/info/' + nav2RowId,
        roles: [1,2,3],
        anchor: false
    },
    {
        id: 'posts_details',
        text: 'Detalles',
        appSection: 'posts/details/' + nav2RowId,
        roles: [1,2],
        anchor: false
    },
    {
        id: 'posts_images',
        text: 'Imágenes',
        appSection: 'posts/images/' + nav2RowId,
        roles: [1,2,3],
        anchor: false
    },
    {
        id: 'posts_edit',
        text: 'Editar',
        appSection: 'posts/edit/' + nav2RowId,
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