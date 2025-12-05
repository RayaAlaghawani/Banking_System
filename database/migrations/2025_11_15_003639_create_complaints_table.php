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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('government_agencie_id')
                ->nullable()
                ->constrained('government_agencies')
                ->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('attachment_path')->nullable()->comment('مسار حفظ الملف المرفق (صورة/ملف)');
            $table->enum('status', ['Pending', 'In Progress', 'Resolved', 'Rejected'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
