<?php

namespace Tests\Feature\Domains\Order;

use App\Domains\Order\Actions\HandleCustomerSupportAction;
use App\Jobs\Order\GenerateAiSummaryJob;
use App\Domains\Order\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AiSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_critical_customer_message_triggers_ai_escalation_and_dispatches_job()
    {
        // 1. إخبار Laravel بأننا نريد مراقبة الـ Queues وهمياً دون إطلاقها حقيقة
        Queue::fake();

        // 2. تزييف رد الـ Groq API (Http::fake)
        // سنقنع النظام بأن الـ AI قام بتحليل الرسالة ورد بكلمة [CRITICAL]
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '[CRITICAL] Shipping delay detected. Order is pending for 14 days.'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // 3. إنشاء طلب وهمي في قاعدة البيانات لفحصه
        $order = Order::create([
            'user_id' => 1,
            'order_number' => 'ORD-999-AI',
            'total_amount' => 200.00,
            'status' => 'pending'
        ]);

        // 4. استدعاء الـ Action المسؤول عن الدعم الفني والذكاء الاصطناعي
        $action = app(HandleCustomerSupportAction::class);
        $result = $action->execute($order, "My order is delayed for 14 days!");

        // 5. الفحوصات الهندسية الصارمة (Assertions):
        
        // الفحص أ: هل عاد الـ Action بالـ Status الصحيح (escalated)؟
        $this->assertEquals('escalated', $result['status']);
        $this->assertStringContainsString('[CRITICAL]', $result['ai_analysis']);

        // الفحص ب: هل تأكد النظام من إرسال الـ Job (GenerateAiSummaryJob) إلى الـ Redis Queue؟
       Queue::assertPushed(GenerateAiSummaryJob::class);
    }
}