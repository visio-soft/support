<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('partner_support_replies')) {
            Schema::create('partner_support_replies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('partner_support_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->text('content');
                $table->boolean('is_admin_reply')->default(false)->index();
                $table->boolean('is_internal_note')->default(false)->index();
                $table->json('attachments')->nullable();
                $table->timestamps();
                $table->softDeletes();
     

                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_support_replies');
    }
};
