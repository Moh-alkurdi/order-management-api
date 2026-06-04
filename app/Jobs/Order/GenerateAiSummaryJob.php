<?php

namespace App\Jobs\Order;

use App\Domains\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Illuminate\Support\Facades\Log;

class GenerateAiSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $customerMessage;
    protected $aiAnalysis;
    
    /**
     * Fault Tolerance Configurations
     */
    public $tries = 3;
    public $backoff = [5, 10, 20];

    // نمرر للـ Job الطلب، ورسالة العميل المتذمر، وتحليل الذكاء الاصطناعي
    public function __construct(Order $order, string $customerMessage, string $aiAnalysis)
    {
        $this->order = $order;
        $this->customerMessage = $customerMessage;
        $this->aiAnalysis = $aiAnalysis;
    }

    public function handle(): void
    {
        // محاكاة إرسال إيميل عاجل عالي الأهمية لفريق الدعم البشري في ألمانيا
        $emailContent = "
        🚨 [CRITICAL SUPPORT TICKED]
        Order Number: {$this->order->order_number}
        Customer Message: '{$this->customerMessage}'
        AI Analysis Verdict: {$this->aiAnalysis}
        Action Required: Please contact the logistics company immediately!
        ";

        // سنقوم بطباعتها في الـ Log كإشعار عاجل تم في الخلفية دون تعطيل المستخدم
        Log::alert($emailContent);
    }

    /**
     * Dead Letter Queue (DLQ) Hook
     * دالة الطوارئ عند فشل الـ Job بعد 3 محاولات لإرسال شكوى العميل
    */
    public function failed(Throwable $exception): void
    {
        // نصل للمتغيرات المحمية من داخل الكلاس نفسه بأمان تلو التعديل
        Log::critical("🚨 DLQ ALERT: Support ticket AI summary failed for Order: {$this->order->order_number}. Message saved in DLQ. Reason: {$exception->getMessage()}");
    }
}