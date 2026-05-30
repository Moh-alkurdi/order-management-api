<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // اسم الموديل الذي تم تعديله (مثلاً Order)
            $table->unsignedBigInteger('auditable_id'); // رقم السجل (Order ID)
            $table->string('event'); // نوع العملية: created, updated, deleted
            $table->json('old_values')->nullable(); // البيانات قبل التعديل
            $table->json('new_values')->nullable(); // البيانات بعد التعديل
            $table->unsignedBigInteger('user_id')->nullable(); // من قام بالعملية (إن وجد)
            $table->timestamps();

            // فهارس لتسريع الاستعلام مستقبلاً في لوحة التحكم
            $table->index(['auditable_type', 'auditable_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
