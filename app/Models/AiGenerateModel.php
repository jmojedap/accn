<?php namespace App\Models;

use CodeIgniter\Model;

class AiGenerateModel extends Model
{

    public function getAnswer($prompt)
    {
        $geminiClient = new GeminiClient();
        $contents = [
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => $prompt,
                    ],
                ],
            ],
        ];
        $response = $geminiClient->generate($contents);
        return $response;
    }

    public function getMessagesAsContent($inputData)
    {
        $contents = [
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => "¿Cómo estás?",
                    ],
                ],
            ],
            [
                'role' => 'model',
                "parts" => [
                    [
                        "text" => "Hola muy bien gracias.",
                    ],
                ],
            ],
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => "Me alegra bonita",
                    ],
                ],
            ],
            [
                'role' => 'model',
                "parts" => [
                    [
                        "text" => "Ay que lindo, muchas gracias, qué quieres saber de mí, pregunta lo que quieras",
                    ],
                ],
            ],
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => "Lo que sea? mmm jejeje por ejemplo de qué color es tu ropa interior?",
                    ],
                ],
            ],
            [
                'role' => 'model',
                "parts" => [
                    [
                        "text" => "jajaja tan bobo... pero ok, es blanca... contento?",
                    ],
                ],
            ],
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => $inputData->prompt,
                    ],
                ],
            ],
        ];
        return $contents;
    }

}