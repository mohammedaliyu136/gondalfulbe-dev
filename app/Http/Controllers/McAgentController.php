<?php

namespace App\Http\Controllers;

use App\Exports\VenderExport;
use App\Imports\VenderImport;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\Agent;
use App\Models\PaySlipMcAgentBatch;
use App\Models\PaySlipMcAgentBatchItem;
use App\Models\warehouse;
use App\Monnify\Monnify;

use Auth;
use App\Models\User;
use App\Models\Plan;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class McAgentController extends Controller
{


    public function index()
    {
        if(\Auth::user()->can('manage mc officer'))
        {
            $agents = Agent::where('created_by', \Auth::user()->creatorId())->get();
            $payslip_batches = PaySlipMcAgentBatch::where('created_by', \Auth::user()->creatorId())->get();

            return view('mc_agent.index', compact('agents', 'payslip_batches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create mc officer'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
            $warehouses = Warehouse::all(); // Assuming you have a Warehouse model
            $monnify = new Monnify();
            $result = $monnify->bankList();
            $response = $result->getData();
            if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
                // Convert responseBody (object) to an array
                $responseBodyArray = json_decode(json_encode($response->details->responseBody), true);
            
                $bankList = array_map(function ($bank) {
                    return [
                        'name' => $bank['name'],
                        'code' => $bank['code'],
                    ];
                }, $responseBodyArray);

            }
            return view('mc_agent.create', compact('customFields', 'warehouses', 'bankList'));        
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create mc officer')) {
            $rules = [
                'name' => 'required',
                'contact' => 'required',
                'bank_name' => 'required',
                'bank_code' => 'required',
                'bank_account' => 'required',
                'account_name' => 'required',
                'email' => [
                    'required',
                    Rule::unique('agents')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    }),
                ],
                'collection_centre' => 'required|exists:warehouses,id', // Ensure valid warehouse ID
            ];
    
            $validator = \Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('mcagent.index')->with('error', $messages->first());
            }
    
            $authUser = \Auth::user();
            $creator = User::find($authUser->creatorId());

    
            $mcAgent = new Agent();
            $mcAgent->agent_id = $this->agentNumber();
            $mcAgent->name = $request->name;
            $mcAgent->contact = $request->contact;
            $mcAgent->email = $request->email;
            $mcAgent->bank_name = $request->bank_name;
            $mcAgent->bank_code = $request->bank_code;
            $mcAgent->bank_account = $request->bank_account;
            $mcAgent->account_name = $request->account_name;
            $mcAgent->tax_number = $request->tax_number;
            $mcAgent->billing_name = $request->billing_name;
            $mcAgent->billing_country = $request->billing_country;
            $mcAgent->billing_state = $request->billing_state;
            $mcAgent->billing_city = $request->billing_city;
            $mcAgent->billing_phone = $request->billing_phone;
            $mcAgent->billing_zip = $request->billing_zip;
            $mcAgent->billing_address = $request->billing_address;
            $mcAgent->created_by = $authUser->creatorId();
    
            // Fetch the warehouse name using the selected warehouse ID
            $warehouse = Warehouse::find($request->collection_centre);
            $mcAgent->collection_centre = $warehouse->name;
    
            // Handle Image Upload
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('uploads/mcagents'), $imageName);
                $mcAgent->image = $imageName;
            }
    
            $mcAgent->save();
            CustomField::saveData($mcAgent, $request->customField);
    
            return redirect()->route('mcagent.index')->with('success', __('Agent successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        try {
            $id       = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Agent Not Found.'));
        }

        $id     = \Crypt::decrypt($ids);
        $agent = Agent::find($id);

        return view('mc_agent.show', compact('agent'));
    }


    public function edit($id)
    {
        if (!\Auth::user()->can('edit mc officer')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        $mcAgent = Agent::findOrFail($id);
        $warehouses = Warehouse::all();
    
        $mcAgent->customField = CustomField::getData($mcAgent, 'mcagent');
        $customFields = CustomField::where('created_by', \Auth::user()->creatorId())
                                   ->where('module', 'rider')
                                   ->get();
    
        $selectedBankCode = $mcAgent->bank_code;
        $selectedWarehouseId = Warehouse::where('name', $mcAgent->collection_centre)->value('id');
    
        $bankList = [];
        $monnify = new Monnify();
        $response = $monnify->bankList()->getData();
    
        if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
            $bankList = collect($response->details->responseBody)->map(function ($bank) {
                return [
                    'name' => $bank->name,
                    'code' => $bank->code,
                ];
            })->toArray();
        }
    
        return view('mc_agent.edit', compact(
            'mcAgent',
            'customFields',
            'bankList',
            'selectedBankCode',
            'warehouses',
            'selectedWarehouseId'
        ));
    }



    public function update(Request $request, Agent $agent)
    {
        if (!\Auth::user()->can('edit mc officer')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        // Validation rules
        $rules = [
            'name' => 'required',
            'contact' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'bank_account' => 'required',
            'account_name' => 'required',
            'email' => [
                'required',
                Rule::unique('agents')->where(function ($query) {
                    return $query->where('created_by', \Auth::user()->id);
                }),
            ],
            'collection_centre' => 'required|exists:warehouses,id', // Ensure valid warehouse ID
            // Only validate image if the input is provided
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    
        // Perform validation
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('mcagent.index')->with('error', $validator->getMessageBag()->first());
        }
    
        // Update rider details
        $agent->fill([
            'name'             => $request->name,
            'contact'          => $request->contact,
            'email'            => $request->email,
            'tax_number'       => $request->tax_number,
            'bank_name'        => $request->bank_name,
            'bank_code'        => $request->bank_code,
            'bank_account'     => $request->bank_account,
            'account_name'     => $request->account_name,
            'created_by'       => \Auth::user()->creatorId(),
            'billing_name'     => $request->billing_name,
            'billing_country'  => $request->billing_country,
            'billing_state'    => $request->billing_state,
            'billing_city'     => $request->billing_city,
            'billing_phone'    => $request->billing_phone,
            'billing_zip'      => $request->billing_zip,
            'billing_address'  => $request->billing_address,
            'shipping_name'    => $request->shipping_name,
            'shipping_country' => $request->shipping_country,
            'shipping_state'   => $request->shipping_state,
            'shipping_city'    => $request->shipping_city,
            'shipping_phone'   => $request->shipping_phone,
            'shipping_zip'     => $request->shipping_zip,
            'shipping_address' => $request->shipping_address,
        ]);
        
        // Handle Image Upload (if exists)
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($agent->image && file_exists(public_path('uploads/mcagents/' . $mcAgent->image))) {
                unlink(public_path('uploads/mcagents/' . $mcAgent->image));
            }
    
            // Save new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/mcagents'), $imageName);
            $agent->image = $imageName;
        }
    
        $agent->save();
    
        // Save custom fields
        CustomField::saveData($agent, $request->customField);
    
        return redirect()->route('mcagent.index')->with('success', __('Agent successfully updated.'));
    }

    public function destroy(Agent $agent)
    {
        if (\Auth::user()->can('delete mc officer')) {
    
                // Check if the rider has any payslip records
                $hasPayslips = PaySlipMcAgentBatchItem::where('agent_id', $agent->id)->exists();
    
                if ($hasPayslips) {
                    return redirect()->back()->with('error', __('Cannot delete agent. Payslip records exist.'));
                }
    
               try {
                    $agent->delete();
                    return redirect()->route('mcagent.index')->with('success', __('Agent successfully deleted.'));
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', __('Error deleting agent: ') . $e->getMessage());
                }
    
                return redirect()->route('mcagent.index')->with('success', __('Agent successfully deleted.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    // Function to get the latest rider number
    // This generates the next rider ID by finding the most recently created rider and incrementing the ID.
    function agentNumber()
    {
        $latest = Agent::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->agent_id + 1;
    }
    
    // Function to get the latest payment batch ID
    // This generates the next payment batch ID by finding the most recent batch and incrementing the ID.
    public function latestBatchId()
    {
        $latest = PaySlipMcAgentBatch::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }


    /**
     * Create bulk payment
     */
     public function bulkPaymentCreate()
    {
        if(\Auth::user()->can('manage payment mc officer')){
            $riders = Agent::where('created_by', \Auth::user()->creatorId())->where('is_active', 1)->get();
        
            // Return view or JSON response
            return view('mc_agent.payslip_create', compact('riders'));
        
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    


    public function bulkPaymentStore(Request $request)
    {
        if(\Auth::user()->can('generate bulk payment mc officer')) {
            
            
            //  $rules = [
            //     'start_date' => 'required|date|before_or_equal:end_date',  // Ensure start date is before or equal to end date
            //     'end_date' => 'required|date|after_or_equal:start_date',   // Ensure end date is after or equal to start date
            //     'agent_ids' => 'required|array',
            //     'agent_ids.*' => 'required|integer|exists:riders,id',
            //     'amount' => 'required|array',
            //     'amount.*' => 'required|numeric|min:0',
            //  ];
    
            // // Perform validation
            // $validator = \Validator::make($request->all(), $rules);
        
            // if ($validator->fails()) {
            //     return redirect()->back()->with('error', $validator->getMessageBag()->first());
            // }
            
            // Start a transaction
            DB::beginTransaction();
            
            try {
                // Retrieve the selected rider IDs and amounts
                $riderIds = $request->input('agent_ids', []);
                $riderAmounts = $request->input('amounts', []);
                
                // Fetch the riders whose IDs are in the array
                $riders = Agent::whereIn('id', $riderIds)->get();
    
                // Create a new payment batch
                $paymentBatch = new PaySlipMcAgentBatch();
                $paymentBatch->batch_id = 'BULK-AG-00' . $this->latestBatchId();
                $paymentBatch->start_date = $request->start_date;
                $paymentBatch->end_date = $request->end_date;
                $paymentBatch->batch_type = 'regular';
                $paymentBatch->status = 0;
                $paymentBatch->created_by = 2;
                $paymentBatch->save();
    
                // Loop through the riders and save the payment batch items
                foreach ($riderIds as $riderId) {
                $amount = isset($riderAmounts[$riderId]) ? $riderAmounts[$riderId] : 0;
            
                $paymentBatchItem = new PaySlipMcAgentBatchItem();
                $paymentBatchItem->agent_id = $riderId;
                $paymentBatchItem->pay_slip_mc_agent_batch_id = $paymentBatch->id;
                $paymentBatchItem->amount = $amount;
                $paymentBatchItem->status = 0;
                $paymentBatchItem->created_by = 2;
                $paymentBatchItem->save();
            }
                
                // Commit the transaction
                DB::commit();
    
                return redirect()->back()->with('success', __('Bulk payment successfully created.'));
            } catch (\Exception $e) {
                // Rollback the transaction if any query fails
                DB::rollback();
                
                // Log the error or handle the exception as needed
                \Log::error('Bulk payment store failed: ' . $e->getMessage());
    
                return redirect()->back()->with('error', __('An error occurred while processing the bulk payment.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkPaymentShow($id)
    {
        if(\Auth::user()->can('manage payment mc officer')){
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Payslip Not Found.'));
        }
        // $id = Crypt::decrypt($id); 
        $payslip = PaySlipMcAgentBatch::where('id', $id)->first();
        $payslipItems = PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', '=', $id)->get();
        
        $result = PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $id)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $failed = PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $id)
        ->where('txn_status', 'FAILED')
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $reversed = PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $id)
        ->where('status', 6)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $totalCount = $result->total_count; // Total number of items
        $totalSum = $result->total_sum;    // Total sum of 'amount' column
        
        $failedCount = $failed->total_count;
        $failedTotalSum = $failed->total_sum;
        
        $reversedCount = $reversed->total_count;
        $reveresedTotalSum = $reversed->total_sum;
        return view('mc_agent.payslip_show', compact('payslip', 'payslipItems', 'totalCount', 'totalSum', 'failedCount', 'failedTotalSum', 'reversedCount'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    
    public function bulkPaymentEdit($id)
    {
        //  try {
        //     $id       = Crypt::decrypt($id);
        // } catch (\Throwable $th) {
        //     return redirect()->back()->with('error', __('Rider Not Found.'));
        // }   
        
        if(\Auth::user()->can('generate bulk payment mc officer')){
            $payslip = PaySlipMcAgentBatchItem::findOrFail($id);
        
            // Return view or JSON response
            return view('mc_agent.payslip_edit', compact('payslip'));
        
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        } 
    }
    
    public function bulkPaymentUpdate(Request $request, PaySlipMcAgentBatchItem $payslip)
    {
        if (!\Auth::user()->can('generate bulk payment mc officer')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        // Validation rules
        $rules = [
            'amount' => 'required|numeric|min:0',
        ];
    
        // Perform validation
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('mcagent.index')->with('error', $validator->getMessageBag()->first());
        }
    
        // Update rider details
        $payslip->fill([
            'amount'             => $request->amount,

        ]);
        
        $payslip->save();
    
        return redirect()->back()->with('success', __('Payslip item updated successfuly.'));
    }
    
    public function paymentBatchItemtDelete(PaySlipMcAgentBatchItem $payslip)
    {
        $payslipBatch = PaySlipMcAgentBatch::findOrFail($payslip->pay_slip_mc_agent_batch_id);
        
        if ($payslipBatch->status > 0) {
            return redirect()->back()->with('error', __('Not Allow. Batch already approved.'));
        }
    
        if (\Auth::user()->can('generate bulk payment mc officer')) {
            if ($payslip->created_by == \Auth::user()->creatorId()) {
                $payslip->delete();
    
                return redirect()->route('mcagent.payslips.show', \Crypt::encrypt($payslipBatch->id))
                                 ->with('success', __('Pay slip item successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approvePayment($payslipId)
    {
        if(\Auth::user()->can('approve payment mc officer')){
             try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            $payslip = PaySlipMcAgentBatch::where('id', $id)->first();
            $payslip->status = 1;
            $payslip->save();
            
            PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $id)
            ->update([
                'status' => 1,
            ]);
            return redirect()->back()->with('success', __('Payslip approved successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
       
        
    }
    
    public function initialisePayment($payslipId)
    {
        if(\Auth::user()->can('initialise payment mc officer')){
            try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            
            $payslipBatch = PaySlipMcAgentBatch::find(Crypt::decrypt($payslipId));
            
            // Prepare the payload
            $beneficiaries = PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $id)->get();
            $payLoad = [];
            foreach ($beneficiaries as $beneficiary) {
                $metaData = [
                    'riderId' => $beneficiary->agent->id,
                    'paymentBatchItemId' => $beneficiary->id,
                ];
                
                $ref = $this->generateValidReference($payslipBatch->end_date);
                
                $payLoad[] = [
                    'reference' => $ref.'-'.$beneficiary->id,
                    'narration' => $payslipBatch->end_date. '-PAYMENT',  
                    'destinationAccountNumber' => $beneficiary->agent->bank_account, 
                    'destinationBankCode' => $beneficiary->agent->bank_code, 
                    'amount' => $beneficiary->amount,
                    'metaData' => $metaData,
                    'currency' => 'NGN'
                ];
            }
    
            
            //Prepare post data
            $REF = $payslipBatch->end_date.'-';
            $batchRef = $this->generateValidReference($REF);
             $postData = [
                'title' => 'Bulk Payment to Riders',
                'batchReference' =>  $batchRef,
                'narration' => 'SEB-MC-AGENTS-Payout',
                'transactionList' => $payLoad,
                'onValidationFailure' => 'CONTINUE',
                'notificationInterval' => 25,
            ];
            
            try {
                $monnify = new Monnify();
                $result = $monnify->bulkPaymentInitialise($postData);
                $response = $result->getData();
                if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
                    //1. get transaction details
                    //2.update transtions items reference and status
                    $responseBody = $response->details->responseBody;
                    $payslip = PaySlipMcAgentBatch::where('id', $id)->first();
                    $payslip->batch_reference = $responseBody->batchReference;
                    $payslip->total_fee = $responseBody->totalFee;
                    $payslip->status = 3;
                    $payslip->save();
                    
                    $this->updatePaylipTxn($batchRef);
                    
                    return redirect()->back()->with('success', __('Bulk payment successfully initialize.'));
                    
                
                } else {
                    return redirect()->back()->with('error', __($response->details->responseMessage));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __( $e->getMessage()));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    
    public function authorisePayment(Request $request)
    {
        if (!\Auth::user()->can('authorise payment mc officer')) {
            return redirect()->back()->with('error', __('Permission denied.'));
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
            $payslipBatch = PaySlipMcAgentBatch::findOrFail($payslipBatchId);
            
            if ($payslipBatch->status == 4 ) {
                return redirect()->back()->with('error', __('Salary paid already!'));
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
                PaySlipMcAgentBatchItem::where('pay_slip_mc_agent_batch_id', $payslipBatch->id)
                    ->update(['status' => 4]);
    
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
    public function revalidatePaylip($batchRef)
    {
         if(\Auth::user()->can('initialise payment mc officer')) {
            try {
                $batchRef = Crypt::decrypt($batchRef);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Reference error.'));
            }
            
            $this->updatePaylipTxn($batchRef);
            
            return redirect()->back()->with('success', __('Transaction status successfuly updated.'));
         }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }    
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
                            'payslipId' => $matches[1],
                            'reference' => $item->reference,
                            'transactionDescription' => $item->transactionDescription ?? '',
                            'status' => $item->status,
                        ];
                    }
                    return null;
                }, $response->details->responseBody->content);
    
                $batchItems = array_filter($batchItems);
    
                foreach ($batchItems as $item) {
                    PaySlipMcAgentBatchItem::where('id', $item['payslipId'])->update([ 
                        'txn_status' => $item['status'],
                        'reference' => $item['reference'],
                        'txn_description' => $item['transactionDescription'],
                    ]);
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
    
    public function resendToken(Request $request)
    {
        if(\Auth::user()->can('resend token mc officer')){
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
    
    private function generateValidReference($prefix)
    {
        return trim($prefix . str_replace('.', '_', uniqid())); // Replaces dots with underscores
    }
}
