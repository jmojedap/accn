<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class GeminiClient
{
    /**
     * Funciones para solicitar respuestas a la API de Gemini
     * Versión 2025-10-02
     */

    protected CURLRequest $http;

    public function __construct()
    {
        // Cliente HTTP de CI4
        $this->http = Services::curlrequest([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Recibe mensaje de usuario y genera respuesta
     */
    public function generate(array $requestSettings): array
    {
        $requestSettings['model_id'] = $requestSettings['model'] ?? 'gemini-2.5-flash-lite';
        $requestSettings['generate_content_format'] = $requestSettings['generate_content_format'] ?? 'generateContent';
        $requestSettings['api_key'] = getenv('gemini.ApiKey');

        $url = $this->buildUrl($requestSettings);

        $requestData = [
            'contents' => $requestSettings['contents'],
            'system_instruction' => [
                'parts' => $requestSettings['system_instruction_parts'],
            ],
            'generationConfig' => [
                'temperature' => 1.2,
                'maxOutputTokens' => 1000,
                'responseMimeType' => 'text/plain',
            ],
        ];

        $payload = json_encode($requestData);

        $responseData = $this->executeRequest($url, $payload);

        $responseText = 'Ocurrió un error al obtener la respuesta.';
        if (
            isset($responseData['response']['candidates'][0]['content']['parts'][0]['text'])
        ) {
            $responseText = $responseData['response']['candidates'][0]['content']['parts'][0]['text'];
        }

        return [
            'model_id'         => $requestSettings['model_id'],
            'response_text'    => $responseText,
            'response_details' => $responseData['response'] ?? [],
            'error'            => $responseData['error'] ?? '',
        ];
    }

    /**
     * Ejecuta la solicitud HTTP a Gemini
     */
    protected function executeRequest(string $url, string $payload): array
    {
        $data = [
            'error'    => '',
            'response' => [],
        ];

        try {
            $response = $this->http->post($url, [
                'body' => $payload,
            ]);

            if ($response->getStatusCode() !== 200) {
                $data['error'] = 'API request failed with status ' .
                    $response->getStatusCode() . ': ' .
                    $response->getBody();
                log_message('error', 'a. Gemini API error: ' . $response->getBody());
            } else {
                $data['response'] = json_decode($response->getBody(), true);
            }
        } catch (\Throwable $e) {
            log_message('debug', 'url: ' . $url);
            log_message('debug', 'payload: ' . $payload);
            $data['error'] = $e->getMessage();
            log_message('error', 'b. Gemini API error: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Construye la URL de la API
     */
    protected function buildUrl(array $requestSettings): string
    {
        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:%s?key=%s',
            $requestSettings['model_id'],
            $requestSettings['generate_content_format'],
            $requestSettings['api_key']
        );
    }

    /**
     * Respuesta simulada para desarrollo
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
                'modelVersion' => 'gemini-2.0-flash-lite',
                'usageMetadata' => [
                    'promptTokenCount'     => 10,
                    'candidatesTokenCount' => 20,
                ],
            ],
        ];
    }

    /**
     * Carga instrucciones del sistema desde archivo Markdown
     */
    public function systemInstruction(
        string $key = 'ayudante',
        string $folder = 'ai_system_instructions'
    ): string {
        $path = rtrim(PATH_CONTENT, '/') . '/' . $folder . '/' . $key . '.md';

        if (!is_file($path)) {
            return '';
        }

        $content = file_get_contents($path);
        $content = str_replace(["\r\n", "\n"], ' ', $content);

        return trim($content);
    }
}
