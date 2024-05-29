<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    /**
     * Reglas de validación general para archivos
     * 2023-04-08
     */
    public $generalFile = [
        'file_field' => [
            'label' => 'archivo',
            'rules' => [
                'uploaded[file_field]',
                'max_size[file_field,2000]',
                //'max_dims[file_field,4000,4000]',
            ],
            'errors' => [
                'max_size' => 'El tamaño del archivo no debe ser mayor a 1 Mb.',
                //'max_dims' => 'archivo no es una imagen, o es demasiado ancho o alto.'
            ]
        ],
    ];
}
