<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var sections = [
    {
        id: 'files_explore',
        text: 'Explorar',
        appSection: 'files/explore',
        roles: [1,2,3]
    },
    {
        id: 'files_add',
        text: 'Nuevo',
        appSection: 'files/add',
        roles: [1,2,3]
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