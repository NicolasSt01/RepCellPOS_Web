<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout = 15;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.base_url', ''), '/');
        $this->apiKey = config('services.evolution.api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    public function ping(): array
    {
        return $this->get('/');
    }

    public function createInstance(string $instanceName): array
    {
        return $this->post('/instance/create', [
            'instanceName' => $instanceName,
            'integration' => 'WHATSAPP-BAILEYS',
            'qrcode' => true,
        ]);
    }

    public function connectInstance(string $instance): array
    {
        return $this->post('/instance/connect', [
            'instanceName' => $instance,
        ]);
    }

    public function getQrCode(string $instance): array
    {
        return $this->get("/instance/qrcode/{$instance}");
    }

    public function getConnectionState(string $instance): array
    {
        return $this->get("/instance/connectionState/{$instance}");
    }

    public function listInstances(): array
    {
        return $this->get('/instance/fetchInstances');
    }

    public function logout(string $instance): array
    {
        return $this->delete("/instance/logout/{$instance}");
    }

    public function deleteInstance(string $instance): array
    {
        return $this->delete("/instance/delete/{$instance}");
    }

    public function sendText(string $instance, string $number, string $text): array
    {
        return $this->post("/message/sendText/{$instance}", [
            'number' => $number,
            'text' => $text,
            'delay' => 1200,
        ]);
    }

    public function fetchGroups(string $instance, bool $withParticipants = true): array
    {
        $query = $withParticipants ? '?getParticipants=true' : '';
        return $this->get("/group/fetchAll/{$instance}{$query}");
    }

    public function fetchContacts(string $instance): array
    {
        return $this->post("/chat/findContacts/{$instance}", [
            'where' => new \stdClass(),
        ]);
    }

    public function fetchChats(string $instance): array
    {
        return $this->post("/chat/findChats/{$instance}", [
            'where' => new \stdClass(),
        ]);
    }

    protected function get(string $endpoint): array
    {
        return $this->request('get', $endpoint);
    }

    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, $data);
    }

    protected function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = "{$this->baseUrl}{$endpoint}";

        try {
            $http = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout);

            $response = match ($method) {
                'get' => $http->get($url),
                'post' => $http->post($url, $data),
                'delete' => $http->delete($url),
                default => throw new Exception("Método HTTP no soportado: {$method}"),
            };

            Log::info('[EvolutionAPI] Request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            $body = $response->json() ?? [];

            if (!$response->successful()) {
                Log::warning('[EvolutionAPI] Error', [
                    'status' => $response->status(),
                    'body' => $body,
                ]);
            }

            return array_merge(['_status' => $response->status()], $body);
        } catch (Exception $e) {
            Log::error('[EvolutionAPI] Exception', ['error' => $e->getMessage()]);
            return ['_status' => 0, '_error' => $e->getMessage()];
        }
    }
}
