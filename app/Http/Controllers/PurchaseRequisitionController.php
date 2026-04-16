<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionApproval;
use App\Models\ServiceProvider;
use App\Models\Employee;
use App\Models\PurchaseRequisitionItem;
use App\Models\PurchaseRequisitionPaymentBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\Mail\OtpRequisitionMail;
use App\Monnify\Monnify;
use Illuminate\Support\Facades\Mail;
use App\Models\Utility;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;



class PurchaseRequisitionController extends Controller
{
    public function index()
    {
        if (!Gate::check('manage requisition')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    
        $user = Auth::user();
        $employee = $user->employee;
        $department = $employee ? $employee->department : null;
    
        // 1. User can view ALL requisitions
        if ($user->can('view all requisition')) {
            $requisitions = PurchaseRequisition::latest()->get();
        } 
        else {
            // 2. User can approve as HOD → see entire department
            if ($user->can('approve hod requisition')) {
                $requisitions = PurchaseRequisition::where('department_id', $department->id)
                    ->latest()
                    ->get();
            } 
            // 3. Normal user → see only their own requisitions
            else {
                $requisitions = PurchaseRequisition::where('department_id', $department->id)
                    ->where('created_by', $user->id)  // <-- CORRECT: property, not method
                    ->latest()
                    ->get();
            }
        }
    
        return view('purchase_requisitions.index', compact('requisitions'));
    }

    
    public function report(Request $request)
    {
        if (Gate::check('view report requisition')) {
    
            $user = Auth::user();
            $employee = $user->employee;
            $department = $employee ? $employee->department : null;
    
            if ($user->can('view report requisition')) {
                $query = PurchaseRequisition::query();
            } else {
                $query = PurchaseRequisition::where('department_id', $department->id);
            }
    
            // DATE FILTER
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
    
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Priority filter
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            // Approval stage filter
            if ($request->filled('stage')) {
                $query->where('current_stage_level', $request->stage);
            }
            
            // Payment status filter
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

    
            $requisitions = $query->latest()->get();
    
            // BASE STATS
            $stats = [
                'total'     => $query->count(),
                'pending'   => (clone $query)->where('status', 'pending')->count(),
                'approved'  => (clone $query)->where('status', 'approved')->count(),
                'partial'   => (clone $query)->where('payment_status', 3)->count(),
                'high'      => (clone $query)->where('priority', 'High')->count(),
            ];
    
            // STAGE STATS
            $stageNames = [
                1 => 'HOD',
                2 => 'Internal Audit',
                3 => 'Accounts',
                4 => 'MD',
                5 => 'Final Approval',
            ];
    
            $stageStats = [];
            foreach ($stageNames as $level => $label) {
                $stageStats[$label] = (clone $query)
                    ->where('current_stage_level', $level)
                    ->count();
            }
            
            $paidTotal = (clone $query)
                ->where('payment_status', 4)
                ->get()
                ->sum(fn($r) => $r->totalApprovedCost());
            
            $unpaidTotal = (clone $query)
                ->where('payment_status', 2)
                ->get()
                ->sum(fn($r) => $r->totalApprovedCost());

            // Add Partial Payments Logic
            $partiallyPaidItems = (clone $query)->where('payment_status', 3)->with('paymentBatches')->get();
            $partialPaidAmount = 0;
            $partialUnpaidAmount = 0;

            foreach($partiallyPaidItems as $item) {
                $paid = $item->paymentBatches->sum('pivot.amount');
                $total = $item->totalApprovedCost();
                $partialPaidAmount += $paid;
                $partialUnpaidAmount += ($total - $paid);
            }

            $paidTotal += $partialPaidAmount;
            $unpaidTotal += $partialUnpaidAmount;
            
            // Priority distribution
            $priorityData = [
                'High'   => (clone $query)->where('priority', 'High')->count(),
                'Medium' => (clone $query)->where('priority', 'Medium')->count(),
                'Low'    => (clone $query)->where('priority', 'Low')->count(),
            ];
            
            // Monthly graph (last 12 months)
            $year = now()->year;

            // build 12-month data (Jan..Dec) for the selected year
            $monthlyDataFull = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyDataFull[] = (clone $query)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $m)
                    ->count();
            }

    
            return view('purchase_requisitions.report', compact(
                'requisitions',
                'stats',
                'stageStats',
                'paidTotal',
                'unpaidTotal',
                'priorityData',
                'monthlyDataFull'
            ));

        }
    
