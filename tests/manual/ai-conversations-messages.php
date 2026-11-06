<?php

/**
 * Verificacion aislada de paginacion; solo crea datos en SQLite en memoria.
 * Ejecutar: D:/xampp/php/php.exe -d extension=sqlite3 tests/manual/ai-conversations-messages.php
 */

use App\Controllers\Api\AiConversations;
use App\Models\AiConversationModel;
use App\Models\AiMesagesModel;
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

final class MessagesTestController extends AiConversations
{
    public function __construct(ConnectionInterface $connection)
    {
        $this->aiConversationModel = new AiConversationModel($connection);
        $this->aiMesagesModel = new AiMesagesModel($connection);
    }

    public function prepareRequest(array $query): void
    {
        $this->request = new IncomingRequest(new App(), new URI('http://localhost/accn/'), null, new UserAgent());
        $this->request->setGlobal('get', $query);
        $this->response = new Response(new App());
    }
}

function checkMessages(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

$connection = Database::connect([
    'DBDriver' => 'SQLite3',
    'database' => ':memory:',
    'DBPrefix' => '',
    'DBDebug' => true,
], false);
$connection->query('CREATE TABLE ai_conversations (id INTEGER PRIMARY KEY, deleted_at TEXT NULL)');
$connection->query('CREATE TABLE ai_messages (id INTEGER PRIMARY KEY, conversation_id INTEGER, role TEXT, text TEXT, created_at TEXT, deleted_at TEXT NULL)');
$connection->query('INSERT INTO ai_conversations (id) VALUES (1), (2)');

for ($id = 1; $id <= 120; $id++) {
    $createdAt = $id === 1 ? '2026-10-04 12:00:00' : sprintf('2026-10-03 12:%02d:00', intdiv($id - 1, 10));
    $connection->table('ai_messages')->insert([
        'id' => $id,
        'conversation_id' => 1,
        'role' => 'user',
        'text' => 'Mensaje ' . $id,
        'created_at' => $createdAt,
        'deleted_at' => $id === 111 ? '2026-10-04 13:00:00' : null,
    ]);
}
$connection->query("INSERT INTO ai_messages (id, conversation_id, created_at) VALUES (201, 2, '2026-10-05 12:00:00')");
$expectedIds = array_values(array_filter(array_merge([1], range(120, 2)), static fn ($id) => $id !== 111));
$controller = new MessagesTestController($connection);

foreach ([0, 50, 100, 150] as $offset) {
    $controller->prepareRequest($offset === 0 ? [] : ['offset' => (string) $offset]);
    $response = $controller->getMessages('1');
    $data = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    checkMessages($response->getStatusCode() === 200, 'La pagina debe responder 200.');
    checkMessages($data['limit'] === 50 && $data['offset'] === $offset, 'Valores predeterminados y offset incorrectos.');
    checkMessages(array_map('intval', array_column($data['messages'], 'id')) === array_slice($expectedIds, $offset, 50), 'Orden, aislamiento, borrado logico o pagina incorrectos.');
}

$controller->prepareRequest(['limit' => '3', 'offset' => '2']);
$data = json_decode($controller->getMessages('1')->getBody(), true, 512, JSON_THROW_ON_ERROR);
checkMessages(array_map('intval', array_column($data['messages'], 'id')) === array_slice($expectedIds, 2, 3), 'No se respeto limit personalizado.');

foreach ([['limit' => '0'], ['limit' => '-1'], ['limit' => '1.5'], ['limit' => 'abc'], ['limit' => ['50']], ['offset' => '-1'], ['offset' => 'abc'], ['offset' => ['0']]] as $query) {
    $controller->prepareRequest($query);
    checkMessages($controller->getMessages('1')->getStatusCode() === 400, 'La paginacion invalida debe responder 400.');
}
$controller->prepareRequest([]);
checkMessages($controller->getMessages('0')->getStatusCode() === 400, 'ID cero debe responder 400.');
checkMessages($controller->getMessages('999')->getStatusCode() === 404, 'Conversacion inexistente debe responder 404.');
$connection->query("UPDATE ai_conversations SET deleted_at = '2026-10-04 13:00:00' WHERE id = 1");
checkMessages($controller->getMessages('1')->getStatusCode() === 404, 'Conversacion borrada debe responder 404.');

$route = 'api/conversations/get_messages/([0-9]+)';
$routes = Services::routes();
checkMessages(isset($routes->getRoutes('get')[$route]), 'Falta la ruta GET.');
checkMessages($routes->getRoutes('get')[$route] === '\\App\\Controllers\\Api\\AiConversations::getMessages/$1', 'La ruta debe usar AiConversations.');
checkMessages($routes->getRoutesOptions($route, 'get')['filter'] === ['apiCors', 'auth'], 'Faltan los filtros de acceso y CORS.');
checkMessages(in_array('api/conversations/get_messages', (new Config\Acl())->loggedRoutes, true), 'Falta el permiso en ACL.');
checkMessages(! isset($routes->getRoutes('get')['api/ai_generate/get_messages/([0-9]+)']), 'La ruta anterior debe eliminarse.');
checkMessages(! method_exists(App\Controllers\Api\AiGenerate::class, 'getMessages'), 'getMessages debe salir de AiGenerate.');

echo "OK: paginas, limite, orden por fecha/ID, aislamiento, borrado logico, validacion, ruta y ACL.\n";
