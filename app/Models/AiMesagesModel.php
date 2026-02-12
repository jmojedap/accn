<?php namespace App\Models;

use CodeIgniter\Model;

class AiMesagesModel extends Model
{
    protected $table = 'ai_messages';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'conversation_id',
        'role',
        'text',
        'prompt_token_count',
        'candidates_token_count',
        'model_version',
        'response_details',
        'updater_id',
        'updated_at',
        'creator_id',
        'created_at'
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getMessages($conversationId)
    {
        return $this->where('conversation_id', $conversationId)->findAll();
    }

    public function insertUserMessage($data)
    {
        $data['role'] = 'user';
        $data['updater_id'] = session()->get('user_id');
        $data['creator_id'] = session()->get('user_id');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Inserta un mensaje de IA en la tabla ai_messages
     * 
     * @param int $conversationId
     * @param string $responseText
     * @param array $responseDetails
     * @return int
     */
    public function insertAiMessage($conversationId, $responseText, $responseDetails)
    {
        $aRow = [
            'conversation_id' => $conversationId,
            'role' => 'model',
            'text' => $responseText,
            'model_version' => $responseDetails['modelVersion'] ?? '-',
            'prompt_token_count' => $responseDetails['usageMetadata']['promptTokenCount'] ?? 0,
            'candidates_token_count' => $responseDetails['usageMetadata']['candidatesTokenCount'] ?? 0,
            'response_details' => json_encode($responseDetails),
            'creator_id' => session()->get('user_id'),
            'updater_id' => session()->get('user_id'),
        ];

        return $this->insert($aRow);
    }

    /**
     * Elimina los mensajes seleccionados de la conversación
     * 2026-02-07
     * @param array $selected
     * @param int $conversationId
     * @return int  
     */
    function deleteMessages($selected, $conversationId)
    {
        //Verificar si el usuario en sesión es el que creó el mensaje 
        $qtyDeleted = $this->where('conversation_id', $conversationId)
            ->whereIn('id', $selected)
            ->where('creator_id', session()->get('user_id'))
            ->delete();   
        return $qtyDeleted;
    }   
}
