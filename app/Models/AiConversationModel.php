<?php namespace App\Models;

use CodeIgniter\Model;

class AiConversationModel extends Model
{
    protected $table = 'ai_conversations';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'name',
        'description',
        'user_1_id',
        'user_2_id',
        'agent_id',
        'type',
        'related_id',
        'summary',
        'prompt_token_count',
        'candidates_token_count',
        'token_count',
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

    public function sitAgent($sitId)
    {
        //Devolver datos del sit, tabla posts
        $sit = $this->db->table('posts')
            ->where('id', $sitId)
            ->get()
            ->getRow();

        return $sit;
    }   
}
