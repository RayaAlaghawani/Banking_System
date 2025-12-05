<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // تم إضافة عمود رمز التحقق هنا
            $table->string('code')->index();
            $table->timestamp('expires_at');
            $table->timestamps();

            // إضافة مفتاح فريد لـ user_id لتجنب رموز تحقق متعددة لنفس المستخدم
            $table->unique(['user_id', 'code']);
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
