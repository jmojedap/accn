<?php
    $uri = current_url(true);
?>
<script>
var sectionId = '<?= $uri->getSegment(2) . '_' . $uri->getSegment(3) ?>'
var sections = [
    {
        id: 'users_explore',
        text: 'Explorar',
        cf: 'users/explore',
        roles: [1,2,3],
    },
    {
        id: 'users_add',
        text: 'Nuevo',
        cf: 'users/add',
        roles: [1,2,3],
    }
]
    
//Filter role sections
var nav_2 = sections.filter(section => section.roles.includes(parseInt(APP_RID)))

//Set active class
nav_2.forEach((section,i) => {
    nav_2[i].class = ''
    if ( section.id == sectionId ) nav_2[i].class = 'active'
})
</script>

<?php view('common/nav_2');