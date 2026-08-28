<?php

namespace App\Models;

use App\Libraries\GeminiClient;
use CodeIgniter\Model;

class AiGenerateModel extends Model
{

    /**
     * Extrae del payload solo las opciones permitidas para construir la
     * solicitud a Gemini.
     */
    public function requestOptions(array $inputData): array
    {
        $allowedKeys = [
            'model_id',
            'generate_content_format',
            'generation_config',
            'system_instruction_folder',
            'system_instruction_key',
        ];

        $requestOptions = [];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $inputData)) {
                $requestOptions[$key] = $inputData[$key];
            }
        }

        return $requestOptions;
    }

    /**
     * Genera una respuesta sencilla a partir de un prompt individual.
     */
    public function getAnswer(string $prompt, array $requestOptions = []): array
    {
        $requestOptions['contents'] = [
            $this->messageAsContent('user', $prompt),
        ];

        return $this->generate($requestOptions);
    }

    /**
     * Genera una respuesta usando el historial completo de una conversacion.
     */
    public function getConversationAnswer(array $conversation, array $requestOptions = []): array
    {
        $geminiClient = new GeminiClient();
        $instructionKey = $requestOptions['system_instruction_key'] ?? 'ayudante';
        $instructionFolder = $requestOptions['system_instruction_folder'] ?? 'general';

        $aiConversationModel = new AiConversationModel();
        $requestOptions['contents'] = $aiConversationModel->getMessagesAsContent((int) $conversation['id']);

        $requestOptions['system_instruction_parts'] = $requestOptions['system_instruction_parts']
            ?? $geminiClient->systemInstructionParts($instructionKey, $instructionFolder);


        // Agregar al system_instruction_parts, el resumen de la conversación de $conversation['summary'] si existe. Esto ayuda a que Gemini genere una respuesta más coherente.
        $summaryData = $conversation['summary'] ?? null;
        if (! empty($summaryData['summary'])) {
            $requestOptions['system_instruction_parts'][] = [
                'role' => 'system',
                'parts' => [
                    [
                        'text' => "Resumen de la conversación: {$summaryData['summary']}",
                    ],
                ],
            ];
        }

        $requestOptions['generation_config'] = $requestOptions['generation_config']
            ?? $geminiClient->defaultGenerationSettings();

        unset($requestOptions['system_instruction_folder']);

        return $geminiClient->generate($requestOptions);
    }

    /**
     * Genera una respuesta de Gemini con un request explicito.
     */
    public function generate(array $requestSettings): array
    {
        $geminiClient = new GeminiClient();

        $requestSettings['safetySettings'] = [
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_NONE'
            ]
        ];

        return $geminiClient->generate($requestSettings);
    }

    /* protected function normalizeRole(string $role): string
    {
        return $role === 'model' ? 'model' : 'user';
    } */
}
