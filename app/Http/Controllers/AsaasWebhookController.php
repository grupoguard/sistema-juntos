<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AsaasWebhookController extends Controller
{
    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    public function handle(Request $request)
    {
        $payload = $request->all();

        // formato típico:
        // { "id": "...eventId...", "event": "PAYMENT_UPDATED", "payment": { ... } }
        $eventId   = data_get($payload, 'id');
        $eventType = data_get($payload, 'event');
        $payment   = data_get($payload, 'payment', []);
        $asaasPaymentId  = data_get($payment, 'id');
        $asaasCustomerId = data_get($payment, 'customer');

        if (!$eventId || !$eventType) {
            return response()->json(['ok' => false, 'error' => 'invalid_event'], 400);
        }

        // Dedup de evento (at least once)
        $already = DB::table('asaas_webhook_events')->where('event_id', $eventId)->exists();
        if ($already) {
            return response()->json(['ok' => true, 'duplicated' => true], 200);
        }

        DB::transaction(function () use ($eventId, $eventType, $asaasPaymentId, $payload, $payment, $asaasCustomerId) {
            DB::table('asaas_webhook_events')->insert([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'asaas_payment_id' => $asaasPaymentId,
                'payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$asaasPaymentId) {
                return;
            }

            // Busca o financial pelo asaas_payment_id (na sua nova modelagem)
            $fa = DB::table('financial_asaas')->where('asaas_payment_id', $asaasPaymentId)->first();

            if (!$fa) {
                // Se não existe no seu sistema ainda, guarda para você tratar depois
                DB::table('asaas_unmatched_payments')->updateOrInsert(
                    ['asaas_payment_id' => $asaasPaymentId],
                    [
                        'asaas_customer_id' => $asaasCustomerId,
                        'cpf' => null,
                        'reason' => 'webhook_payment_not_found',
                        'payload' => json_encode($payload),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                return;
            }

            $financialId = (int) $fa->financial_id;

            // Monta campos vindos do Asaas
            $newStatus = (string) (data_get($payment, 'status') ?? 'PENDING');
            $newValue  = (float)  (data_get($payment, 'value') ?? 0);

            $dueDateStr = data_get($payment, 'dueDate');
            $newDueDate = $dueDateStr ? Carbon::parse($dueDateStr)->format('Y-m-d') : null;
            $newChargeDate = $newDueDate ? (int) Carbon::parse($newDueDate)->day : null;

            $billingType = data_get($payment, 'billingType');
            $newPaymentMethod = $this->mapPaymentMethod($billingType);

            $isPaid = in_array($newStatus, $this->paidStatuses, true);

            // Carrega estado atual do financial
            $current = DB::table('financial')->where('id', $financialId)->first();
            if (!$current) return;

            $updates = [];

            // Só atualiza o que mudou
            if ($current->status !== $newStatus) {
                $updates['status'] = $newStatus;
            }

            if ((float)$current->value !== $newValue && $newValue > 0) {
                $updates['value'] = $newValue;
            }

            if ($newDueDate && $current->due_date !== $newDueDate) {
                $updates['due_date'] = $newDueDate;
            }

            if ($newChargeDate && (int)$current->charge_date !== $newChargeDate) {
                $updates['charge_date'] = $newChargeDate;
            }

            if ($newPaymentMethod && $current->payment_method !== $newPaymentMethod) {
                $updates['payment_method'] = $newPaymentMethod;
            }

            $newChargePaid = $isPaid ? 1 : 0;
            if ((int)$current->charge_paid !== $newChargePaid) {
                $updates['charge_paid'] = $newChargePaid;
            }

            // paid_value: só preenche quando pago
            $currentPaidValue = $current->paid_value !== null ? (float)$current->paid_value : null;
            $desiredPaidValue = $isPaid ? $newValue : null;

            if ($currentPaidValue !== $desiredPaidValue) {
                $updates['paid_value'] = $desiredPaidValue;
            }

            if (!empty($updates)) {
                $updates['updated_at'] = now();
                DB::table('financial')->where('id', $financialId)->update($updates);
            }

            // financial_history só quando o status mudou
            if ($current->status !== $newStatus) {
                DB::table('financial_history')->insert([
                    'financial_id' => $financialId,
                    'old_status' => $current->status,
                    'new_status' => $newStatus,
                    'reason' => 'Webhook Asaas',
                    'changed_by' => 'ASAAS',
                    'metadata' => json_encode([
                        'event_id' => $eventId,
                        'event_type' => $eventType,
                        'asaas_payment_id' => $asaasPaymentId,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Atualiza também o financial_asaas (links/pix/externalReference) só se vier
            $faUpdates = [];
            $extRef = data_get($payment, 'externalReference');
            if ($extRef && $fa->external_reference !== $extRef) $faUpdates['external_reference'] = $extRef;

            $inv = data_get($payment, 'invoiceUrl');
            if ($inv && $fa->invoice_url !== $inv) $faUpdates['invoice_url'] = $inv;

            $bs = data_get($payment, 'bankSlipUrl');
            if ($bs && $fa->bank_slip_url !== $bs) $faUpdates['bank_slip_url'] = $bs;

            $pixPayload = data_get($payment, 'pix.payload');
            if ($pixPayload && $fa->pix_qr_code !== $pixPayload) $faUpdates['pix_qr_code'] = $pixPayload;

            $pixUrl = data_get($payment, 'pix.qrCode.url');
            if ($pixUrl && $fa->pix_qr_code_url !== $pixUrl) $faUpdates['pix_qr_code_url'] = $pixUrl;

            if ($asaasCustomerId && $fa->asaas_customer_id !== $asaasCustomerId) $faUpdates['asaas_customer_id'] = $asaasCustomerId;

            if (!empty($faUpdates)) {
                $faUpdates['updated_at'] = now();
                DB::table('financial_asaas')->where('id', $fa->id)->update($faUpdates);
            }
        });

        return response()->json(['ok' => true], 200);
    }

    private function mapPaymentMethod(?string $billingType): string
    {
        return match ($billingType) {
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'BOLETO' => 'BOLETO',
            default => 'BOLETO',
        };
    }
}