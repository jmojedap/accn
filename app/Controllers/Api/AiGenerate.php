<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\GeminiClient;
use App\Models\AiGenerateModel;

class AiGenerate extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->aiGenerateModel = new AiGenerateModel();
	}

// Funciones
//-----------------------------------------------------------------------------

    /**
     * POST :: Genera respuesta IA en texto
     */
    public function getAnswer()
    {
        $inputData = $this->request->getJSON();
        $prompt = $inputData->prompt;

        $geminiClient = new GeminiClient();
        $contents = $this->aiGenerateModel->getMessagesAsContent($inputData);
        log_message('debug', 'contents: ' . json_encode($contents));
        $systemInstructionParts = $geminiClient->systemInstructionParts('diana-psicologa');
        
        $data = $geminiClient->generate([
            'contents' => $contents,
            'system_instruction_parts' => $systemInstructionParts,
        ]);

        return $this->response->setJSON($data);
    }
}