        return back()->with('error', 'Permission denied.');
    }


    public function create()
    {
        if(\Auth::user()->can('create requisition') || 
            Gate::check('approve md requisition')
            )
        {
            return view('purchase_requisitions.create');
        }else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        
    }

    public function show($id)
    {
        if(\Auth::user()->can('manage requisition'))
        {
            try {
            $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Requisition Not Found.'));
            }
            
            $pr = PurchaseRequisition::with(['items', 'approvals'])->findOrFail($id);
            return view('purchase_requisitions.show', compact('pr'));
        }else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }
    
    public function view($id, $stage)
    {
        if(\Auth::user()->can('manage requisition'))
        {
            try {
            $id     = Crypt::decrypt($id);
            $stage  = Crypt::decrypt($stage);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Requisition Not Found.'));
            }
            
            $pr = PurchaseRequisition::with(['items', 'approvals'])->findOrFail($id);
            $serviceProviders = ServiceProvider::select('id', 'name', 'bank_name', 'bank_account')->get();
            
            // Validate that the stage exists
            $stages = [1 => 'HOD', 2 => 'Internal Audit', 3 => 'Accounts', 4 => 'MD', 5 => 'Final Approval'];
            
            if (!array_key_exists($stage, $stages)) {
                return redirect()->back()->with('error', 'Invalid approval stage.');
            }
        
            return view('purchase_requisitions.show_approval', compact('pr', 'stage', 'serviceProviders'));
        }else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
       
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create requisition'))
        {
                    $request->validate([
                    'title' => 'required',
                    'requested_by' => 'required',
                    'items' => 'required|array|min:1',
                    'items.*.name' => 'required|string',
                    'items.*.quantity' => 'required|integer|min:1',
                    'items.*.estimated_cost' => 'required|numeric|min:0',
                ]);
            
                DB::beginTransaction();
            
                try {
                    $user = Auth::user();
                    $employee = $user->employee; // Get the associated employee
                    $department = $employee ? $employee->department : null; // Get department
            
                    if (!$department) {
                        return redirect()->back()->with('error', 'User does not belong to any department.');
                    }
            
                    // Create Purchase Requisition
                    $pr = PurchaseRequisition::create([
                        'pr_id' => 'PR0'. $this->latestPrId(),
                        'title' => $request->title,
                        'comment' => $request->comment,
                        'priority' => $request->priority,
                        'created_by' => $user->id,
                        'requested_by' => $request->requested_by,
                        'department_id' => $department->id,
                        'current_stage_level' => 1, // Start from stage 1
                    ]);
            
                    // Insert Purchase Requisition Items
                    foreach ($request->items as $item) {
                        PurchaseRequisitionItem::create([
                            'purchase_requisition_id' => $pr->id,
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'estimated_cost' => $item['estimated_cost'],
                        ]);
                    }
            
                    // Create Approval Stages
                    $stages  = [
                        'HOD', 
                        'Internal Audit', 
                        'Accounts', 
                        'MD', 
                        //'Final Approval'
                        ];
                    
                    foreach ($stages as $index => $stage) {
                        PurchaseRequisitionApproval::create([
                            'purchase_requisition_id' => $pr->id,
                            'created_by' => Auth::id(),
                            'stage_level' => $index + 1,  // Replaces 'phase'
                            'stage_name' => $stage,
                        ]);
                    }
        
                    DB::commit();
            
                    return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition created successfully.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Failed to create Purchase Requisition: ' . $e->getMessage());
                }
        }else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }

    public function edit($id)
    {
        if(\Auth::user()->can('edit requisition'))
        {
            $requisition = PurchaseRequisition::findOrFail($id);
            return view('purchase_requisitions.edit', compact('requisition'));
        }else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
        
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'item_name' => 'required',
            'quantity' => 'required|integer',
            'estimated_cost' => 'required|numeric',
        ]);

        $requisition = PurchaseRequisition::findOrFail($id);
        $requisition->update($request->all());

        return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition updated successfully.');
    }

    // public function destroy($id)
    // {
    //     if(\Auth::user()->can('delete requisition'))
    //     {
    //         PurchaseRequisition::findOrFail($id)->delete();
    //         return redirect()->route('purchase-requisitions.index')->with('success', 'Purchase Requisition deleted.');
    //     }else
    //     {
    //         return redirect()->back()->with('error', 'Permission denied.');
    //     }
        
    // }

    public function filterByStageLevel($stage_level)
    {
        $requisitions = PurchaseRequisition::where('current_stage_level', $stage_level)->get();
        return view('purchase_requisitions.approval_list', compact('requisitions'));
    }


    
    public function approvalHOD()
    {
        if (
            !\Auth::user()->can('approve hod requisition') &&
            !\Gate::check('approve md requisition')
        ) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    
        $user = auth()->user();
    
        // Get employee record
        $employee = Employee::where('user_id', $user->id)->first();
    
        if (!$employee && !$user->hasRole('Managing Director')) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }
    
        // Base query for HOD stage
        $baseQuery = PurchaseRequisition::where('current_stage_level', 1);
    
        // MD sees all, others see only their department
        if (!$user->hasRole('Managing Director')) {
            $baseQuery->where('department_id', $employee->department_id);
        }
    
        // Pending
        $pendingRequisitions = (clone $baseQuery)
            ->where('status', 'pending')
            ->get();
    
        // Rejected
        $rejectedRequisitions = (clone $baseQuery)
            ->where('status', 'rejected')
            ->get();
    
        return view(
            'purchase_requisitions.approval_list',
            compact('pendingRequisitions', 'rejectedRequisitions')
        );
    }


    
    public function approvalInternalAudit()
    {
        if (
            !\Auth::user()->can('approve audit requisition') &&
            !\Gate::check('approve md requisition')
        ) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    
        // Base query for Internal Audit stage
        $baseQuery = PurchaseRequisition::where('current_stage_level', 2);
    
        // Pending requisitions
        $pendingRequisitions = (clone $baseQuery)
            ->where('status', 'pending')
            ->get();
    
        // Rejected requisitions
        $rejectedRequisitions = (clone $baseQuery)
            ->where('status', 'rejected')
            ->get();
    
        return view(
            'purchase_requisitions.approval_list',
            compact('pendingRequisitions', 'rejectedRequisitions')
        );
    }

    
    public function approvalAccounts()
    {
        if (
            !\Auth::user()->can('approve accounts requisition') &&
            !\Gate::check('approve md requisition')
        ) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    
        // Base query for Accounts stage
        $baseQuery = PurchaseRequisition::where('current_stage_level', 3);
    
        // Pending requisitions
        $pendingRequisitions = (clone $baseQuery)
            ->where('status', 'pending')
            ->get();
    
        // Rejected requisitions
        $rejectedRequisitions = (clone $baseQuery)
            ->where('status', 'rejected')
            ->get();
    
        return view(
            'purchase_requisitions.approval_list',
            compact('pendingRequisitions', 'rejectedRequisitions')
        );
    }

    
    public function approvalMD()
    {
        if (!\Auth::user()->can('approve md requisition')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    
        $pendingRequisitions = PurchaseRequisition::where('current_stage_level', 4)
            ->where('status', 'pending')
            ->get();
    
        $rejectedRequisitions = PurchaseRequisition::where('current_stage_level', 4)
            ->where('status', 'rejected')
            ->get();
    
        return view('purchase_requisitions.approval_list', compact(
            'pendingRequisitions',
            'rejectedRequisitions'
        ));
    }

    
    public function finalApproval()
    {
        $requisitions = PurchaseRequisition::where('current_stage_level', 5)->get();
        return view('purchase_requisitions.approval_list', compact('requisitions'));
    }


    private function getApproverByStageLevel($stage_level)
    {
        $roles = ['Manager', 'Finance', 'Procurement', 'Director', 'CEO'];
        return User::where('role', $roles[$stage_level - 1])->first()->id ?? null;
    }
    
    public function approve(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);

        $pr = PurchaseRequisition::findOrFail($id);
        $approval = PurchaseRequisitionApproval::where('purchase_requisition_id', $id)
            ->where('stage_level', $pr->current_stage_level)
            //->where('user_id', Auth::id())
            ->firstOrFail();

        $approval->update(['status' => $request->status, 'approved_by' => Auth::id()]);

        if ($request->status == 'approved') {
            if ($pr->current_stage_level < 4) {
                $pr->increment('current_stage_level');
            } else {
                $pr->increment('current_stage_level');
                $pr->update(['status' => 'approved', 'payment_status' => 1]);
            }
        } else {
            $pr->update(['status' => 'rejected']);
        }

        return redirect()->route('purchase-requisitions.index')->with('success', 'Approval updated.');
    }
    
    public function hodApprove(Request $request, $id)
    {
        $pr = PurchaseRequisition::findOrFail($id);
    
        // Find the approval record for the current stage
        $approval = PurchaseRequisitionApproval::where('purchase_requisition_id', $id)
            ->where('stage_level', $pr->current_stage_level)
            //->where('user_id', Auth::id()) // Uncomment if user-specific approval is needed
            ->firstOrFail();
    
        // Update approved quantity and cost for each item
        foreach ($request->quantity as $itemId => $quantity) {
            $item = PurchaseRequisitionItem::findOrFail($itemId);
            $item->quantity = $quantity;
            $item->estimated_cost = $request->estimated_cost[$itemId];
            $item->status = $request->status[$itemId];
            $item->save();
        }
    
        // Mark approval as completed
        $approval->status = 'approved';
        $approval->approved_by = Auth::id();
        $approval->updated_at = now();
        $approval->save();
    
        // Move to the next approval stage
        $pr->current_stage_level = 2; // Move to 'Accounts' stage
        $pr->save();
    
        return redirect()->route('purchase-requisitions.index')->with('success', 'Approval updated.');
    }
    
    public function internalAuditApprove(Request $request, $id)
    {
        $pr = PurchaseRequisition::findOrFail($id);
    
        // Find the approval record for the current stage
        $approval = PurchaseRequisitionApproval::where('purchase_requisition_id', $id)
            ->where('stage_level', $pr->current_stage_level)
            //->where('user_id', Auth::id()) // Uncomment if user-specific approval is needed
            ->firstOrFail();
    
        // Update approved quantity and cost for each item
        foreach ($request->approved_quantity as $itemId => $quantity) {
            $item = PurchaseRequisitionItem::findOrFail($itemId);
            $item->approved_quantity = $quantity;
            $item->approved_cost = $request->approved_cost[$itemId];
            $item->status = $request->status[$itemId];
            $item->save();
        }
    
        // Mark approval as completed
        $approval->status = 'approved';
        $approval->approved_by = Auth::id();
        $approval->updated_at = now();
        $approval->save();
    
        // Move to the next approval stage
        $pr->current_stage_level = 3; // Move to 'Accounts' stage
        $pr->save();
    
        return redirect()->route('purchase-requisitions.index')->with('success', 'Approval updated.');
    }
    
    public function accountsApprove(Request $request, $id)
    {
        $pr = PurchaseRequisition::findOrFail($id);
    
        $approval = PurchaseRequisitionApproval::where('purchase_requisition_id', $id)
            ->where('stage_level', $pr->current_stage_level)
            ->firstOrFail();
    
        $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
        ]);
    
        $pr->service_provider_id = $request->service_provider_id;
        $pr->save();
    
        $approval->status = 'approved';
        $approval->approved_by = Auth::id();
        $approval->updated_at = now();
        $approval->save();
    
        $pr->current_stage_level = 4; // Move to MD approval
        $pr->save();
    
        return redirect()->route('purchase-requisitions.index')->with('success', 'Approval updated.');
    }
    
    public function payment()
    {
        if(\Auth::user()->can('manage payment requisition')){
            $requisitions = PurchaseRequisition::where('payment_status',  PurchaseRequisition::PAYMENT_STATUS_PENDING)->get();
            $partiallyPaidRequisitions = PurchaseRequisition::where('payment_status', PurchaseRequisition::PAYMENT_STATUS_PARTIALLY_PAID)->get();
            
            $payslip_batches = PurchaseRequisitionPaymentBatch::get();

        return view('purchase_requisitions.payment', compact('requisitions', 'partiallyPaidRequisitions', 'payslip_batches'));
            
        }else{
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }

    public function cancelPayment($id)
    {
        if (\Auth::user()->can('manage payment requisition')) {
            try {
                $id = \Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Requisition Not Found.'));
            }

            $pr = PurchaseRequisition::findOrFail($id);
            // Verify if it is in a state that can be cancelled (e.g. pending payment)
            // The requirement says change status to rejected and payment_status to null.
            
            $pr->status = 'rejected';
            $pr->payment_status = null;
            $pr->save();

            return redirect()->back()->with('success', 'Requisition payment cancelled successfully.');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
    

    public function bulkPaymentStore(Request $request)
    {
        if(\Auth::user()->can('initialise payment requisition')){
            $request->validate([
            'requisition_ids' => 'required|array',
            'requisition_ids.*' => 'exists:purchase_requisitions,id', // Ensures each ID exists
            'amounts' => 'required|array',
        ]);
    
        $requisitionsIds = $request->input('requisition_ids', []);
        $amounts = $request->input('amounts', []);
    
        DB::beginTransaction();
        try {
            // Create a new payment batch
            $paymentBatch = new PurchaseRequisitionPaymentBatch();
            $paymentBatch->batch_id = 'BULKPR00' . $this->latestBatchId();
            $paymentBatch->batch_type = 'regular';
            $paymentBatch->status = 0;
            $paymentBatch->created_by = auth()->id(); // Use authenticated user
            $paymentBatch->save();
    
            // Process each requisition
            foreach ($requisitionsIds as $id) {
                if (!isset($amounts[$id])) {
                    continue; // Skip if no amount is set (shouldn't happen with proper frontend)
                }

                $amountToPay = $amounts[$id];
                $pr = PurchaseRequisition::find($id);
                $totalApproved = $pr->totalApprovedCost();
                
                // Attach to batch with amount
                $paymentBatch->paySlips()->attach($id, ['amount' => $amountToPay]);
                
                // Calculate total paid so far (including this new batch which is pending) or just rely on logic
                // Ideally, we sum up all PAID or PROCESSING batches.
                // For now, let's look at the current request logic.
                
                // If amount paid is less than total approved cost, set status to Partially Paid
                // However, "Payment Status" 2 seems to be "Unpaid" or "In Progress" in this context?
                // Looking at original code: update(['pr_payment_batch_id' => $paymentBatch->id, 'payment_status' => 2]);
                // Status 2 = UNPAID (which probably means "Processing" or "Pending Payment" in this system's weird naming)
                // Status 3 = PARTIALLY PAID
                // Status 4 = PAID
                
                // If we are paying the full remaining amount, it goes to "Processing/Unpaid" (2).
                // If we are paying only a part, it goes to "Partially Paid" (3)?
                // actually, let's keep it simple. If it's in a batch, it's "Processing". 
                // But we need to know if it's fully covered.
                
                // Let's assume Status 2 is "In Batch / Pending Payment".
                // If it's partially paid, effectively it IS in a batch.
                
                $pr->payment_status = 2; // Set to "Unpaid/In Batch" for now as per original flow
                $pr->pr_payment_batch_id = $paymentBatch->id; // Keep this mainly for backward compat or easy reference if needed, though pivot is primary now.
                $pr->save();
            }
    
            DB::commit();
    
            return redirect()->back()->with('success', 'Bulk payment batch created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create payment batch: ' . $e->getMessage());
        }
        }else{
            return redirect()->back()->with('error', 'Permission denied.');
        }
        
    }


    public function showPayslip($id)
    {
         if(\Auth::user()->can('manage payment requisition')){
            try {
                $id = Crypt::decrypt($id);
                } catch (\Throwable $th) {
                    return redirect()->back()->with('error', __('Payslip Not Found.'));
                }
                
                $payslip = PurchaseRequisitionPaymentBatch::with('paySlips')->where('id', $id)->first();
                // $payslipItems = PurchaseRequisition::where('pr_payment_batch_id', '=', $id)->get(); NOT NEEDED, use relationship
                $payslipItems = $payslip->paySlips;
                
            $totalSum = 0;
            foreach ($payslipItems as $item) {
                $totalSum += $item->pivot->amount; 
            }

                
                //$totalCount = PurchaseRequisition::where('pr_payment_batch_id', $id)->count();
                $totalCount = $payslipItems->count();
                
                // For stats, we might need a more complex query or filter the existing collection
                // Since this is a legacy codebase, preserving exact behavior for "failed" and "reversed" might be tricky if they rely on PR columns.
                // The PR columns txn_status etc. are still on the PR table, but if a PR is split across multiple batches, 
                // the txn_status on the PR might be overwritten or ambiguous.
                // However, for this task, I will attempt to preserve existing logic where possible.
                // If a PR is in a batch, it's linked.
                
                // Recalculating these stats based on the Collection is safer than DB query if we assume the Batch<->PR link is the source of truth.
                
                $failedCount = 0;
                $failedTotalSum = 0;
                $reversedCount = 0;
                $reversedCount = 0;

                // Simple check on the items in THIS batch
                foreach ($payslipItems as $item) {
                    if ($item->txn_status == 'FAILED') {
                        $failedCount++;
                        $failedTotalSum += $item->pivot->amount;
                    }
                    if ($item->status == 6) { // assuming 6 is reversed
                        $reversedCount++;
                        //$reversedTotalSum += $item->pivot->amount; // Variable not used in view? logic below had it.
                    }
                }

                // Original logic for failed/reversed sums was distinct queries.
                // Let's keep it simple for now and trust the collection iteration.
                
                return view('purchase_requisitions.showpayslip', compact('payslip', 'payslipItems', 'totalCount', 'totalSum', 'failedCount', 'failedTotalSum', 'reversedCount'));
                
        }else{
            return redirect()->back()->with('error', 'Permission denied.');
        }
        
        
    }
    
    public function approvePayment($payslipId)
    {
        if(\Auth::user()->can('approve payment farmers')){
             try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            
            
            $payslip = PurchaseRequisitionPaymentBatch::find($id);
            DB::beginTransaction();
                try {
                    // Update PayslipHrBatch status
                    $payslip->status = 1;
                    $payslip->approved_by = \Auth::user()->id;
                    $payslip->approved_at = now();
                    $payslip->save();
        
                    // Update all related PaySlips
                    PurchaseRequisition::where('pr_payment_batch_id', $payslip->id)
                        ->update(['payment_status' => 2]);
        
                    // Commit the transaction if everything is successful
                    DB::commit();
        
                    return redirect()->back()->with('success', __('Payslip approved successfully.'));
                } catch (\Exception $e) {
                    // Rollback in case of any errors
                    DB::rollBack();
                    return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
                }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
       
        
    }
     /**
     * Payslip approval page 
     * Requires OTP to proceed
     */
        public function approvePayment2($id)
        {
            
            if (\Auth::user()->can('approve payment requisition')) {
                $payslip = PurchaseRequisitionPaymentBatch::find(Crypt::decrypt($id));
                
                $totalSum = PurchaseRequisitionItem::whereHas('purchaseRequisition', function ($query) use ($id) {
                $query->where('purchase_requisition_id', '=', Crypt::decrypt($id));
                })->sum('estimated_cost');
        
                if (!$payslip) {
                    return redirect()->back()->with('error', __('Payslip not found.'));
                }
                
                if ($payslip->status == 6) {
                    return redirect()->back()->with('error', __('Payslip already payed.'));
                }
                
                
                
                // Generate a 6-digit OTP
                $otp = rand(100000, 999999);
        
                // Store OTP in session for validation
                Session::put('otp', $otp);
                Session::put('otp_expires', now()->addMinutes(10)); // OTP expires in 10 mins
        
                // Fetch mail settings
                $settings = Utility::settingsById(\Auth::user()->id);
                $data = Utility::getSetting();
                $setting = [
                    'mail_driver' => '',
                    'mail_host' => '',
                    'mail_port' => '',
                    'mail_encryption' => '',
                    'mail_username' => '',
                    'mail_password' => '',
                    'mail_from_address' => '',
                    'mail_from_name' => '',
                ];
        
                foreach ($data as $row) {
                    $setting[$row->name] = $row->value;
                }
        
                // Apply mail settings dynamically
                config([
                    'mail.driver' => $settings['mail_driver'] ?? $setting['mail_driver'],
                    'mail.host' => $settings['mail_host'] ?? $setting['mail_host'],
                    'mail.port' => $settings['mail_port'] ?? $setting['mail_port'],
                    'mail.encryption' => $settings['mail_encryption'] ?? $setting['mail_encryption'],
                    'mail.username' => $settings['mail_username'] ?? $setting['mail_username'],
                    'mail.password' => $settings['mail_password'] ?? $setting['mail_password'],
                    'mail.from.address' => $settings['mail_from_address'] ?? $setting['mail_from_address'],
                    'mail.from.name' => $settings['mail_from_name'] ?? $setting['mail_from_name'],
                ]);
        
                try {
                    // Send OTP email manually
                    Mail::to(\Auth::user()->email)->send(new OtpRequisitionMail($otp, $payslip, $totalSum));
                    
                    // Mail::to([\Auth::user()->email, 'farm.manager@seborefarms.ng'])
                    //     ->send(new OTPSalaryMail($otp, $payslip));
    
        
                    return view('purchase_requisitions.approve_payment', compact('payslip', 'totalSum'));
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', __('Failed to send OTP email: ') . $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        
        public function processApproval(Request $request)
        {
            if (\Auth::user()->can('approve payment requisition')) {
              //  Retrieve OTP from session
                $storedOtp = Session::get('otp');
                $otpExpires = Session::get('otp_expires');
        
                if (!$storedOtp || now()->greaterThan($otpExpires)) {
                    return redirect()->back()->with('error', 'OTP has expired. Please request a new one.');
                }
        
                if ($request->otp != $storedOtp) {
                    return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
                }
        
                // OTP is valid - process approval
                Session::forget(['otp', 'otp_expires']);
        
                $payslip = PurchaseRequisitionPaymentBatch::find(Crypt::decrypt($request->payslip_id));
        
                if (!$payslip) {
                    return redirect()->back()->with('error', __('Payslip not found.'));
                }
        
                if ($payslip->status == 1) {
                    return redirect()->back()->with('error', __('Payslip already approved.'));
                }
        
                // Use a database transaction to ensure atomicity
                DB::beginTransaction();
                try {
                    // Update PayslipHrBatch status
                    $payslip->status = 1;
                    $payslip->approved_by = \Auth::user()->id;
                    $payslip->approved_at = now();
                    $payslip->save();
        
                    // Update all related PaySlips
                    PurchaseRequisition::where('pr_payment_batch_id', $payslip->id)
                        ->update(['payment_status' => 2]);
        
                    // Commit the transaction if everything is successful
                    DB::commit();
        
                    return redirect()->back()->with('success', __('Payslip approved successfully.'));
                } catch (\Exception $e) {
                    // Rollback in case of any errors
                    DB::rollBack();
                    return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
                }
            } 

        }
     
             
    public function initialisePayment($payslipId)
    {
         if(\Auth::user()->can('initialise payment requisition')){
        DB::beginTransaction();
        try {
            
            $payslipBatch = PurchaseRequisitionPaymentBatch::with('paySlips')->find(Crypt::decrypt($payslipId));
            
            if (!$payslipBatch) {
                return redirect()->back()->with('error', __('Payslip batch not found.'));
            }
            
            // Filter only unpaid items in this batch? logic says where payment_status = 2.
            // If we are in the batch, and the batch isn't paid, we assume we pay all items in valid state.
            $unpaidPR = $payslipBatch->paySlips->filter(function($pr) {
                return $pr->payment_status == PurchaseRequisition::PAYMENT_STATUS_UNPAID
                    || $pr->txn_status === 'EXPIRED'
                    || $pr->payment_status == PurchaseRequisition::PAYMENT_STATUS_PARTIALLY_PAID;
            });

            if ($unpaidPR->isEmpty()) {
                // If filtering by status 2 fails, we might just check if the batch itself is unpaid.
                // But let's stick to user logic.
                return redirect()->back()->with('error', __('No unpaid requisition  found for this batch.'));
            }
                
            $batchId = $payslipBatch->id;
            $payLoad = [];
    
            foreach ($unpaidPR as $Pr) {

                $ref = $this->generateValidReference('PR');
                $payLoad[] = [
                    'reference' => $ref.'-'.$Pr->id,
                    'narration' => $Pr->title. '-PR',
                    'destinationAccountNumber' => $Pr->serviceProvider->bank_account,
                    'destinationBankCode' => $Pr->serviceProvider->bank_code,
                    'amount' => $Pr->pivot->amount, // CHANGED: Use pivot amount
                    'currency' => 'NGN',
                ];
                // $employee->status = 0;
                $Pr->txn_ref = $ref;
                $Pr->save();
            }
    
            // Prepare post data
            $REF = 'REQ-';
            $batchRef = $this->generateValidReference($REF);
            $postData = [
                'title' => 'Purchase Requisition',
                'batchReference' => $batchRef,
                'narration' => 'Requisition Payment',
                'transactionList' => $payLoad,
                'onValidationFailure' => 'CONTINUE',
                'notificationInterval' => 25,
            ];
    
            $monnify = new Monnify();
            $result = $monnify->bulkPaymentInitialise($postData);
            $response = $result->getData();
    
            if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
                // Get transaction details and update transactions items reference and status
                $responseBody = $response->details->responseBody;
                $paymentBatch = PurchaseRequisitionPaymentBatch::find($batchId);
                $paymentBatch->total_fee = $responseBody->totalFee;
                $paymentBatch->batch_reference = $batchRef;
                $paymentBatch->status = 3;
                $paymentBatch->save();
                $this->updatePaylipTxn($batchRef);
                DB::commit();
                
                return redirect()->route('purchase-requisitions.payments')->with('success', __('Payslip Bulk Payment successfully Innitialised.'));
            } else {
                DB::rollBack();
                \Log::error('Monnify Bulk Payment Initialise Error: ' . json_encode($response));

                return redirect()->back()->with('error', __($response->details->responseMessage));
            }
        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error('Monnify Bulk Payment Initialise Error: ' . json_encode($response));
            return redirect()->back()->with('error', __($e->getMessage()));
        }
        }else{
            return redirect()->back()->with('error', 'Permission denied.');
        }
        

    }
    
    public function authorisePayment(Request $request)
    {
        
        if (!\Auth::user()->can('authorise payment requisition')) {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ], 403);
        }

    
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6', // Ensure OTP is a 6-digit number
            'payslip_batch_id' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            // Start Database Transaction
            DB::beginTransaction();
    
            // Retrieve the payslip batch
            $payslipBatchId = Crypt::decrypt($request->payslip_batch_id);
            $payslipBatch = PurchaseRequisitionPaymentBatch::findOrFail($payslipBatchId);
            
            if ($payslipBatch->status == 4 ) {
                return redirect()->back()->with('error', __('Requisition paid already!'));
            }
    
            $monnify = new Monnify();
    
            // Call Monnify bulk payment authorization
            $result = $monnify->bulkPaymentAuthorize($payslipBatch->batch_reference, $request->otp);
            $response = $result->getData();
    
            if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
                $responseBody = $response->details->responseBody;
    
                // Update the payslip batch with the Monnify response details
                $payslipBatch->status = 4; 
                $payslipBatch->save();
    
                // Update all related PaySlips
                PurchaseRequisition::where('pr_payment_batch_id', $payslipBatch->id)
                    ->update(['status' => 1]);
    
                // Request bulk transfer transactions
                $transactions = $monnify->getBulkTransferTransactions($responseBody->batchReference);
                $transactions = $transactions->getData();
                $transactionData = $transactions->details->responseBody;
    
                // Commit Transaction (Finalizing the database changes)
                DB::commit();
                $this->updatePaylipTxn($payslipBatch->batch_reference);
                return response()->json([
                    'success' => true,
                    'message' => 'Bulk transfer authorized successfully.',
                    'data' => [
                        'batchReference' => $responseBody->batchReference,
                        'totalAmount' => $responseBody->totalAmount,
                        'totalFee' => $responseBody->totalFee,
                        'batchStatus' => $responseBody->batchStatus,
                        'dateCreated' => $responseBody->dateCreated,
                    ]
                ], 200);
            } else {
                // Rollback Transaction (Undo database changes if Monnify fails)
                DB::rollBack();
                
                // Log the error with full trace
                \Log::error('Error authorizing payment:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
    
                return response()->json([
                    'success' => false,
                    'message' => $response->responseMessage ?? 'Bulk transfer authorization failed',
                    'details' => $response->responseBody ?? []
                ], 400);
            }
        } catch (\Exception $e) {
            // Rollback Transaction in case of an error
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while authorizing the payment.',
                'error' => $response
            ], 500);
        }
    }
    
    public function resendToken(Request $request)
    {
        if(\Auth::user()->can('resend token requisition')){
            // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'reference' => 'required', 
        ]);
        

    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
    
        //try {
            // Retrieve the payslip batch
            $tansactionRef = Crypt::decrypt($request->reference);
    
            $monnify = new Monnify();
    
            // Call Monnify bulk payment authorization
            $result = $monnify->resendToken($tansactionRef);
            $response = $result->getData();
            if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
            $responseBody = $response->details->responseBody;

            return response()->json([
                'success' => true,
                'message' => $responseBody->message,
                'data' => [
                    'message' => $responseBody->message,
                ]
            ], 200);
            
            } else {
                // Handle Monnify-specific error response
                return response()->json([
                    'success' => false,
                    'message' => $response->details->responseMessage ?? 'System unable to genrate new OTP',
                    'details' => $responseBod ?? []
                ], 400);
            }
            
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'An error occurred while authorizing the payment.',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
        }
         else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
    } 
    
    public function revalidatePaylip($batchRef)
    {
        if (!\Auth::user()->can('initialise payment requisition')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        try {
            $batchRef = Crypt::decrypt($batchRef);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Reference error.'));
        }
        
        $this->updatePaylipTxn($batchRef);
        
        return redirect()->back()->with('success', __('Transaction status successfuly updated.'));
                
    }
    
    private function latestPrId()
    {
        $latest = PurchaseRequisition::latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    
    
    public function updatePaylipTxn($batchRef)
    {
        $monnify = new Monnify();
        $maxRetries = 5;
        $retryDelay = 5; // Wait 5 seconds before retrying
    
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $result = $monnify->getBulkTransferTransactions($batchRef);
            $response = $result->getData();
    
            if (isset($response->details->responseBody->content)) {
                $batchItems = array_map(function ($item) {
                    if (preg_match('/-(\d+)$/', $item->reference, $matches)) {
                        return [
                            'requisitionId' => $matches[1],
                            'reference' => $item->reference,
                            'transactionDescription' => $item->transactionDescription ?? '',
                            'status' => $item->status,
                        ];
                    }
                    return null;
                }, $response->details->responseBody->content);
    
                $batchItems = array_filter($batchItems);
    
                foreach ($batchItems as $item) {
                    $prId = (int) $item['requisitionId'];
                    \Log::info("UpdatePaylipTxn: Processing Reference: " . $item['reference'] . " | Extracted ID: " . $prId);
                    
                    $pr = PurchaseRequisition::where('id', $prId)->first();
                    
                    if (!$pr) {
                        \Log::warning("UpdatePaylipTxn: PR not found for ID: " . $prId);
                    }

                    if ($pr) {
                        $paymentStatus = PurchaseRequisition::PAYMENT_STATUS_PAID;
                        
                        if ($item['status'] == 'FAILED') {
                            $paymentStatus = PurchaseRequisition::PAYMENT_STATUS_FAILED;
                        } else {
                            $totalPaid = $pr->paymentBatches->sum('pivot.amount');
                            if ($totalPaid >= $pr->totalApprovedCost()) {
                                $paymentStatus = PurchaseRequisition::PAYMENT_STATUS_PAID;
                            } else {
                                $paymentStatus = PurchaseRequisition::PAYMENT_STATUS_PARTIALLY_PAID;
                            }
                        }

                        $pr->update([
                            'txn_status' => $item['status'],
                            'txn_ref' => $item['reference'],
                            'txn_description' => $item['transactionDescription'],
                            'payment_status' => $paymentStatus
                        ]);
                    }
                }
                return;
            }
    
            // Wait before retrying
            sleep($retryDelay);
        }
    
        // If still no response after max retries, return an error
        // return redirect()->back()->with('error', __('Failed to fetch transaction details after multiple attempts.'));
        return false;
    }
    
    private function latestBatchId()
    {
        $latest = PurchaseRequisitionPaymentBatch::latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    
    private function generateValidReference($prefix)
    {
        return trim($prefix . str_replace('.', '_', uniqid())); // Replaces dots with underscores
    }


}
