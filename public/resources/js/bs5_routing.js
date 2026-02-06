//Requerir contenidos HTML vía Ajax
function getSections(menuType)
{
    beforeGetSections(menuType);
    axios.get(URL_MOD + appSection + '/?json=' + menuType)
    .then(response => {
        loadSections(response.data, menuType);
    })
    .catch(function(error) { console.log(error) })
}

//Antes de actualizar, limpiar o blanquear secciones
function beforeGetSections(menuType)
{
    $('#viewA').html('')
    $('#viewB').html('')
    
    if ( menuType === 'nav1' ) { $('#nav2').html('') }
    if ( menuType === 'nav2' ) { $('#nav3').html('') }
    
    $('.popover').remove()  //Especial, para quitar elementos de herramienta de edición enriquecida, plugin SummerNote

    $('#loadingIndicator').show()
}

//Al recibir datos con contenidos HTML, cargar las secciones
function loadSections(responseData, menuType)
{
    $('#loadingIndicator').hide()
    document.title = responseData.headTitle
    history.pushState(null, null, URL_MOD + appSection)
    
    $('#headTitle').html(responseData.headTitle)
    $('#viewA').html(responseData.viewA)
    
    //Si se requirió desde Nav 1
    if ( menuType === 'nav1')
    {
        $('#nav2').html(responseData.nav2)
        $('#nav3').html(responseData.nav3)
    }
    
    //Si se requirió desde Nav 2
    if ( menuType === 'nav2' )
    {
        $('#nav3').html(responseData.nav3)
    }
}