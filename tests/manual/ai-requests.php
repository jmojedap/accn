<?php

/**
 * Verifica registro y chat con SQLite en memoria y transporte Gemini simulado.
 * Ejecutar: D:/xampp/php/php.exe -d extension=sqlite3 tests/manual/ai-requests.php
 */

use App\Controllers\Api\AiConversations;
use App\Database\Migrations\CreateAiRequests;
use App\Libraries\GeminiClient;
use App\Models\AiConversationModel;
use App\Models\AiMesagesModel;
use App\Models\AiRequestModel;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Database;
use Config\Services;

chdir(dirname(__DIR__, 2));
require 'system/Test/bootstrap.php';
require 'app/Database/Migrations/2026-10-03-000001_CreateAiRequests.php';

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (! (error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

Services::injectMock('session', new class {
    public function get(string $key)
    {
        return $key === 'user_id' ? 77 : null;
    }
});

final class RequestTestGeminiClient extends GeminiClient
{
    public array $responses = [];
    public array $bodies = [];
    public int $calls = 0;
    public $onRequest;

    public function __construct()
    {
        // El transporte simulado nunca crea un cliente HTTP.
    }

    public function systemInstructionParts(string $key = 'ayudante', string $folder = 'general'): array
    {
        return [['text' => "Instruccion {$folder}/{$key}"]];
    }

    protected function executeRequest(string $url, string $payload): array
    {
        $this->calls++;
        $this->bodies[] = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if ($this->onRequest !== null) {
            ($this->onRequest)($this->bodies[count($this->bodies) - 1]);
        }

        checkRequests($this->responses !== [], 'No debe ejecutarse una llamada inesperada a Gemini.');
        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return ['error' => '', 'response' => $response];
    }
}

final class RequestTestModel extends AiRequestModel
{
    public RequestTestGeminiClient $client;

    public function __construct(ConnectionInterface $connection, RequestTestGeminiClient $client)
    {
        parent::__construct($connection);
        $this->client = $client;
    }

    protected function geminiClient(): GeminiClient
    {
        return $this->client;
    }
}

final class RequestTestConversationModel extends AiConversationModel
{
    public AiRequestModel $requests;

    protected function aiRequestModel(): AiRequestModel
    {
        return $this->requests;
    }
}

final class RequestTestController extends AiConversations
{
    public function __construct(ConnectionInterface $connection, RequestTestModel $requests)
    {
        $this->db = $connection;
        $conversations = new RequestTestConversationModel($connection);
        $conversations->requests = $requests;
        $this->aiConversationModel = $conversations;
        $this->aiMesagesModel = new AiMesagesModel($connection);
        $this->aiRequestModel = $requests;
    }

    public function answer(array $payload): Response
    {
        $this->request = new IncomingRequest(
            new App(),
            new URI('http://localhost/accn/'),
            json_encode($payload, JSON_THROW_ON_ERROR),
            new UserAgent()
        );
        $this->response = new Response(new App());

        return $this->getAnswer();
    }
}

function checkRequests(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function requestFixture(bool $debug = true): array
{
    $connection = Database::connect([
        'DBDriver' => 'SQLite3',
        'database' => ':memory:',
        'DBPrefix' => '',
        'DBDebug' => $debug,
    ], false);
    $forge = new class ($connection) extends CodeIgniter\Database\SQLite3\Forge {
        public function createTable(string $table, bool $ifNotExists = false, array $attributes = [])
        {
            // ENGINE y COLLATE son atributos de MySQL; el esquema se conserva.
            return parent::createTable($table, $ifNotExists);
        }
    };
    (new CreateAiRequests($forge))->up();
    $client = new RequestTestGeminiClient();

    return [$connection, $client, new RequestTestModel($connection, $client)];
}

function requestResponse(string $text): array
{
    return ['candidates' => [['content' => ['parts' => [['text' => $text]]]]]];
}

[$connection, $client, $requests] = requestFixture();
$settings = [
    'contents' => [['role' => 'user', 'parts' => [['text' => 'Hola']]]],
    'api_key' => 'TEST_SECRET_NOT_FOR_STORAGE',
    'url' => 'https://example.invalid/secret',
    'system_instruction_parts' => 'Instruccion de prueba',
    'conversation_summary' => ['summary' => 'Resumen anterior'],
    'generation_config' => ['max_output_tokens' => 123],
];
$context = [
    'tool' => 'chat',
    'tool_type_request' => 'test',
    'creator_id' => 51,
    'related_type' => 'conversation',
    'related_id' => '12',
];
$details = requestResponse('Hola ');
$details['candidates'][0]['content']['parts'][] = ['text' => 'mundo'];
$details['usageMetadata'] = ['promptTokenCount' => 0, 'candidatesTokenCount' => 8];
$client->responses[] = $details;
$client->onRequest = static function (array $body) use ($connection): void {
    $pending = $connection->table('ai_requests')->orderBy('id', 'DESC')->get()->getRowArray();
    checkRequests($pending['status'] === 'pending', 'La solicitud debe existir antes de consumir la API.');
    checkRequests($pending['related_type'] === 'conversation' && (int) $pending['related_id'] === 12, 'La relacion debe guardarse antes de consumir la API.');
    checkRequests(json_decode($pending['request_json'], true) === $body, 'Debe guardarse el cuerpo realmente enviado.');
};
$result = $requests->generate($settings, $context);
$row = $requests->find($result['ai_request_id']);
checkRequests($result['status'] === 'success' && $result['error'] === '' && $result['error_message'] === null, 'Contrato de exito incorrecto.');
checkRequests($result['response_text'] === 'Hola mundo' && $row['response_content'] === 'Hola mundo', 'Debe conservar todas las partes de texto.');
checkRequests($row['status'] === 'success' && (int) $row['creator_id'] === 51, 'Faltan el estado o creator_id.');
checkRequests($row['modelo'] === GeminiClient::DEFAULT_MODEL_ID && $row['response_type'] === 'plain_text', 'Valores predeterminados incorrectos.');
checkRequests((int) $row['input_tokens'] === 0 && (int) $row['output_tokens'] === 8, 'Los tokens cero tambien deben persistirse.');
checkRequests(json_decode($row['response_json'], true) === $details && $row['created_at'] !== '', 'Faltan respuesta original o fecha.');
checkRequests(strpos($row['request_json'], $settings['api_key']) === false && strpos($row['request_json'], $settings['url']) === false, 'No guardar credenciales ni URL.');
$body = json_decode($row['request_json'], true);
checkRequests($body['generationConfig']['maxOutputTokens'] === 123, 'La configuracion guardada debe estar normalizada.');
checkRequests(count($body['system_instruction']['parts']) === 2, 'Deben incluirse las instrucciones y el resumen.');
$client->onRequest = null;

$client->responses[] = requestResponse('Sin tokens');
$result = $requests->generate($settings + ['model_id' => 'modelo-prueba'], [
    'tool' => 'chat', 'tool_type_request' => 'test', 'response_type' => 'markdown',
]);
$row = $requests->find($result['ai_request_id']);
checkRequests($row['input_tokens'] === null && $row['output_tokens'] === null, 'Los tokens ausentes deben permanecer NULL.');
checkRequests((int) $row['creator_id'] === 77 && $row['modelo'] === 'modelo-prueba' && $row['response_type'] === 'markdown', 'Contexto o modelo personalizado incorrectos.');
checkRequests($row['related_type'] === null && $row['related_id'] === null, 'Las solicitudes sin entidad relacionada deben guardar ambos campos como NULL.');

$before = $requests->countAllResults();
$calls = $client->calls;
foreach ([
    [],
    ['tool' => 'chat'],
    $context + ['response_type' => 'json'],
    array_replace($context, ['tool' => ' ']),
    array_replace($context, ['related_type' => null]),
    array_replace($context, ['related_id' => null]),
    array_replace($context, ['related_type' => ' ']),
    array_replace($context, ['related_type' => str_repeat('a', 51)]),
    array_replace($context, ['related_id' => 0]),
    array_replace($context, ['related_id' => -1]),
    array_replace($context, ['related_id' => '1.5']),
    array_replace($context, ['related_id' => true]),
    array_replace($context, ['related_id' => ['12']]),
] as $invalid) {
    try {
        $requests->generate($settings, $invalid);
        throw new RuntimeException('El contexto invalido debe rechazarse.');
    } catch (InvalidArgumentException $e) {
        // La validacion debe ocurrir antes de registrar o enviar la solicitud.
    }
}
$unsupported = $requests->generate($settings + ['proveedor' => 'otro'], $context);
$invalidSettings = $settings;
$invalidSettings['contents'][0]['parts'][0]['text'] = "\xB1\x31";
$invalidJson = $requests->generate($invalidSettings, $context);
checkRequests($unsupported['status'] === 'error' && $invalidJson['status'] === 'error', 'Proveedor o JSON invalidos deben fallar.');
checkRequests($requests->countAllResults() === $before && $client->calls === $calls, 'No ejecutar ni registrar solicitudes invalidas.');

foreach ([
    ['error' => ['message' => 'Cuota agotada']],
    ['promptFeedback' => ['blockReason' => 'SAFETY']],
    requestResponse('   '),
    new RuntimeException('Fallo de transporte simulado'),
] as $failure) {
    $client->responses[] = $failure;
    $result = $requests->generate($settings, $context);
    $row = $requests->find($result['ai_request_id']);
    checkRequests($result['status'] === 'error' && $result['error'] !== '' && $result['error'] === $result['error_message'], 'El fallo debe devolver errores compatibles.');
    checkRequests($row['status'] === 'error' && $row['error_message'] === $result['error'], 'El fallo debe persistirse.');
    checkRequests($row['related_type'] === 'conversation' && (int) $row['related_id'] === 12, 'Los errores tambien deben conservar la entidad relacionada.');
    if (is_array($failure)) {
        checkRequests(json_decode($row['response_json'], true) === $failure, 'Conservar tambien el JSON de errores y bloqueos.');
    } else {
        checkRequests($row['response_json'] === null, 'Sin respuesta, response_json debe quedar NULL.');
    }
}

foreach ([true, false] as $debug) {
    [$dbFailure, $clientFailure, $requestsFailure] = requestFixture($debug);
    $dbFailure->query("CREATE TRIGGER reject_insert BEFORE INSERT ON ai_requests BEGIN SELECT RAISE(FAIL, 'insert failure'); END");
    $result = $requestsFailure->generate($settings, $context);
    checkRequests($result['status'] === 'error' && $result['ai_request_id'] === null && $clientFailure->calls === 0, 'Un fallo de insercion debe impedir consumir la API.');
    $dbFailure->query('DROP TRIGGER reject_insert');
    $dbFailure->query("CREATE TRIGGER reject_update BEFORE UPDATE ON ai_requests BEGIN SELECT RAISE(FAIL, 'update failure'); END");
    $clientFailure->responses[] = requestResponse('Respuesta que no pudo registrarse');
    $result = $requestsFailure->generate($settings, $context);
    checkRequests($result['status'] === 'error' && $result['ai_request_id'] !== null && strpos($result['error'], 'actualizar') !== false, 'No reportar exito si falla el guardado final.');
    checkRequests($dbFailure->DBDebug === $debug, 'No modificar la configuracion compartida de la BD.');
}

// Integracion: controlador, historial, registro de respuestas y resumen.
$connection->query('CREATE TABLE ai_conversations (id INTEGER PRIMARY KEY, summary TEXT, system_instruction_key TEXT, updater_id INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
$connection->query('CREATE TABLE ai_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, conversation_id INTEGER, ai_request_id BIGINT NULL UNIQUE, role TEXT, text TEXT, creator_id INTEGER, updater_id INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
$connection->table('ai_conversations')->insert(['id' => 1, 'summary' => 'Resumen previo', 'system_instruction_key' => 'persona']);
$connection->table('ai_conversations')->insert(['id' => 2]);
$controller = new RequestTestController($connection, $requests);
checkRequests($controller->answer(['prompt' => ' ', 'conversation_id' => 1])->getStatusCode() === 400, 'Prompt vacio debe responder 400.');
checkRequests($controller->answer(['prompt' => 'Hola', 'conversation_id' => 999])->getStatusCode() === 404, 'Conversacion inexistente debe responder 404.');
$before = $requests->countAllResults();
$answerDetails = requestResponse('Respuesta del chat');
$answerDetails['usageMetadata'] = ['promptTokenCount' => 10, 'candidatesTokenCount' => 4];
$summaryDetails = requestResponse('Resumen nuevo');
$summaryDetails['usageMetadata'] = ['promptTokenCount' => 20, 'candidatesTokenCount' => 6];
$client->responses = [$answerDetails, $summaryDetails];
$result = json_decode($controller->answer([
    'prompt' => 'Hola',
    'conversation_id' => 1,
    'related_type' => 'otra_entidad',
    'related_id' => 999,
])->getBody(), true, 512, JSON_THROW_ON_ERROR);
checkRequests($result['status'] === 'success' && $result['user_message_id'] && $result['ai_message_id'], 'El chat debe mantener su contrato de mensajes.');
checkRequests($requests->countAllResults() === $before + 2, 'Chat y resumen deben registrar una solicitud cada uno.');
$answerRow = $requests->find($result['ai_request_id']);
$summaryRow = $requests->orderBy('id', 'DESC')->first();
$messages = new AiMesagesModel($connection);
$userMessage = $messages->find($result['user_message_id']);
$aiMessage = $messages->find($result['ai_message_id']);
checkRequests($userMessage['ai_request_id'] === null, 'Los mensajes de usuario no deben referenciar una solicitud de IA.');
checkRequests((int) $aiMessage['ai_request_id'] === (int) $result['ai_request_id'], 'El mensaje IA debe referenciar la solicitud que lo genero.');
checkRequests($aiMessage['text'] === $answerRow['response_content'], 'El texto del mensaje debe corresponder a la respuesta registrada.');
checkRequests((int) $messages->getRecentMessages(1, 1)[0]['ai_request_id'] === (int) $result['ai_request_id'], 'La lectura de mensajes debe incluir la referencia a su solicitud.');
try {
    $messages->insertAiMessage(1, 'Respuesta duplicada', $result['ai_request_id']);
    throw new RuntimeException('No se debe permitir duplicar una respuesta para la misma solicitud.');
} catch (CodeIgniter\Database\Exceptions\DatabaseException $e) {
    checkRequests($messages->countAllResults() === 2, 'La restriccion unica debe conservar los dos mensajes originales.');
}
checkRequests($answerRow['tool_type_request'] === 'conversation_answer' && $summaryRow['tool_type_request'] === 'conversation_summary', 'Distinguir respuesta y resumen en el registro.');
foreach ([$answerRow, $summaryRow] as $relatedRequest) {
    checkRequests($relatedRequest['related_type'] === 'conversation' && (int) $relatedRequest['related_id'] === 1, 'Respuesta y resumen deben usar la conversacion validada, sin aceptar la relacion enviada por el cliente.');
}
$totals = $connection->query(
    'SELECT COUNT(*) AS requests_count, SUM(input_tokens) AS input_tokens, SUM(output_tokens) AS output_tokens FROM ai_requests WHERE related_type = ? AND related_id = ?',
    ['conversation', 1]
)->getRowArray();
checkRequests((int) $totals['requests_count'] === 2 && (int) $totals['input_tokens'] === 30 && (int) $totals['output_tokens'] === 10, 'El acumulado por conversacion debe incluir respuesta y resumen, excluyendo otras entidades.');
$answerBody = json_decode($answerRow['request_json'], true);
checkRequests($answerBody['system_instruction']['parts'][0]['text'] === 'Instruccion general/persona', 'Usar la instruccion de la conversacion.');
checkRequests(strpos($answerBody['system_instruction']['parts'][1]['text'], 'Resumen previo') !== false, 'Incluir el resumen previo en la respuesta.');
$summaryBody = json_decode($summaryRow['request_json'], true);
checkRequests($summaryBody['generationConfig']['maxOutputTokens'] === 3000 && count($summaryBody['contents']) === 3, 'El resumen debe incluir ambos mensajes y su instruccion final.');
$conversation = $connection->table('ai_conversations')->where('id', 1)->get()->getRowArray();
checkRequests($conversation['summary'] === 'Resumen nuevo', 'Debe guardar el resumen generado.');

$messageCount = $connection->table('ai_messages')->countAllResults();
$before = $requests->countAllResults();
$client->responses = [[]];
$result = json_decode($controller->answer(['prompt' => 'Otro mensaje', 'conversation_id' => 1])->getBody(), true, 512, JSON_THROW_ON_ERROR);
checkRequests($result['status'] === 'error' && ! isset($result['ai_message_id']), 'Una respuesta vacia no debe guardarse como mensaje IA.');
checkRequests($connection->table('ai_messages')->countAllResults() === $messageCount + 1 && $requests->countAllResults() === $before + 1, 'No crear mensaje IA ni solicitar resumen al fallar la respuesta.');

$messageCount = $connection->table('ai_messages')->countAllResults();
$before = $requests->countAllResults();
$connection->query("CREATE TRIGGER reject_answer_update BEFORE UPDATE ON ai_requests BEGIN SELECT RAISE(FAIL, 'answer update failure'); END");
$client->responses = [requestResponse('Respuesta no registrada')];
$result = json_decode($controller->answer(['prompt' => 'Fallo de guardado', 'conversation_id' => 1])->getBody(), true, 512, JSON_THROW_ON_ERROR);
$connection->query('DROP TRIGGER reject_answer_update');
checkRequests($result['status'] === 'error' && ! isset($result['ai_message_id']), 'El controlador debe respetar el fallo de registro final.');
checkRequests($connection->table('ai_messages')->countAllResults() === $messageCount + 1 && $requests->countAllResults() === $before + 1, 'No guardar mensaje IA ni generar resumen si falla la actualizacion del registro.');

$conversations = new RequestTestConversationModel($connection);
$conversations->requests = $requests;
$before = $requests->countAllResults();
$summaryResult = $conversations->summarizeConversation(2);
checkRequests($summaryResult['error'] !== '' && $requests->countAllResults() === $before, 'Una conversacion vacia no debe consumir la API de resumen.');
$client->responses = [['error' => ['message' => 'Resumen fallido']]];
$summaryResult = $conversations->summarizeConversation(1);
$conversation = $connection->table('ai_conversations')->where('id', 1)->get()->getRowArray();
checkRequests($summaryResult['error'] !== '' && $conversation['summary'] === 'Resumen nuevo', 'Un resumen fallido no debe sobrescribir el anterior.');
$failedSummary = $requests->orderBy('id', 'DESC')->first();
checkRequests($failedSummary['related_type'] === 'conversation' && (int) $failedSummary['related_id'] === 1, 'Los resumenes fallidos deben conservar la relacion con la conversacion.');

echo "OK: registro pending/success/error, creator_id, relacion y tokens por conversacion, cuerpo sin credenciales, errores de API/BD, validacion y flujo de chat/resumen.\n";
