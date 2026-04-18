<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('sponsor_code')->unique();
            $table->string('organization_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('organization_type', 20)->default('NGO');
            $table->string('country')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by_admin')->default(0);
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sponsor_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->unsignedBigInteger('sponsor_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 14, 2)->nullable();
            $table->string('status', 20)->default('Active');
            $table->json('objectives')->nullable();
            $table->json('focus_areas')->nullable();
            $table->unsignedBigInteger('created_by')->default(0);
            $table->timestamps();

            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
        });

        Schema::create('project_farmers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('farmer_id');
            $table->date('enrolled_date')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'farmer_id']);
            $table->foreign('project_id')->references('id')->on('sponsor_projects')->onDelete('cascade');
            $table->foreign('farmer_id')->references('id')->on('venders')->onDelete('cascade');
        });

        Schema::create('project_agents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('agent_id');
            $table->date('enrolled_date')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'agent_id']);
            $table->foreign('project_id')->references('id')->on('sponsor_projects')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('extension_agents')->onDelete('cascade');
        });

        Schema::create('project_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('mcc_name', 50);
            $table->date('enrolled_date')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'mcc_name']);
            $table->foreign('project_id')->references('id')->on('sponsor_projects')->onDelete('cascade');
        });

        Schema::create('project_cooperatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('cooperative_id');
            $table->date('enrolled_date')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'cooperative_id']);
            $table->foreign('project_id')->references('id')->on('sponsor_projects')->onDelete('cascade');
            $table->foreign('cooperative_id')->references('id')->on('cooperatives')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_cooperatives');
        Schema::dropIfExists('project_centers');
        Schema::dropIfExists('project_agents');
        Schema::dropIfExists('project_farmers');
        Schema::dropIfExists('sponsor_projects');
        Schema::dropIfExists('sponsors');
    }
};
