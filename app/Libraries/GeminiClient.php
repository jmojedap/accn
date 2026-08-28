<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class GeminiClient
{
    /**
     * Funciones para solicitar respuestas a la API de Gemini.
     * Version 2026-08-15
     */
    protected const DEFAULT_MODEL_ID = 'gemini-3.5-flash-lite';
    protected const DEFAULT_GENERATE_CONTENT_FORMAT = 'generateContent';

    protected CURLRequest $http;

    /**
     * Prepara el cliente HTTP de CodeIgniter para enviar solicitudes JSON.
     */
    public function __construct()
    {
        $this->http = Services::curlrequest([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false,
        ]);
    }

    /**
     * Devuelve los parametros base de generacion usados por Gemini.
     */
    public function defaultGenerationSettings(): array
    {
        return [
            'temperature' => 1.6,
            'max_output_tokens' => 1000,
            'response_mime_type' => 'text/plain',
        ];
    }

    /**
     * Construye la solicitud, llama a Gemini y normaliza la respuesta.
     */
    public function generate(array $requestSettings): array
    {
        $requestSettings = $this->normalizeRequestSettings($requestSettings);
        $url = $this->buildUrl($requestSettings);

        $requestData = [
            'contents' => $requestSettings['contents'],
            // La API de Gemini requiere esta llave en camelCase.
            'generationConfig' => $this->geminiGenerationSettings($requestSettings['generation_config']),
        ];

        if (! empty($requestSettings['system_instruction_parts'])) {
            $requestData['system_instruction'] = [
                'parts' => $requestSettings['system_instruction_parts'],
            ];
        }

        $payload = json_encode($requestData, JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return $this->errorResponse(
                $requestSettings['model_id'],
                'No fue posible codificar el payload JSON para Gemini.'
            );
        }

        log_message('info', 'Gemini payload: ' . $payload);

        $responseData = $this->executeRequest($url, $payload);
        log_message('info', 'Gemini response: ' . json_encode($responseData));

        $response_text = $this->extractResponseText($responseData['response']);

        return [
            'model_id' => $requestSettings['model_id'],
            'response_text' => $response_text,
            'response_details' => $responseData['response'] ?? [],
            'error' => $responseData['error'] ?? '',
        ];
    }

    /**
     * Envia el POST a Gemini y decodifica la respuesta HTTP.
     */
    protected function executeRequest(string $url, string $payload): array
    {
        $data = [
            'error' => '',
            'response' => [],
        ];

        try {
            $response = $this->http->post($url, [
                'body' => $payload,
            ]);

            $body = $response->getBody();
            $statusCode = $response->getStatusCode();
            $decodedResponse = json_decode($body, true);

            if ($statusCode !== 200) {
                $data['error'] = 'API request failed with status ' . $statusCode . ': ' . $body;
                log_message('error', 'a. Gemini API error: ' . $body);
            }

            if (is_array($decodedResponse)) {
                $data['response'] = $decodedResponse;
            } elseif ($data['error'] === '') {
                $data['error'] = 'Gemini API returned an invalid JSON response.';
                log_message('error', 'Gemini invalid JSON response: ' . $body);
            }
        } catch (\Throwable $e) {
            $data['error'] = $e->getMessage();
            log_message('error', 'b. Gemini API error: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Arma la URL del modelo y metodo configurados para Gemini.
     */
    protected function buildUrl(array $requestSettings): string
    {
        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:%s?key=%s',
            rawurlencode((string) $requestSettings['model_id']),
            rawurlencode((string) $requestSettings['generate_content_format']),
            rawurlencode((string) $requestSettings['api_key'])
        );
    }

    /**
     * Genera una respuesta local simulada para pruebas de interfaz.
     */
    public function generateMock(): array
    {
        return [
            'response' => [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'Respuesta simulada Gemini ' . date('Y-m-d H:i:s'),
                                ],
                            ],
                        ],
                    ],
                ],
                'modelVersion' => self::DEFAULT_MODEL_ID,
                'usageMetadata' => [
                    'promptTokenCount' => 10,
                    'candidatesTokenCount' => 20,
                ],
            ],
        ];
    }

    /**
     * Convierte una instruccion Markdown en parts para Gemini.
     */
    public function systemInstructionParts(
        string $key = 'ayudante',
        string $folder = 'general'
    ): array {

        $instructionText = $this->systemInstructionText($key, $folder);

        if ($instructionText === '') {
            return [];
        }

        return [
            ['text' => $instructionText],
        ];
    }

    /**
     * Lee el texto de una instruccion del sistema desde writable.
     */
    public function systemInstructionText(
        string $key = '',
        string $folder = 'general'
    ): string {
        $cleanKey = $this->cleanPathSegment($key);
        $cleanFolder = $this->cleanPathSegment($folder);
        $path = WRITEPATH . 'ai_instructions' . DIRECTORY_SEPARATOR . $cleanFolder . DIRECTORY_SEPARATOR . $cleanKey . '.md';

        if (! is_file($path)) {
            log_message('error', "Instruccion de IA no encontrada en: {$path}");

            return '';
        }

        $content = file_get_contents($path);

        if ($content === false) {
            log_message('error', "No fue posible leer la instruccion de IA en: {$path}");

            return '';
        }

        return trim($content);
    }

    /**
     * Completa valores faltantes y unifica la estructura de configuracion.
     */
    protected function normalizeRequestSettings(array $requestSettings): array
    {
        if (! array_key_exists('contents', $requestSettings) && $this->looksLikeContents($requestSettings)) {
            $requestSettings = [
                'contents' => $requestSettings,
            ];
        }

        $requestSettings['model_id'] = $requestSettings['model_id'] ?? self::DEFAULT_MODEL_ID;
        $requestSettings['generate_content_format'] = $requestSettings['generate_content_format']
            ?? self::DEFAULT_GENERATE_CONTENT_FORMAT;
        $requestSettings['api_key'] = $requestSettings['api_key']
            ?? env('gemini.ApiKey')
            ?? env('GEMINI_API_KEY')
            ?? getenv('gemini.ApiKey')
            ?: getenv('GEMINI_API_KEY');
        $requestSettings['contents'] = $requestSettings['contents'] ?? [];
        $requestSettings['generation_config'] = $requestSettings['generation_config']
            ?? $this->defaultGenerationSettings();
        $requestSettings['system_instruction_parts'] = $this->normalizeSystemInstructionParts(
            $requestSettings['system_instruction_parts'] ?? []
        );

        return $requestSettings;
    }

    /**
     * Asegura que system_instruction_parts tenga el formato parts esperado.
     */
    protected function normalizeSystemInstructionParts(array|string $parts): array
    {
        if (is_string($parts)) {
            return [
                ['text' => $parts],
            ];
        }

        if (
            count($parts) === 1
            && isset($parts[0])
            && is_array($parts[0])
            && ! isset($parts[0]['text'])
            && isset($parts[0][0]['text'])
        ) {
            return $parts[0];
        }

        return $parts;
    }

    /**
     * Traduce la configuracion snake_case al formato requerido por Gemini.
     */
    protected function geminiGenerationSettings(array $generationSettings): array
    {
        return [
            'temperature' => $generationSettings['temperature'] ?? 1.6,
            'maxOutputTokens' => $generationSettings['max_output_tokens'] ?? 1000,
            'responseMimeType' => $generationSettings['response_mime_type'] ?? 'text/plain',
        ];
    }

    /**
     * Detecta si el arreglo recibido ya parece una lista de contents.
     */
    protected function looksLikeContents(array $requestSettings): bool
    {
        return isset($requestSettings[0])
            && is_array($requestSettings[0])
            && array_key_exists('parts', $requestSettings[0]);
    }

    /**
     * Obtiene el texto principal desde la respuesta de Gemini.
     */
    protected function extractResponseText(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text']
            ?? 'Ocurrio un error al obtener la respuesta.';
    }

    /**
     * Devuelve una respuesta normalizada cuando falla la preparacion local.
     */
    protected function errorResponse(string $model_id, string $error): array
    {
        log_message('error', $error);

        return [
            'model_id' => $model_id,
            'response_text' => 'Ocurrio un error al obtener la respuesta.',
            'response_details' => [],
            'error' => $error,
        ];
    }

    /**
     * Limpia segmentos de ruta para evitar saltos de directorio.
     */
    protected function cleanPathSegment(string $segment): string
    {
        $segment = trim($segment);
        $segment = str_replace(['../', '..\\', './', '.\\'], '', $segment);

        return trim($segment, DIRECTORY_SEPARATOR . '/\\');
    }
}