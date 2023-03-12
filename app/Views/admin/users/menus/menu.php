<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(3) . '_' . $uri->getSegment(4) ?>'
var nav2RowId = '<?= $row->idcode ?>'
var sections = [
    {
        id: 'users_profile',
        text: 'Perfil',
        appSection: 'users/profile/' + nav2RowId,
        roles: [1,2,3],
        anchor: false
    },
    {
        id: 'users_edit',
        text: 'Editar',
        appSection: 'users/edit/' + nav2RowId,
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