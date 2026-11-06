<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AiMesagesModel;

class AiGenerate extends BaseController
{
    protected AiMesagesModel $aiMesagesModel;

    /**
     * Inicializa el modelo usado para eliminar mensajes del chat IA.
     */
    public function __construct()
    {
        $this->aiMesagesModel = new AiMesagesModel();
    }

    /**
     * DELETE :: Valida la seleccion recibida y elimina mensajes de una conversacion.
     */
    public function deleteMessages()
    {
        $inputData = $this->request->getJSON(true) ?? [];
        $selected = $inputData['selected'] ?? [];
        $conversationId = (int) ($inputData['conversation_id'] ?? 0);

        if ($conversationId <= 0 || ! is_array($selected) || $selected === []) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['qty_deleted' => 0]);
        }

        $qtyDeleted = $this->aiMesagesModel->deleteMessages($selected, $conversationId);

        return $this->response->setJSON(['qty_deleted' => $qtyDeleted]);
    }
}
