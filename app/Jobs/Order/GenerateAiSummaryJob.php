<?php

namespace App\Jobs\Order;

use App\Domains\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $customerMessage;
    protected $aiAnalysis;

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
}