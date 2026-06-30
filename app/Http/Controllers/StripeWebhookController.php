<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            Log::warning('Stripe webhook: falta cabecera Stripe-Signature');
            return response('Missing signature', 400);
        }

        try {
            $this->stripeService->handleWebhook($payload, $sigHeader);
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return response('Webhook error', 400);
        }
    }
}
