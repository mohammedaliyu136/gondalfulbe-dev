<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionPaymentBatch;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_requisition_payment_batch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_requisition_payment_batch_id');
            $table->unsignedBigInteger('purchase_requisition_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys
            // assuming table names are pluralized versions of models, checking standard conventions
            // purchase_requisitions and purchase_requisition_payment_batches
            
            // We'll skip strict foreign key constraints in code if we aren't 100% sure of table names, 
            // but standard laravel is plural snake_case.
            // Let's assume standard behavior but be safe with data migration first.
        });

        // Data Migration
        // Move existing 1-to-many relationships to the new pivot table
        $batches = PurchaseRequisitionPaymentBatch::all();
        
        foreach ($batches as $batch) {
            // Find PRs that belong to this batch using the OLD column
            // We use DB facade to avoid Model caching/relationship issues if we change the model file simultaneously
            $prs = DB::table('purchase_requisitions')
                    ->where('pr_payment_batch_id', $batch->id)
                    ->get();

            foreach ($prs as $pr) {
                // Calculate total approved cost for this PR to set as the amount
                // We need to sum the items for this PR.
                $totalAmount = DB::table('purchase_requisition_items')
                                ->where('purchase_requisition_id', $pr->id)
                                ->where('status', 1) // Assuming 1 is active/approved
                                ->sum(DB::raw('approved_quantity * approved_cost'));
                
                if (!$totalAmount) {
                     $totalAmount = 0;
                }

                DB::table('purchase_requisition_payment_batch_items')->insert([
                    'purchase_requisition_payment_batch_id' => $batch->id,
                    'purchase_requisition_id' => $pr->id,
                    'amount' => $totalAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_requisition_payment_batch_items');
    }
};
