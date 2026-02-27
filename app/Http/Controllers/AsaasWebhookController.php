<?php

namespace App\Http\Controllers;

use App\Models\Financial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $expected = (string) config('services.asaas.webhook_token');
        $received = (string) $request->header('asaas-access-token');

        if ($expected !== '' && $received !== $expected) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $payload = $request->all();

        $eventId = data_get($payload, 'id');        // id do evento
        $eventType = data_get($payload, 'event');   // tipo do evento
        $payment = data_get($payload, 'payment');   // objeto payment
        $asaasPaymentId = data_get($payment, 'id');

        if (!$eventId || !$eventType) {
            return response()->json(['ok' => false, 'error' => 'invalid_event'], 400);
        }

        // Dedup: ignora evento repetido
        $already = DB::table('asaas_webhook_events')->where('event_id', $eventId)->exists();
        if ($already) {
            return response()->json(['ok' => true, 'duplicated' => true], 200);
        }

        DB::transaction(function () use ($eventId, $eventType, $asaasPaymentId, $payload, $payment) {

            DB::table('asaas_webhook_events')->insert([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'asaas_payment_id' => $asaasPaymentId,
                'payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$asaasPaymentId) return;

            $financial = Financial::where('asaas_payment_id', $asaasPaymentId)->first();
            if (!$financial) return;

            $old = $financial->status;
            $new = data_get($payment, 'status');

            if ($new && $new !== $old) {
                $financial->status = $new;
                $financial->paid_value = data_get($payment, 'value') ?? $financial->paid_value;
                $financial->updated_at = now();
                $financial->save();

                DB::table('financial_history')->insert([
                    'financial_id' => $financial->id,
                    'old_status' => $old,
                    'new_status' => $new,
                    'reason' => 'Webhook Asaas',
                    'changed_by' => 'ASAAS',
                    'metadata' => json_encode([
                        'event_id' => $eventId,
                        'event_type' => $eventType,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json(['ok' => true], 200);
    }
}