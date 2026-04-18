<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_agents', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->json('assigned_communities')->nullable();
            $table->json('assigned_centers')->nullable();
            $table->date('join_date')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('field_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_id')->unique();
            $table->unsignedBigInteger('agent_id');
            $table->date('visit_date');
            $table->string('center', 50)->nullable();
            $table->string('community', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('extension_agents')->onDelete('restrict');
            $table->index(['agent_id', 'visit_date', 'center']);
        });

        Schema::create('visit_farmers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->unsignedBigInteger('farmer_id')->nullable();
            $table->string('farmer_name')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('field_visits')->onDelete('cascade');
        });

        Schema::create('visit_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->string('topic', 50);
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('field_visits')->onDelete('cascade');
        });

        Schema::create('visit_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_id');
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->foreign('visit_id')->references('id')->on('field_visits')->onDelete('cascade');
        });

        Schema::create('training_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('title');
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->string('center', 50)->nullable();
            $table->json('facilitators')->nullable();
            $table->text('topics_covered')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();
        });

        Schema::create('training_attendees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('farmer_id')->nullable();
            $table->string('farmer_name')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('training_events')->onDelete('cascade');
        });

        Schema::create('training_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('material_name');
            $table->decimal('quantity_distributed', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('training_events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_materials');
        Schema::dropIfExists('training_attendees');
        Schema::dropIfExists('training_events');
        Schema::dropIfExists('visit_photos');
        Schema::dropIfExists('visit_topics');
        Schema::dropIfExists('visit_farmers');
        Schema::dropIfExists('field_visits');
        Schema::dropIfExists('extension_agents');
    }
};
