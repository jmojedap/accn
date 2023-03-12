var nav_1_elements = [
    {
        text: 'Usuarios', active: false, icon: 'fa fa-user', cf: 'users/explore', anchor: false,
        sections: ['users/explore', 'users/add', 'users/import', 'users/profile', 'users/edit', 'users/inbody', 'users/orders'],
        subelements: []
    },
    {
        text: 'Posts',
        active: false,
        icon: 'far fa-file-alt',
        cf: 'posts/explore',
        sections: ['posts/explore', 'posts/add', 'posts/import', 'posts/info', 'posts/edit', 'posts/image', 'posts/details', 'posts/comments'],
        subelements: []
    },
    {
        text: 'Repositorio',
        active: false,
        icon: 'fas fa-book',
        cf: 'repositorio/explore',
        sections: ['repositorio/explore','repositorio/info','repositorio/edit','repositorio/details','repositorio/add'],
        subelements: []
    },
    {
        text: 'Escuela de Cuidado',
        active: false,
        icon: 'fa fa-solid fa-hands-holding',
        cf: '',
        sections: [],
        subelements: [
            {
                text: 'Actividades', active: false, icon: 'fa fa-regular fa-calendar', cf: 'cuidado/explore',
                sections: ['cuidado/explore','cuidado/details','cuidado/add','cuidado/actividad_asistentes','cuidado/actividad_sesiones','cuidado/edit']
            },
            {
                text: 'Exportar', active: false, icon: 'fa fa-download', cf: 'cuidado/export_panel',
                sections: ['cuidado/export_panel']
            },
        ]
    },
];