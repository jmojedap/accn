<?php

namespace App\Models;

use App\Libraries\GeminiClient;
use CodeIgniter\Model;

class AiRequestModel extends Model
{
    protected $table = 'ai_requests';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tool',
        'tool_type_request',
        'proveedor',
        'modelo',
        'input_tokens',
        'output_tokens',
        'request_json',
        'response_json',
        'response_content',
        'response_type',
        'status',
        'error_message',
        'related_type',
        'related_id',
        'created_at',
        'creator_id',
    ];

    protected $useTimestamps = false;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    /**
     * Extrae del payload el proveedor y las opciones permitidas de la solicitud.
     */
    public function requestOptions(array $inputData): array
    {
        $allowedKeys = [
            'proveedor',
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
     * Registra la solicitud antes de llamar al proveedor y guarda su resultado.
     * tool y tool_type_request son obligatorios; response_type y creator_id son opcionales.
     * related_type y related_id son opcionales y deben proporcionarse juntos.
     */
    public function generate(array $requestSettings, array $context): array
    {
        $context = $this->validateContext($context);
        $proveedor = strtolower(trim((string) ($requestSettings['proveedor'] ?? 'gemini')));
        $result = [
            'status' => 'error',
            'ai_request_id' => null,
            'model_id' => $requestSettings['model_id'] ?? GeminiClient::DEFAULT_MODEL_ID,
            'response_text' => '',
            'response_details' => [],
            'error' => '',
            'error_message' => null,
        ];

        if ($proveedor !== 'gemini') {
            return $this->failedResult($result, "Proveedor no disponible: {$proveedor}");
        }

        try {
            $geminiClient = $this->geminiClient();
            $requestSettings = $this->prepareGeminiRequest($requestSettings, $geminiClient);
            $requestJson = json_encode(
                $geminiClient->requestBody($requestSettings),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo preparar la solicitud de IA: ' . $e->getMessage());

            return $this->failedResult($result, 'No se pudo preparar la solicitud de IA.');
        }

        try {
            $requestId = $this->createRequest($requestSettings, $context, $requestJson);
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo registrar la solicitud de IA: ' . $e->getMessage());
            $requestId = false;
        }

        // No consumir la API si la solicitud no pudo registrarse.
        if ($requestId === false) {
            return $this->failedResult($result, 'No se pudo registrar la solicitud de IA.');
        }

        $result['ai_request_id'] = $requestId;
        $responseJson = null;

        try {
            $apiResponse = $geminiClient->generate($requestSettings);
            $result['response_details'] = $apiResponse['response_details'] ?? [];
            $result['response_text'] = $apiResponse['response_text'] ?? '';
            $error = ($apiResponse['error'] ?? '')
                ?: ($result['response_details']['error']['message'] ?? '');

            if ($error === '' && trim($result['response_text']) === '') {
                $error = 'Gemini no devolvio contenido de texto.';
            }

            $responseJson = json_encode(
                $result['response_details'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if ($error !== '') {
                $result = $this->failedResult($result, $error);
            } else {
                $result['status'] = 'success';
            }
        } catch (\Throwable $e) {
            $result = $this->failedResult($result, $e->getMessage());
        }

        return $this->completeRequest($requestId, $result, $responseJson);
    }

    /**
     * Valida la herramienta, el formato de respuesta y la relacion opcional con una entidad.
     */
    protected function validateContext(array $context): array
    {
        foreach (['tool' => 80, 'tool_type_request' => 60] as $field => $maxLength) {
            if (! isset($context[$field]) || ! is_string($context[$field])) {
                throw new \InvalidArgumentException("Falta el campo {$field}.");
            }

            $context[$field] = trim($context[$field]);

            if ($context[$field] === '' || mb_strlen($context[$field]) > $maxLength) {
                throw new \InvalidArgumentException("El campo {$field} no es valido.");
            }
        }

        $context['response_type'] = $context['response_type'] ?? 'plain_text';

        if (! in_array($context['response_type'], ['plain_text', 'markdown', 'html', 'other'], true)) {
            throw new \InvalidArgumentException('response_type no permitido.');
        }

        $context['related_type'] = $context['related_type'] ?? null;
        $context['related_id'] = $context['related_id'] ?? null;

        if ($context['related_type'] !== null || $context['related_id'] !== null) {
            if (
                ! is_string($context['related_type'])
                || trim($context['related_type']) === ''
                || mb_strlen(trim($context['related_type'])) > 50
            ) {
                throw new \InvalidArgumentException('related_type debe ser un texto no vacio de hasta 50 caracteres.');
            }

            if (! is_int($context['related_id']) && ! is_string($context['related_id'])) {
                throw new \InvalidArgumentException('related_id debe ser un entero positivo.');
            }

            $relatedId = filter_var($context['related_id'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if ($relatedId === false) {
                throw new \InvalidArgumentException('related_id debe ser un entero positivo.');
            }

            $context['related_type'] = trim($context['related_type']);
            $context['related_id'] = $relatedId;
        }

        $context['creator_id'] = $context['creator_id'] ?? session()->get('user_id');

        return $context;
    }

    /**
     * Inserta el registro pending con el cuerpo que se enviara al proveedor.
     *
     * @return int|string|false
     */
    protected function createRequest(array $requestSettings, array $context, string $requestJson)
    {
        return $this->insert([
            'tool' => $context['tool'],
            'tool_type_request' => $context['tool_type_request'],
            'proveedor' => 'gemini',
            'modelo' => $requestSettings['model_id'],
            'request_json' => $requestJson,
            'response_type' => $context['response_type'],
            'status' => 'pending',
            'related_type' => $context['related_type'],
            'related_id' => $context['related_id'],
            'creator_id' => $context['creator_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Guarda el resultado y los tokens; un fallo de guardado impide reportar exito.
     *
     * @param int|string $requestId
     */
    protected function completeRequest($requestId, array $result, ?string $responseJson): array
    {
        $usage = $result['response_details']['usageMetadata'] ?? [];

        try {
            $updated = $this->update($requestId, [
                'input_tokens' => $usage['promptTokenCount'] ?? null,
                'output_tokens' => $usage['candidatesTokenCount'] ?? null,
                'response_json' => $responseJson,
                'response_content' => $result['response_text'],
                'status' => $result['status'],
                'error_message' => $result['error_message'],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo actualizar la solicitud de IA: ' . $e->getMessage());
            $updated = false;
        }

        if (! $updated) {
            return $this->failedResult(
                $result,
                trim($result['error'] . ' No se pudo actualizar el registro de la solicitud de IA.')
            );
        }

        return $result;
    }

    /**
     * Mantiene error para los consumidores actuales y error_message para el registro.
     */
    protected function failedResult(array $result, string $message): array
    {
        $result['status'] = 'error';
        $result['error'] = $message;
        $result['error_message'] = $message;

        return $result;
    }

    /**
     * Permite sustituir el transporte en pruebas sin consumir la API real.
     */
    protected function geminiClient(): GeminiClient
    {
        return new GeminiClient();
    }

    /**
     * Prepara las instrucciones y configuracion de Gemini antes de registrar la solicitud.
     */
    protected function prepareGeminiRequest(array $requestSettings, GeminiClient $geminiClient): array
    {
        $requestSettings['model_id'] = $requestSettings['model_id'] ?? GeminiClient::DEFAULT_MODEL_ID;
        $instructionKey = $requestSettings['system_instruction_key'] ?? 'ayudante';
        $instructionFolder = $requestSettings['system_instruction_folder'] ?? 'general';

        $requestSettings['system_instruction_parts'] = $requestSettings['system_instruction_parts']
            ?? $geminiClient->systemInstructionParts($instructionKey, $instructionFolder);

        $summary = $requestSettings['conversation_summary'] ?? null;
        if (is_array($summary)) {
            $summary = $summary['summary'] ?? null;
        }

        if (is_string($summary) && trim($summary) !== '') {
            if (is_string($requestSettings['system_instruction_parts'])) {
                $requestSettings['system_instruction_parts'] = [
                    ['text' => $requestSettings['system_instruction_parts']],
                ];
            }

            $requestSettings['system_instruction_parts'][] = [
                'text' => 'Resumen de la conversación: ' . trim($summary),
            ];
        }

        $requestSettings['generation_config'] = $requestSettings['generation_config']
            ?? $geminiClient->defaultGenerationSettings();

        unset(
            $requestSettings['proveedor'],
            $requestSettings['system_instruction_folder'],
            $requestSettings['conversation_summary']
        );

        $requestSettings['safetySettings'] = [
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_NONE',
            ],
        ];

        return $requestSettings;
    }
}
