//Establecer nuevo CF (Controller Function), y cargar secciones
function load_cf(new_cf)
{
    app_cf = new_cf;
    load_sections('nav_1');
}

//Requerir contenidos HTML vía Ajax
function load_sections(menu_type)
{
    $.ajax({
        url: url_app + app_cf + '/?json=' + menu_type,
        beforeSend: function(){
            before_send_load_sections(menu_type);
        },
        success: function(result){
            success_load_sections(result, menu_type);
        }
    });
}

//Antes de actualizar, limpiar o blanquear secciones
function before_send_load_sections(menu_type)
{
    $('#view_a').html('<p class="text-center">Cargando...3</p>');
    $('#view_b').html('');
    
    if ( menu_type === 'nav_1' ) { $('#nav_2').html(''); }
    if ( menu_type === 'nav_2' ) { $('#nav_3').html(''); }

    $('.popover').remove(); //Especial, para quitar elementos de herramienta de edición enriquecida, plugin SummerNote
}

//Al recibir datos con contenidos HTML, cargar las secciones
function success_load_sections(result, menu_type)
{
    document.title = result.head_title;
    history.pushState(null, null, url_app + app_cf);
    
    $('#head_title').html(result.head_title);
    $('#head_subtitle').html(result.head_subtitle);
    $('#view_a').html(result.view_a);
    
    //Si se requirió desde Nav 1
    if ( menu_type === 'nav_1')
    {
        $('#nav_2').html(result.nav_2);
        $('#nav_3').html(result.nav_3);
    }
    
    //Si se requirió desde Nav 2
    if ( menu_type === 'nav_2' )
    {
        $('#nav_3').html(result.nav_3);
    }
}