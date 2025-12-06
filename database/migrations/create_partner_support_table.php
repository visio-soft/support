<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('partner_support')) {
            Schema::create('partner_support', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('park_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('subject');
                $table->text('content');
                $table->string('status')->default('open')->index();
                $table->string('priority')->default('normal')->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Add foreign key constraints if needed
                // $table->foreign('park_id')->references('id')->on('parks')->onDelete('cascade');
                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                // $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
                // $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_support');
    }
};
