<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var slug = '<?= $row->slug ?>'
var nav2RowId = '<?= $row->id ?>'
var sections = [
    {
        id: 'sits_info',
        text: 'Información',
        appSection: 'sits/info/' + slug,
        roles: [1,2,3,21],
        anchor: true
    },
    {
        id: 'sits_photo_albums',
        text: 'Álbumes',
        appSection: 'sits/photo_albums/' + slug,
        roles: [1,2,3,21],
        anchor: true
    },
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