var nav1Elements = [
    {
        text: 'Inicio', active: false, icon: 'fa fa-gauge', cf: 'app/dashboard', anchor: false,
        sections: ['app/dashboard'],
        subelements: []
    },
    {
        text: 'Usuarios', active: false, icon: 'fa fa-user', cf: 'users/explore', anchor: true,
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
        text: 'Archivos',
        active: false,
        icon: 'far fa-image',
        cf: 'files/explore',
        sections: ['files/explore', 'files/add', 'files/import', 'files/info', 'files/edit', 'files/image', 'files/details'],
        subelements: []
    },
    {
        text: 'Comentarios',
        active: false,
        icon: 'far fa-comment',
        cf: 'comments/explore',
        sections: ['comments/explore', 'comments/add', 'comments/info'],
        subelements: []
    },
    {
        text: 'Ajustes',
        active: false,
        style: '',
        icon: 'fa fa-sliders-h',
        cf: '',
        sections: ['config/options'],
        subelements: [
            {
                text: 'General', active: false, icon: 'fa fa-cogs', cf: 'config/options',
                sections: ['config/options', 'config/processes', 'config/colors', 'config/import', 'config/import_e']
            },
            {
                text: 'Ítems', active: false, icon: 'fa fa-bars', cf: 'items/manage',
                sections: ['items/manage', 'items/import']
            },
            {
                text: 'Base de datos', active: false, icon: 'fa fa-database', cf: 'sync/panel',
                sections: ['sync/panel']
            },
            {
                text: 'Eventos', active: false, icon: 'far fa-clock', cf: 'events/summary', anchor: false,
                sections: ['events/explore', 'events/summary']
            },
            {
                text: 'Lugares', active: false, icon: 'fa fa-map-marker-alt', cf: 'places/explore', anchor: false,
                sections: ['places/explore', 'places/add', 'places/edit'],
            },
            {
                text: 'Periodos', active: false, icon: 'fa fa-calendar', cf: 'periods/explore', anchor: false,
                sections: ['periods/explore', 'periods/add', 'periods/edit', 'periods/calendar'],
            }
        ]
    },
    {
        text: 'Ayuda',
        active: false,
        icon: 'far fa-question-circle',
        cf: 'app/help',
        sections: ['app/help'],
        subelements: []
    },
];