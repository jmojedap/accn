<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\GeminiClient;

class AiGenerate extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
	}

// Funciones
//-----------------------------------------------------------------------------

    /**
     * POST :: Genera respuesta IA en texto
     */
    public function getAnswer()
    {
        $geminiClient = new GeminiClient();
        $contents = [
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => "Hola, cómo te llamas?",
                    ],
                ],
            ],
            [
                'role' => 'model',
                "parts" => [
                    [
                        "text" => "Soy Diana, una asistente de IA que responde en español.",
                    ],
                ],
            ],
            [
                'role' => 'user',
                "parts" => [
                    [
                        "text" => "¿Cómo estás?",
                    ],
                ],
            ],
        ];
        $system_instruction_parts = [
            [
                ["text" => 'Eres una asistente de IA que responde en español'],
                ["text" => 'Tu nombre es Diana.'],
            ],
        ];  
        $data['message'] = $geminiClient->generate([
            'contents' => $contents,
            'system_instruction_parts' => $system_instruction_parts,
        ]);

        return $this->response->setJSON($data);
    }
}