<?php

namespace App\Services;

use App\Models\OldFinancial;
use App\Models\OrderPrice;
use App\Models\Order;
use App\Models\Client;
use App\Helpers\FormatHelper;
use Illuminate\Support\Facades\Log;

class OldFinancialImportService
{
    public function handle()
    {
        OldFinancial::query()->distinct()->orderBy('nome')->chunk(500, function ($records) {
            foreach ($records as $old) {
                try {
                    $name = $old->nome;
                    $totalValue = $old->valor_plano;
                    $productValue = $old->valor_original;
                    $dependentTotal = $old->valor_adicionais;
                    $chargeDate = $old->data_vencimento;
                    $chargeType = $old->billingType;

                    // Verificar se o cliente existe
                    $orderId = $this->getOrderId($name);
                    if (!$orderId) {
                        Log::warning("Cliente/Pedido não encontrado para: {$name}");
                        continue;
                    }

                    $orderPrice = $this->getOrderPrice($orderId);
                    if (!$orderPrice) {
                        Log::warning("OrderPrice não encontrado para order_id: {$orderId}");
                        continue;
                    }

                    $dependentsCount = $orderPrice->dependents_count ?? 0;

                    // Atualizar Order
                    Order::updateOrCreate(
                        ['id' => $orderId],
                        [
                            'charge_type' => $chargeType,
                            'charge_date' => $chargeDate
                        ]
                    );

                    // Preparar dados para atualização de preços
                    if ($dependentsCount > 0 && $dependentTotal > 0) {                        
                        $dependentValue = ($dependentTotal / $dependentsCount);

                        $updatePrices = [
                            'product_value' => $productValue / 100,
                            'dependent_value' => $dependentValue / 100,
                        ];

                    } else {
                        $updatePrices = [
                            'product_value' => $totalValue / 100,
                            'dependent_value' => 0,
                        ];
                    }

                    // Corrigir o updateOrCreate - usar array para a condição where
                    OrderPrice::updateOrCreate(
                        ['order_id' => $orderId],
                        $updatePrices
                    );

                } catch (\Exception $e) {
                    Log::error("Erro ao processar registro {$old->id}: " . $e->getMessage());
                    continue;
                }
            }
        });
    }

    public function getOrderId($name)
    {
        try {
            $client = Client::where('name', $name)->first();
            if (!$client) {
                return null;
            }

            $order = Order::where('client_id', $client->id)->first();
            return $order ? $order->id : null;

        } catch (\Exception $e) {
            Log::error("Erro ao buscar order_id para cliente {$name}: " . $e->getMessage());
            return null;
        }
    }

    public function getOrderPrice($orderId)
    {
        try {
            return OrderPrice::where('order_id', $orderId)->first();
        } catch (\Exception $e) {
            Log::error("Erro ao buscar OrderPrice para order_id {$orderId}: " . $e->getMessage());
            return null;
        }
    }
}