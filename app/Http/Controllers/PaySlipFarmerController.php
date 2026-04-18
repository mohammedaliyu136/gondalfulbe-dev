<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\PaySlipFarmerBatch;
use App\Models\PaySlipFarmerBatchItem;
use App\Monnify\Monnify;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\PurchasePayment;
use App\Models\Vender;
use App\Models\PaySlip;
use App\Models\SaturationDeduction;
use App\Models\Utility;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Log;

class PaySlipFarmerController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage payment farmers') )
        {
            $payslip_batches = PaySlipFarmerBatch::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->get();
            
            // Get the total amount awaiting initialization
            $total_awaiting = Vender::getTotalBalance();
            
            // Get the count of venders with balance greater than 0
            $farmers_with_positive_balance = Vender::countWithPositiveBalance();
            $collectionCenterPayment = Vender::select(
                    'collection_centre',
                    \DB::raw('SUM(book_balance) as total_balance'),
                    \DB::raw('COUNT(CASE WHEN book_balance > 0 THEN 1 END) as positive_balance_count')
                )
                ->where('book_balance', '>', 0)
                ->groupBy('collection_centre')
                ->get();
                
            $month = [
                '01' => 'JAN',
                '02' => 'FEB',
                '03' => 'MAR',
                '04' => 'APR',
                '05' => 'MAY',
                '06' => 'JUN',
                '07' => 'JUL',
                '08' => 'AUG',
                '09' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC',
            ];

            $year = [

                '2023' => '2023',
                '2024' => '2024',
                '2025' => '2025',
                '2026' => '2026',
                '2027' => '2027',
                '2028' => '2028',
                '2029' => '2029',
                '2030' => '2030',
            ];

            return view('farmerspayslip.index', compact('payslip_batches', 'month', 'year', 'total_awaiting', 'farmers_with_positive_balance', 'collectionCenterPayment'  ));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
    * Generate bulkpayment for all vendors 
    * with positive balance
    * 
    **/
    public function bulk_pay_create()
    {
        if(\Auth::user()->can('generate bulk payment farmers')){
        // Fetch venders with positive balance
        $venders = Vender::getVendersWithPositiveBalance();
        
        $paymentBatch = new PaySlipFarmerBatch();
        $paymentBatch->batch_id = 'BULK00'. $this->latestBatchId();
        $paymentBatch->status = 0;
        $paymentBatch->created_by = 2;
        $paymentBatch->save();
        
        foreach ($venders as $vender) {
            $paymentBatchItem = new PaySlipFarmerBatchItem();
            $paymentBatchItem->vender_id = $vender->id;
            $paymentBatchItem->pay_slip_farmer_batch_id =  $paymentBatch->id;
            $paymentBatchItem->amount = $vender->book_balance;
            $paymentBatchItem->status = 0;
            // Query to fetch the IDs
            $purchaseIds = DB::table('purchases')
                ->where('vender_id', $vender->id)
                ->where('status', '!=', 4)
                ->pluck('id')
                ->toArray();
            
            // Convert the IDs to a comma-separated string
            $commaSeparatedIds = implode(',', $purchaseIds);
            $paymentBatchItem->purchase_items_ids = $commaSeparatedIds;
            $paymentBatchItem->created_by = 2;
            $paymentBatchItem->save();
            $vender->book_balance = ($vender->book_balance - $vender->book_balance);
            $vender->save();
            
        }
        
        
        return redirect()->back()->with('success', __('Bulk payment successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }
    
    
     /**
    * Generate bulkpayment for selected vendors 
    * with positive balance in Collection Center
    * 
    **/
    public function centerBulkPayStore(Request $request)
    {
        if(\Auth::user()->can('generate bulk payment farmers')){
       
        // $request->validate([
        //     'vendor_ids' => 'required|array',
        //     'center' => 'required|string',
        //     'vendor_ids.*' => 'exists:venders,id', // Ensures each ID exists in the `venders` table
        // ]);
        
        // Retrieve the selected vendor IDs
        $vendorIds = $request->input('vendor_ids', []);
        $center = $request->input('center');
        
        // Fetch the vendors whose IDs are in the array
        $venders = Vender::whereIn('id', $vendorIds)->get();
        
        $paymentBatch = new PaySlipFarmerBatch();
        $paymentBatch->batch_id ='BULK00'. $this->latestBatchId();
        $paymentBatch->status = 0;
        $paymentBatch->created_by = 2;
        $paymentBatch->save();
        
        foreach ($venders as $vender) {
            $paymentBatchItem = new PaySlipFarmerBatchItem();
            $paymentBatchItem->vender_id = $vender->id;
            $paymentBatchItem->pay_slip_farmer_batch_id =  $paymentBatch->id;
            $paymentBatchItem->amount = $vender->book_balance;
            $paymentBatchItem->status = 0;
            // Query to fetch the IDs
            $purchaseIds = DB::table('purchases')
                ->where('vender_id', $vender->id)
                ->where('status', '!=', 4)
                ->pluck('id')
                ->toArray();
            
            // Convert the IDs to a comma-separated string
            $commaSeparatedIds = implode(',', $purchaseIds);
            $paymentBatchItem->purchase_items_ids = $commaSeparatedIds;
            $paymentBatchItem->created_by = 2;
            $paymentBatchItem->save();
            $vender->book_balance = ($vender->book_balance - $vender->book_balance);
            $vender->save();
            
        }
        
        
        return redirect()->back()->with('success', __('Bulk payment successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }
    
    
     public function multipleCenterBulkPayStore(Request $request)
    {
        if(\Auth::user()->can('generate bulk payment farmers')){
       
        $selectedCenters = $request->input('collection_center');

        if (!$selectedCenters) {
            return redirect()->back()->with('error', 'No centers selected.');
        }
    
         // Fetch vendors with a positive book balance from the selected collection centers
         $venders = Vender::whereIn('collection_centre', $selectedCenters)
                     ->where('book_balance', '>', 0)
                     ->get();
        
        
        if ($venders->isEmpty()) {
            return redirect()->back()->with('error', 'No vendors found with a positive balance in the selected centers.');
        }
        
        $paymentBatch = new PaySlipFarmerBatch();
        $paymentBatch->batch_id ='BULK00'. $this->latestBatchId();
        $paymentBatch->status = 0;
        $paymentBatch->created_by = 2;
        $paymentBatch->save();
        
        foreach ($venders as $vender) {
            $paymentBatchItem = new PaySlipFarmerBatchItem();
            $paymentBatchItem->vender_id = $vender->id;
            $paymentBatchItem->pay_slip_farmer_batch_id =  $paymentBatch->id;
            $paymentBatchItem->amount = $vender->book_balance;
            $paymentBatchItem->status = 0;
            // Query to fetch the IDs
            $purchaseIds = DB::table('purchases')
                ->where('vender_id', $vender->id)
                ->where('status', '!=', 4)
                ->pluck('id')
                ->toArray();
            
            // Convert the IDs to a comma-separated string
            $commaSeparatedIds = implode(',', $purchaseIds);
            $paymentBatchItem->purchase_items_ids = $commaSeparatedIds;
            $paymentBatchItem->created_by = 2;
            $paymentBatchItem->save();
            $vender->book_balance = ($vender->book_balance - $vender->book_balance);
            $vender->save();
            
        }
        
        
        return redirect()->back()->with('success', __('Bulk payment successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }
    
    
    public function showPayslip($id)
    {
        if(\Auth::user()->can('manage payment farmers')){
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Payslip Not Found.'));
        }
        // $id = Crypt::decrypt($id); 
        $payslip = PaySlipFarmerBatch::where('id', $id)->first();
        $payslipItems = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', '=', $id)->get();
        
        $result = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', $id)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $failed = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', $id)
        ->where('txn_status', 'FAILED')
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $reversed = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', $id)
        ->where('status', 6)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $totalCount = $result->total_count; // Total number of items
        $totalSum = $result->total_sum;    // Total sum of 'amount' column
        
        $failedCount = $failed->total_count;
        $failedTotalSum = $failed->total_sum;
        
        $reversedCount = $reversed->total_count;
        $reveresedTotalSum = $reversed->total_sum;
        return view('farmerspayslip.show', compact('payslip', 'payslipItems', 'totalCount', 'totalSum', 'failedCount', 'failedTotalSum', 'reversedCount'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    
    /**
     * Displays list farmers in collection center 
     * with positive balance
     */
     public function collectionCenterWithBalance($center)
    {
        if(\Auth::user()->can('manage payment farmers')){
        $venders = Vender::where('collection_centre', $center)
                         ->where('book_balance', '>', 0)
                         ->get();
    
        // Return view or JSON response
        return view('farmerspayslip.centerlist', compact('venders', 'center'));
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Payslip reversal page 
     * Requires OTP to proceed
     */
public function reversPaylipPage($id)
{
    if (\Auth::user()->can('reverse fail payment farmers')) {
        $payslip = PaySlipFarmerBatchItem::find(Crypt::decrypt($id));

        if (!$payslip) {
            return redirect()->back()->with('error', __('Payslip not found.'));
        }
        
        if ($payslip->status == 6) {
            return redirect()->back()->with('error', __('Payslip already reversed.'));
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
            Mail::to(\Auth::user()->email)->send(new OTPMail($otp, $payslip));

            return view('farmerspayslip.reversalpage', compact('payslip'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to send OTP email: ') . $e->getMessage());
        }
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


    



public function processReversal(Request $request)
{
    if (\Auth::user()->can('reverse fail payment farmers')) {
        // Retrieve OTP from session
    $storedOtp = Session::get('otp');
    $otpExpires = Session::get('otp_expires');

    if (!$storedOtp || now()->greaterThan($otpExpires)) {
        return redirect()->back()->with('error', 'OTP has expired. Please request a new one.');
    }

    if ($request->otp != $storedOtp) {
        return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
    }

    // OTP is valid - process reversal
    Session::forget(['otp', 'otp_expires']);

    $payslip = PaySlipFarmerBatchItem::find(Crypt::decrypt($request->payslip_id));

    if (!$payslip) {
        return redirect()->back()->with('error', __('Payslip not found.'));
    }

    if ($payslip->status == 6) {
        return redirect()->back()->with('error', __('Payslip already reversed.'));
    }
    
    // Check status of the transaction from the gateway
    $monnify = new Monnify();
    $result = $monnify->getSingleTransaction($payslip->reference);
    $response = $result->getData();

    if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
        // Get transaction details
        $responseBody = $response->details->responseBody;

        if ($responseBody->status === 'FAILED') {
            DB::beginTransaction(); // Start transaction

            try {
                Log::info("Processing reversal for payslip ID: {$payslip->id}");

                $purchaseIds = explode(",", $payslip->purchase_items_ids);
                foreach ($purchaseIds as $purchaseId) {
                    // Query and update Purchase model
                    $purchase = Purchase::find($purchaseId);
                    if ($purchase) {
                        $purchase->status = 1;
                        $purchase->save();
                        
                        // Update Purchase Payment
                        $purchasePayment = PurchasePayment::where('purchase_id', $purchase->id)->first();
                        if ($purchasePayment) {
                            $purchasePayment->description = 'Reversed. Failed Payment';
                            $purchasePayment->reference = $payslip->reference;
                            $purchasePayment->save();
                        } else {
                            Log::warning("Purchase payment not found for purchase ID: {$purchase->id}");
                        }
                    } else {
                        Log::warning("Purchase not found for ID: {$purchaseId}");
                    }
                }

                // Update payslip status
                $payslip->status = 6;
                $payslip->save();

                // Credit Vendor balance
                $vender = Vender::find($payslip->vender_id);
                if ($vender) {
                    $vender->balance += $payslip->amount;
                    $vender->book_balance += $payslip->amount;
                    $vender->save();
                } else {
                    Log::warning("Vendor not found for ID: {$payslip->vender_id}");
                    throw new \Exception("Vendor not found.");
                }

                DB::commit(); // Commit transaction if all is successful
                Log::info("Reversal successful for payslip ID: {$payslip->id}");

                return redirect()->back()->with('success', 'Reversal processed successfully.');
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback transaction if an error occurs
                Log::error("Transaction failed: " . $e->getMessage());
                return redirect()->back()->with('error', 'Transaction failed: ' . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Not a FAILED transaction!'));
        }
    } else {
        return redirect()->back()->with('error', __($response->details->responseMessage));
    }
        
    }else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
    
}


    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPayslipItems($id)
    {
        if(\Auth::user()->can('manage payment farmers')){
             try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Purchased Items Not Found.'));
        }
        
        $payslipItems = PaySlipFarmerBatchItem::where('id', '=', $id)->pluck('purchase_items_ids');
        $status = Purchase::$statues;
        $purchases = Purchase::where('created_by', '=', \Auth::user()->creatorId())->with(['vender','category'])->get();
        // Convert the collection of comma-separated strings into an array of IDs
        $purchaseIds = collect($payslipItems)
            ->flatMap(function ($item) {
                return explode(',', $item); // Split each string into an array of IDs
            })
            ->unique() // Remove duplicate IDs
            ->toArray();
       
        // Query the Purchase model
        $purchases = Purchase::whereIn('id', $purchaseIds)->get();


        return view('farmerspayslip.purchases', compact('purchases', 'status'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
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
            $payslip = PaySlipFarmerBatch::where('id', $id)->first();
            $payslip->status = 1;
            $payslip->save();
            
            PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', $id)
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
        if(\Auth::user()->can('initialise payment farmers')){
            try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            
            
            // Prepare the payload
            $beneficiaries = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', $id)->get();
            $payLoad = [];
            foreach ($beneficiaries as $beneficiary) {
                $metaData = [
                    'venderId' => $beneficiary->vender->id,
                    'paymentBatchItemId' => $beneficiary->id,
                ];
            
                $payLoad[] = [
                    'reference' => $this->generateValidReference(), 
                    'narration' => json_encode($metaData),  
                    'destinationAccountNumber' => $beneficiary->vender->bank_account, 
                    'destinationBankCode' => $beneficiary->vender->bank_code, 
                    'amount' => $beneficiary->amount,
                    'metaData' => $metaData,
                    'currency' => 'NGN'
                ];
            }
    
            
            //Prepare post data
             $postData = [
                'title' => 'Bulk Payment to Vendors',
                'batchReference' =>  $this->generateValidReference(),
                'narration' => 'Farmer Payout',
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
                    $payslip = PaySlipFarmerBatch::where('id', $id)->first();
                    $payslip->batch_reference = $responseBody->batchReference;
                    $payslip->total_fee = $responseBody->totalFee;
                    $payslip->status = 3;
                    $payslip->save();
                    $this->updatePaylipItems($responseBody->batchReference, 3);
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
         if(\Auth::user()->can('authorise payment farmers')){
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
                // Retrieve the payslip batch
                $payslipBatchId = Crypt::decrypt($request->payslip_batch_id);
                $payslipBatch = PaySlipFarmerBatch::findOrFail($payslipBatchId);
        
                $monnify = new Monnify();
        
                // Call Monnify bulk payment authorization
                $result = $monnify->bulkPaymentAuthorize($payslipBatch->batch_reference, $request->otp);
                $response = $result->getData();
                if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
                    $responseBody = $response->details->responseBody;
        
                    // Update the payslip batch with the Monnify response details
                    $payslipBatch->status = 4; 
                    $payslipBatch->save();
                    PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id',  $payslipBatch->id)
                    ->update([
                        'status' => 4,
                    ]);
                    
                    $payslipItems = PaySlipFarmerBatchItem::where('pay_slip_farmer_batch_id', '=', $payslipBatch->id)->get();
                    foreach($payslipItems as $payslipItem){
                        $purchaseIds = explode("," ,$payslipItem->purchase_items_ids);
                        foreach($purchaseIds as $purchaseId){
                            // Query the Purchase model
                            $purchase = Purchase::find($purchaseId);
                            $paymentRegister = (object)[
                                'date' =>  $responseBody->dateCreated,
                                'amount' => $purchase->getDue(),
                                'account_id' => 2,
                                'reference' => $payslipItem->reference,
                                'description' => 'Farmer Payout'
                            ];
                            
                            $this->registerPayment($paymentRegister, $purchaseId);
                        }
                   
                       
                    }
                    
                    //request the the bulk transfer transactions
                    $transactions = $monnify->getBulkTransferTransactions($responseBody->batchReference);
                    $transactions = $transactions->getData();
                    $transactionData = $transactions->details->responseBody;
                    
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
                    // Handle Monnify-specific error response
                    return response()->json([
                        'success' => false,
                        'message' => $response->responseMessage ?? 'Bulk transfer authorization failed',
                        'details' => $response->responseBody ?? []
                    ], 400);
                }
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while authorizing the payment.',
                    'error' => $e->getMessage()
                ], 500);
            }
             
         }
         else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
       
    }

    public function resendToken(Request $request)
    {
        if(\Auth::user()->can('resend token farmers')){
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
    
    public function validateBankAccount(Request $request)
    {
            $monnify = new Monnify();
    
            // Call Monnify bulk payment authorization
            $result = $monnify->validateBankAccount($request->bankCode, $request->accountNumber);
            $response = $result->getData();
            if ($response->details->requestSuccessful && $response->details->responseCode === '0') {
            $responseBody = $response->details->responseBody;

            return response()->json([
                'success' => true,
                'data' => [
                    'accountName' => $responseBody->accountName
                ]
            ], 200);
            
            } else {
                // Handle Monnify-specific error response
                return response()->json([
                    'success' => false,
                    'message' => $response->details->responseMessage,
                    'details' => $response->responseBody ?? []
                ], 400);
            }
    }
    
    public function updatePaylipItems($batchRef, $status)
    {
        $monnify = new Monnify();
        $result = $monnify->getBulkTransferTransactions($batchRef);
        $response = $result->getData();
        $transactionData = $response->details->responseBody;
        // echo '<pre>';
        // var_dump($transactionData);
        // echo '</pre>';
        $bacthItems = [];
        foreach ($transactionData->content as $item) {
            // Decode the narration JSON
            $narrationData = json_decode($item->narration, true);
        
            if ($narrationData) {
                // Store the reference and narration data
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => $narrationData,
                    'status' => $item->status,
                ];
            } else {
                // Handle invalid JSON in narration
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => "Invalid JSON format"
                ];
            }
        }

        foreach($bacthItems as $item){
            //update batchpayslipbatch Item status and ref
            //grab purchases from batchpayslipbatch->purchase_item_ids 
            // register there payments reference
          $paymentBatchItem = PaySlipFarmerBatchItem::where('id', $item['narration']['paymentBatchItemId'])->first();
        
            if ($paymentBatchItem) {
                // Update the status and reference
                $paymentBatchItem->status = $status;
                $paymentBatchItem->txn_status = $item['status'];
                $paymentBatchItem->reference = $item['reference'];
                $paymentBatchItem->save();
            }
        }
                
    }
    
    
    public function revalidatePaylipItems($batchRef)
    {
         if(\Auth::user()->can('initialise payment farmers')) {

            
            $this->updatePaylipTxn($batchRef);
             
            return redirect()->back()->with('success', __('Transaction status successfuly updated.'));
         }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }    
    }
    
    public function updatePaylipTxn($batchRef)
    {
        if(\Auth::user()->can('initialise payment farmers')){
        try {
            $batchRef = Crypt::decrypt($batchRef);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Reference error.'));
        }
        
        $monnify = new Monnify();
        $result = $monnify->getBulkTransferTransactions($batchRef);
        $response = $result->getData();
        $transactionData = $response->details->responseBody;

        $bacthItems = [];
        foreach ($transactionData->content as $item) {
            // Decode the narration JSON
            $narrationData = json_decode($item->narration, true);
        
            if ($narrationData) {
                // Store the reference and narration data
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => $narrationData,
                    'transactionDescription' => $item->transactionDescription ?? '',
                    'status' => $item->status,
                ];
            } else {
                // Handle invalid JSON in narration
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => "Invalid JSON format"
                ];
            }
        }

        foreach($bacthItems as $item){
            //update batchpayslipbatch Item status and ref
            //grab purchases from batchpayslipbatch->purchase_item_ids 
            // register there payments reference
          $paymentBatchItem = PaySlipFarmerBatchItem::where('id', $item['narration']['paymentBatchItemId'])->first();
        
            if ($paymentBatchItem) {
                $paymentBatchItem->txn_status = $item['status'];
                $paymentBatchItem->reference = $item['reference'];
                $paymentBatchItem->txn_description = $item['transactionDescription'];
                $paymentBatchItem->save();
            }
        }
        return redirect()->back()->with('success', __('Transaction status successfuly updated.'));
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

                
    }
    
    private function generateValidReference()
    {
        return 'MCP_' . str_replace('.', '_', uniqid()); // Replaces dots with underscores
    }
    
    public function registerPayment($request, $purchase_id)
    {

            $purchasePayment                 = new PurchasePayment();
            $purchasePayment->purchase_id        = $purchase_id;
            $purchasePayment->date           = $request->date;
            $purchasePayment->amount         = $request->amount;
            $purchasePayment->account_id     = $request->account_id;
            $purchasePayment->payment_method = 0;
            $purchasePayment->reference      = $request->reference;
            $purchasePayment->description    = $request->description;
            $purchasePayment->save();

            $purchase  = Purchase::where('id', $purchase_id)->first();
            $due   = $purchase->getDue();
            $total = $purchase->getTotal();

            if($purchase->status == 0)
            {
                $purchase->send_date = date('Y-m-d');
                $purchase->save();
            }

            if($due <= 0)
            {
                $purchase->status = 4;
                $purchase->save();
            }
            else
            {
                $purchase->status = 3;
                $purchase->save();
            }
            $purchasePayment->user_id    = $purchase->vender_id;
            $purchasePayment->user_type  = 'Vender';
            $purchasePayment->type       = 'Partial';
            $purchasePayment->created_by = \Auth::user()->id;
            $purchasePayment->payment_id = $purchasePayment->id;
            $purchasePayment->category   = 'Bill';
            $purchasePayment->account    = $request->account_id;
            Transaction::addTransaction($purchasePayment);

            $vender = Vender::where('id', $purchase->vender_id)->first();

            $payment         = new PurchasePayment();
            $payment->name   = $vender['name'];
            $payment->method = '-';
            $payment->date   = \Auth::user()->dateFormat($request->date);
            $payment->amount = \Auth::user()->priceFormat($request->amount);
            $payment->bill   = 'bill ' . \Auth::user()->purchaseNumberFormat($purchasePayment->purchase_id);

            Utility::userBalance('vendor', $purchase->vender_id, $request->amount, 'debit');

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            // Send Email
            $setings = Utility::settings();
            if($setings['new_bill_payment'] == 1)
            {

                $vender = Vender::where('id', $purchase->vender_id)->first();
                $billPaymentArr = [
                    'vender_name'   => $vender->name,
                    'vender_email'  => $vender->email,
                    'payment_name'  =>$payment->name,
                    'payment_amount'=>$payment->amount,
                    'payment_bill'  =>$payment->bill,
                    'payment_date'  =>$payment->date,
                    'payment_method'=>$payment->method,
                    'company_name'=>$payment->method,

                ];


                //$resp = Utility::sendEmailTemplate('new_bill_payment', [$vender->id => $vender->email], $billPaymentArr);

            }


    }

    public function pdf($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

       // dd($employee);

        $payslipDetail = Utility::employeePayslipDetail($id,$month);


        return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail'));
    }



    public function export(Request $request)
    {
        $name = 'payslip_' . date('Y-m-d i:h:s');
        $data = Excel::download(new PayslipExport($request), $name . '.xlsx'); ob_end_clean();
        return $data;
    }
    
    public function latestBatchId()
    {
        $latest = PaySlipFarmerBatch::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    
    public function updatePaylipItemsBak($batchRef)
    {
        $monnify = new Monnify();
        $result = $monnify->getBulkTransferTransactions($batchRef);
        $response = $result->getData();
        $transactionData = $response->details->responseBody;
        // echo '<pre>';
        // var_dump($transactionData);
        // echo '</pre>';
        $bacthItems = [];
        foreach ($transactionData->content as $item) {
            // Decode the narration JSON
            $narrationData = json_decode($item->narration, true);
        
            if ($narrationData) {
                // Store the reference and narration data
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => $narrationData
                ];
            } else {
                // Handle invalid JSON in narration
                $bacthItems[] = [
                    'reference' => $item->reference,
                    'narration' => "Invalid JSON format"
                ];
            }
        }
        
        
        foreach($bacthItems as $item){
            //update batchpayslipbatch Item status and ref
            //grab purchases from batchpayslipbatch->purchase_item_ids 
            // register there payments reference
          $paymentBatchItem = PaySlipFarmerBatchItem::where('id', $item['narration']['paymentBatchItemId'])->first();
        
            if ($paymentBatchItem) {
                // Update the status and reference
                $paymentBatchItem->status = 3;
                $paymentBatchItem->reference = $item['reference'];
                $paymentBatchItem->save();
            }
            // $purchaseIds = explode("," ,$paymentBatchItem->purchase_items_ids);
            //             foreach($purchaseIds as $purchaseId){
            //                 // Query the Purchase model
            //                 $purchase = Purchase::find($purchaseId);
            //                 $paymentRegister = (object)[
            //                     'date' =>  date('Y-m-d H:i:s'),
            //                     'amount' => $purchase->getDue(),
            //                     'account_id' => 2,
            //                     'reference' => $item['reference'],
            //                     'description' => 'Farmer Payout'
            //                 ];
                            
            //                 $this->registerPayment($paymentRegister, $purchaseId);
            // }
        }

    }

    public function reconciliation()
    {
        if (! \Auth::user()->can('manage payslip')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $creatorId = \Auth::user()->creatorId();

        $batches = PaySlipFarmerBatch::where('created_by', $creatorId)
            ->whereIn('status', [1, 2])
            ->latest()
            ->get()
            ->map(function ($batch) {
                $items          = PaySlipFarmerBatchItem::where('batch_id', $batch->batch_id)->get();
                $expectedTotal  = $items->sum('net_salary');
                $actualPaid     = $items->where('status', 'PAID')->sum('net_salary');
                $failedCount    = $items->where('status', 'FAILED')->count();
                $pendingCount   = $items->whereNotIn('status', ['PAID', 'FAILED'])->count();

                return [
                    'batch'          => $batch,
                    'expected_total' => $expectedTotal,
                    'actual_paid'    => $actualPaid,
                    'failed_count'   => $failedCount,
                    'pending_count'  => $pendingCount,
                    'matched'        => abs($expectedTotal - $actualPaid) < 1,
                ];
            });

        return view('payslip.farmer.reconciliation', compact('batches'));
    }
}
