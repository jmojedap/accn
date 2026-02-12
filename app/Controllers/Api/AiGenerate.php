<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\GeminiClient;
use App\Models\AiGenerateModel;
use App\Models\AiMesagesModel;

class AiGenerate extends BaseController
{
    public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->aiGenerateModel = new AiGenerateModel();
		$this->aiMesagesModel = new AiMesagesModel();
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
        $conversationId = $inputData->conversation_id;

        //Guardar el mensaje en la tabla ai_messages
        $this->aiMesagesModel->insertUserMessage([
            'conversation_id' => $conversationId,
            'text' => $prompt
        ]);

        $geminiClient = new GeminiClient();
        $contents = $this->aiGenerateModel->getMessagesAsContent($conversationId);
        $systemInstructionParts = $geminiClient->systemInstructionParts($inputData->system_instruction_key);
        
        $data = $geminiClient->generate([
            'contents' => $contents,
            'system_instruction_parts' => $systemInstructionParts,
        ]);

        //Guardar la respuesta en la tabla ai_messages
        $this->aiMesagesModel->insertAiMessage(
            $conversationId,
            $data['responseText'],
            $data['responseDetails']);

        return $this->response->setJSON($data);
    }

    /**
     * DELETE :: Elimina los mensajes seleccionados de la conversación
     * 2026-01-31
     */
    public function deleteMessages()
    {
        $inputData = $this->request->getJSON();
        $selected = $inputData->selected;   
        $conversationId  = $inputData->conversation_id;
        $qtyDeleted = $this->aiMesagesModel->deleteMessages($selected, $conversationId);
        return $this->response->setJSON(['qty_deleted' => $qtyDeleted]);
    }
}