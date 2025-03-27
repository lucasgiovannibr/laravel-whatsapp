<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('message_id')->nullable()->index();
            $table->string('from')->index();
            $table->string('to')->index();
            $table->text('body')->nullable();
            $table->string('type')->default('text');
            $table->boolean('has_media')->default(false);
            $table->string('media_type')->nullable();
            $table->string('media_url')->nullable();
            $table->text('metadata')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_read')->default(false);
            $table->string('status')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['from', 'to']);
            $table->index(['created_at']);
        });

        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('content');
            $table->text('description')->nullable();
            $table->json('placeholders')->nullable();
            $table->string('category')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['name', 'active']);
            $table->index(['category']);
        });

        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'is_active']);
        });

        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('number')->index();
            $table->string('name')->nullable();
            $table->text('profile_picture_url')->nullable();
            $table->boolean('is_business')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
            
            $table->unique(['session_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_sessions');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_messages');
    }
}; 