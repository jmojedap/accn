<?php namespace App\Models;

use CodeIgniter\Model;

class AiMesagesModel extends Model
{
    protected $table = 'ai_messages';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'conversation_id',
        'ai_request_id',
        'role',
        'text',
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

    /**
     * Obtiene los mensajes de una conversación
     * 2026-03-11
     * @param int $conversationId
     * @param string $orderBy
     * @return array
     */
    public function getMessages($conversationId, $orderBy = 'created_at ASC')
    {
        return $this->where('conversation_id', $conversationId)->orderBy($orderBy)->findAll();
    }

    /**
     * Obtiene una pagina desde el mensaje mas reciente, excluyendo borrados logicos.
     */
    public function getRecentMessages(int $conversationId, int $limit = 50, int $offset = 0): array
    {
        return $this->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Inserta un mensaje de usuario en la tabla ai_messages
     * 2026-03-11
     * @param array $data
     * @return int
     */
    public function insertUserMessage($data)
    {
        $data['ai_request_id'] = null;
        $data['role'] = 'user';
        $data['updater_id'] = session()->get('user_id');
        $data['creator_id'] = session()->get('user_id');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Inserta el texto de IA y la referencia a la solicitud que lo genero.
     * 
     * @param int $conversationId
     * @param string $responseText
     * @param int|string $aiRequestId
     * @return int
     */
    public function insertAiMessage($conversationId, $responseText, $aiRequestId)
    {
        $aRow = [
            'conversation_id' => $conversationId,
            'ai_request_id' => $aiRequestId,
            'role' => 'model',
            'text' => $responseText,
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
