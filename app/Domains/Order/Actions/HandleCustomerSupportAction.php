<?php

namespace App\Domains\Order\Actions;

use App\Domains\Order\Models\Order;
use App\Services\AiClassificationService;
use App\Jobs\Order\GenerateAiSummaryJob;

class HandleCustomerSupportAction
{
    protected $aiService;

    public function __construct(AiClassificationService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function execute(Order $order, string $customerMessage): array
    {
        // 1. استدعاء ذكاء الآلة لتحليل الرسالة
        $aiReply = $this->aiService->classifyMessage($order->order_number, $customerMessage);

        // 2. اتخاذ القرار وإطلاق الـ Job في الخلفية إذا كانت الحالة حرجة
        if (str_contains($aiReply, '[CRITICAL]')) {
            GenerateAiSummaryJob::dispatch($order, $customerMessage, $aiReply);

            return [
                'status' => 'escalated',
                'message' => 'Your issue has been marked as high priority and escalated to human support.',
                'ai_analysis' => $aiReply
            ];
        }

        // 3. الحالة طبيعية
        return [
            'status' => 'resolved',
            'message' => $aiReply
        ];
    }
}