var nav1Elements = [
    {
        text: 'Posts',
        active: false,
        icon: 'far fa-file-alt',
        cf: 'posts/explore',
        sections: ['posts/explore', 'posts/add', 'posts/import', 'posts/info', 'posts/edit', 'posts/image', 'posts/details', 'posts/comments'],
        subelements: [],
    },
    {
        text: 'Archivos',
        active: false,
        icon: 'far fa-image',
        cf: 'files/explore',
        sections: ['files/explore', 'files/add', 'files/import', 'files/info', 'files/edit', 'files/image', 'files/details'],
        subelements: [],
    },
    {
        text: 'ECommerce',
        active: false,
        icon: 'fa fa-store',
        cf: '',
        sections: ['ecommerce'],
        subelements: [
            {
                text: 'Productos',
                active: false,
                icon: 'fa fa-box',
                cf: 'ecommerce/products/explore',
                anchor: true,
                sections: ['ecommerce/products'],
            },
        ],
    },
    {
        text: 'Ajustes',
        active: false,
        icon: 'fa fa-sliders-h',
        cf: '',
        sections: [],
        subelements: [
            {
                text: 'Ítems',
                active: false,
                icon: 'fa fa-bars',
                cf: 'items/values/0',
                sections: ['items/values', 'items/import'],
            },
        ],
    },
];
