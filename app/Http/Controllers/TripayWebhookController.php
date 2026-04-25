<?php

namespace App\Http\Controllers;

use App\Services\Payments\TripayWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TripayWebhookController extends Controller
{
    public function __construct(
        private readonly TripayWebhookService $tripayWebhookService
    ) {
    }

    public function shop(Request $request): Response
    {
        $result = $this->tripayWebhookService->handleShopCallback($request);

        return response($result['message'], $result['http_status']);
    }
}
