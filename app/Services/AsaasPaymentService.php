<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AsaasPaymentService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.asaas.api_url'), '/');
        $this->apiKey = (string) config('services.asaas.api_key');

        if (blank($this->apiKey)) {
            throw new RuntimeException('ASAAS_API_KEY não configurada.');
        }
    }

    protected function request(string $method, string $uri, array $payload = []): array
    {
        $method = strtoupper($method);
        $url = $this->baseUrl . '/' . ltrim($uri, '/');

        $client = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'access_token' => $this->apiKey,
            ]);

        $options = [];

        if (in_array($method, ['GET', 'DELETE'], true)) {
            if (!empty($payload)) {
                $options['query'] = $payload;
            }
        } else {
            $options['json'] = $payload;
        }

        $response = $client->send($method, $url, $options);

        if ($response->failed()) {
            \Log::error('Erro Asaas', [
                'method' => $method,
                'url' => $url,
                'payload' => $payload,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $errors = collect($response->json('errors', []))
                ->pluck('description')
                ->filter()
                ->implode(' | ');

            throw new \RuntimeException(
                $errors ?: ($response->json('message') ?? $response->body() ?? 'Erro ao comunicar com o Asaas.')
            );
        }

        return $response->json() ?? [];
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', "payments/{$paymentId}");
    }

    public function getBillingInfo(string $paymentId): array
    {
        return $this->request('GET', "payments/{$paymentId}/billingInfo");
    }

    public function getViewingInfo(string $paymentId): array
    {
        return $this->request('GET', "payments/{$paymentId}/viewingInfo");
    }

    public function updatePayment(string $paymentId, array $payload): array
    {
        return $this->request('PUT', "payments/{$paymentId}", $payload);
    }

    public function deletePayment(string $paymentId): array
    {
        return $this->request('DELETE', "payments/{$paymentId}");
    }

    public function restorePayment(string $paymentId): array
    {
        return $this->request('POST', "payments/{$paymentId}/restore");
    }

    public function receiveInCash(string $paymentId, array $payload): array
    {
        return $this->request('POST', "payments/{$paymentId}/receiveInCash", $payload);
    }

    public function undoReceivedInCash(string $paymentId): array
    {
        return $this->request('POST', "payments/{$paymentId}/undoReceivedInCash");
    }
}