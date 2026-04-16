<?php

namespace App\Http\Controllers;

use App\Exports\VenderExport;
use App\Imports\VenderImport;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\Rider;
use App\Models\RiderTrip;
use App\Models\State;
use App\Models\Lga;
use App\Models\PaySlipRiderBatch;
use App\Models\PaySlipRiderBatchItem;
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
use Illuminate\Support\Facades\Session;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Log;

class RiderController extends Controller
{


    public function index()
    {
        if(\Auth::user()->can('manage rider'))
        {
            $riders = Rider::where('created_by', \Auth::user()->creatorId())
                ->where('is_active', 1)
                ->withCount(['trips as pending_trips_count' => function($query) {
                    $query->where('status', 0);
                }])
                ->with(['state', 'lga']) // Ensure these relationships are loaded
                ->get();
    
            $payslip_batches = PaySlipRiderBatch::where('created_by', \Auth::user()->creatorId())->get();
    
            return view('rider.index', [
                'riders' => $riders,
                'payslip_batches' => $payslip_batches,
                'total_riders' => $riders->count() // Add total count here
            ]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create rider'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
            $state = State::all();
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
            return view('rider.create', compact('customFields', 'state', 'bankList'));        
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create rider')) {
            $rules = [
                'name' => 'required',
                'contact' => 'required',
                'bank_name' => 'required',
                'bank_code' => 'required',
                'bank_account' => 'required',
                'account_name' => 'required',
                'email' => [
                    'required',
                    Rule::unique('riders')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    }),
                ],
                'amount_per_trip' => 'required',
                'state_id' => 'required',
                'lga_id' => 'required',
                'user_type' => 'required'
            ];
    
            $validator = \Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('rider.index')->with('error', $messages->first());
            }
    
            $authUser = \Auth::user();
            $creator = User::find($authUser->creatorId());

    
            $rider = new \App\Models\Rider();
            $rider->rider_id = $this->riderNumber();
            $rider->name = $request->name;
            $rider->contact = $request->contact;
            $rider->email = $request->email;
            $rider->bank_name = $request->bank_name;
            $rider->bank_code = $request->bank_code;
            $rider->bank_account = $request->bank_account;
            $rider->account_name = $request->account_name;
            $rider->tax_number = $request->tax_number;
            $rider->billing_name = $request->billing_name;
            $rider->billing_country = $request->billing_country;
            $rider->billing_state = $request->billing_state;
            $rider->billing_city = $request->billing_city;
            $rider->billing_phone = $request->billing_phone;
            $rider->billing_zip = $request->billing_zip;
            $rider->billing_address = $request->billing_address;
            $rider->created_by = $authUser->creatorId();
            $rider->amount_per_trip = $request->amount_per_trip;
            $rider->state_id = $request->state_id;
            $rider->lga_id = $request->lga_id;
            $rider->type = $request->user_type;
    
    
            // Handle Image Upload
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('uploads/riders'), $imageName);
                $rider->image = $imageName;
            }
    
            $rider->save();
            CustomField::saveData($rider, $request->customField);
    
            return redirect()->route('rider.index')->with('success', __('Rider successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        try {
            $id = \Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Rider Not Found.'));
        }
    
        $rider = Rider::with(['state', 'lga', 'trips', 'pendingTrips'])->find($id);
        
        if (!$rider) {
            return redirect()->back()->with('error', __('Rider Not Found.'));
        }
    
        // Check if the pending-trips tab was requested
        $showPending = request()->has('tab') && request()->get('tab') === 'pending-trips';
        
        // Get trips based on the filter
        $trips = $showPending ? $rider->pendingTrips : $rider->trips;
        
        // Counts for the tabs
        $tripCounts = [
            'all' => $rider->trips->count(),
            'pending' => $rider->pendingTrips->count()
        ];
    
        return view('rider.show', [
            'rider' => $rider,
            'trips' => $trips,
            'tripCounts' => $tripCounts,
            'activeTab' => $showPending ? 'pending-trips' : 'all-trips'
        ]);
    }


    public function edit($id)
    {
        if (!\Auth::user()->can('edit rider')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        $rider = Rider::findOrFail($id);
        
    
        $rider->customField = CustomField::getData($rider, 'rider');
        $customFields = CustomField::where('created_by', \Auth::user()->creatorId())
                                   ->where('module', 'rider')
                                   ->get();
    
        $selectedBankCode = $rider->bank_code;
        $selectedState = $rider->state_id;
        $selectedLga = $rider->lga_id;
        $state = State::all();
        $lga = Lga::where('state_id', $selectedState)->get();
    
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
    
        return view('rider.edit', compact(
            'rider',
            'customFields',
            'bankList',
            'selectedBankCode',
            'state',
            'lga',
            'selectedState',
            'selectedLga'
        ));
    }



    public function update(Request $request, Rider $rider)
    {
        if (!\Auth::user()->can('edit rider')) {
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
                Rule::unique('riders')->where(function ($query) {
                    return $query->where('created_by', \Auth::user()->id);
                }),
            ],
            //'collection_centre' => 'required|exists:warehouses,id', // Ensure valid warehouse ID
            'amount_per_trip' => 'required',
            // Only validate image if the input is provided
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'state_id' => 'required',
            'lga_id' => 'required',
            'user_type' => 'required',
        ];
    
        // Perform validation
        $validator = \Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->route('rider.index')->with('error', $validator->getMessageBag()->first());
        }
    
        // Update rider details
        $rider->fill([
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
            'amount_per_trip' => $request->amount_per_trip,
            'state_id' => $request->state_id,
            'lga_id' => $request->lga_id,
            'type' => $request->user_type
        ]);
        
        // Handle Image Upload (if exists)
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($rider->image && file_exists(public_path('uploads/riders/' . $rider->image))) {
                unlink(public_path('uploads/riders/' . $rider->image));
            }
    
            // Save new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/riders'), $imageName);
            $rider->image = $imageName;
        }
    
        $rider->save();
    
        // Save custom fields
        CustomField::saveData($rider, $request->customField);
    
        return redirect()->route('rider.index')->with('success', __('Rider successfully updated.'));
    }

    public function destroy(Rider $rider)
    {
        if (\Auth::user()->can('delete rider')) {
            if ($rider->created_by == \Auth::user()->creatorId()) {
    
                // Check if the rider has any payslip records
                $hasPayslips = PaySlipRiderBatchItem::where('rider_id', $rider->id)->exists();
    
                if ($hasPayslips) {
                    $rider->is_active = 0;
                    $rider->save();
                    return redirect()->back()->with('error', __('Rider cannot be deleted due to existing payslip records. The rider has been deactivated instead.'));
                }
    
                $rider->delete();
    
                return redirect()->route('rider.index')->with('success', __('Rider successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    // Function to get the latest rider number
    // This generates the next rider ID by finding the most recently created rider and incrementing the ID.
    function riderNumber()
    {
        $latest = Rider::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->rider_id + 1;
    }
    
    // Function to get the latest payment batch ID
    // This generates the next payment batch ID by finding the most recent batch and incrementing the ID.
    public function latestBatchId()
    {
        $latest = PaySlipRiderBatch::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    
    public function bulkPaymentHome() 
    {
        if(\Auth::user()->can('manage rider'))
        {
            $riders = Rider::where('created_by', \Auth::user()->creatorId())->get();
            $payslip_batches = PaySlipRiderBatch::where('created_by', \Auth::user()->creatorId())->get();
            $total_awaiting = Rider::getTotalBalance();
             
            // Get the count of venders with balance greater than 0
            $riders_with_positive_balance = Rider::countWithPositiveBalance();
           $collectionCenterPayment = Rider::select(
                'lgas.name as collection_centre', 'riders.lga_id',
                \DB::raw('SUM(book_balance) as total_balance'),
                \DB::raw('COUNT(CASE WHEN book_balance > 0 THEN 1 END) as positive_balance_count')
            )
            ->join('lgas', 'riders.lga_id', '=', 'lgas.id')
            ->where('book_balance', '>', 0)
            ->groupBy('lgas.name')
            ->get();

            return view('rider.bulk_payment_home', compact('riders', 'payslip_batches', 'riders_with_positive_balance', 'collectionCenterPayment', 'total_awaiting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Displays list riders in lga  
     * with positive balance
     */
     public function lgaWithBalance($lga)
    {
        if(\Auth::user()->can('manage payment farmers')){
        $riders = Rider::where('lga_id', $lga)
                         ->where('book_balance', '>', 0)
                         ->get();
    
        // Return view or JSON response
        return view('rider.lga_list', compact('riders', 'lga'));
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    /**
    * Generate bulkpayment for selected rider 
    * with positive balance in Lga 
    * 
    **/
    public function lgaBulkPayStore(Request $request)
    {
        if(\Auth::user()->can('generate bulk payment farmers')){
    
            // $request->validate([
            //     'vendor_ids' => 'required|array',
            //     'center' => 'required|string',
            //     'vendor_ids.*' => 'exists:venders,id', // Ensures each ID exists in the `venders` table
            // ]);
    
            // Retrieve the selected vendor IDs
            $riderIds = $request->input('rider_ids', []);
            // $center = $request->input('center');
    
            // Fetch the vendors whose IDs are in the array
            $riders = Rider::whereIn('id', $riderIds)->get();
    
            DB::beginTransaction();
    
            try {
                $paymentBatch = new PaySlipRiderBatch();
                $paymentBatch->batch_id = 'BULK-RD-00' . $this->latestBatchId();
                $paymentBatch->status = 0;
                $paymentBatch->created_by = 2;
                $paymentBatch->save();
    
                foreach ($riders as $rider) {
                    $paymentBatchItem = new PaySlipRiderBatchItem();
                    $paymentBatchItem->rider_id = $rider->id;
                    $paymentBatchItem->pay_slip_rider_batch_id = $paymentBatch->id;
                    $paymentBatchItem->amount = $rider->book_balance;
                    $paymentBatchItem->status = 0;
    
                    // Get trip IDs
                    $tripIds = DB::table('rider_trips')
                                ->where('rider_id', $rider->id)
                                ->where('status', '!=', 4)
                                ->pluck('id')
                                ->toArray();
    
                    $paymentBatchItem->trip_ids = implode(',', $tripIds);
                    $paymentBatchItem->created_by = \Auth::id(); // Optional
                    $paymentBatchItem->save();
                    
                    // Update their status to 1
                    DB::table('rider_trips')
                        ->whereIn('id', $tripIds)
                        ->update(['status' => 2]);
    
                    // Reset rider's book balance
                    $rider->book_balance = 0;
                    $rider->save();
                }
    
                DB::commit();
    
                return redirect()->back()->with('success', __('Bulk payment successfully created.'));
    
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Bulk Payment Error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'An error occurred during bulk payment processing.');
            }
    
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    

    public function multipleLgaBulkPayStore2(Request $request)
    {
        if (!\Auth::user()->can('generate bulk payment rider')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    
        $selectedCenters = $request->input('collection_center');
    
        if (!$selectedCenters) {
            return redirect()->back()->with('error', 'No L.G.A selected.');
        }
    
        // Fetch riders with a positive book balance from the selected LGAs
        $riders = Rider::whereIn('lga_id', $selectedCenters)
                    ->where('book_balance', '>', 0)
                    ->get();
    
        if ($riders->isEmpty()) {
            return redirect()->back()->with('error', 'No rider found with a positive balance in the selected LGA.');
        }
    
        DB::beginTransaction();
    
        try {
            $paymentBatch = new PaySlipRiderBatch();
            $paymentBatch->batch_id = 'BULK-RD-00' . $this->latestBatchId();
            $paymentBatch->status = 0;
            $paymentBatch->created_by = \Auth::id(); // Optional: replace 2 with logged in user ID
            $paymentBatch->save();
    
            foreach ($riders as $rider) {
                $paymentBatchItem = new PaySlipRiderBatchItem();
                $paymentBatchItem->rider_id = $rider->id;
                $paymentBatchItem->pay_slip_rider_batch_id = $paymentBatch->id;
                $paymentBatchItem->amount = $rider->book_balance;
                $paymentBatchItem->status = 0;
    
                // Get trip IDs
                $tripIds = DB::table('rider_trips')
                            ->where('rider_id', $rider->id)
                            ->where('status', '=', 2)
                            ->pluck('id')
                            ->toArray();
                
                
    
                $paymentBatchItem->trip_ids = implode(',', $tripIds);
                $paymentBatchItem->created_by = \Auth::id(); // Optional
                $paymentBatchItem->save();
                
                // Update their status to 1
                DB::table('rider_trips')
                    ->whereIn('id', $tripIds)
                    ->update(['status' => 1]);
    
                // Reset rider's book balance
                $rider->book_balance = 0;
                $rider->save();
            }
    
            DB::commit();
    
            return redirect()->back()->with('success', __('Bulk payment successfully created.'));
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Payment Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred during bulk payment processing.');
        }
    }
    
    public function multipleLgaBulkPayStore(Request $request)
    {
        if(\Auth::user()->can('generate bulk payment rider')){
       
        $selectedCenters = $request->input('collection_center');

        if (!$selectedCenters) {
            return redirect()->back()->with('error', 'No LGA selected.');
        }
    
         // Fetch vendors with a positive book balance from the selected collection centers
         $riders = Rider::whereIn('lga_id', $selectedCenters)
                     ->where('book_balance', '>', 0)
                     ->get();
        
        
        if ($riders->isEmpty()) {
            return redirect()->back()->with('error', 'No rider found with a positive balance in the selected LGA.');
        }
        
        $paymentBatch = new PaySlipRiderBatch();
        $paymentBatch->batch_id ='BULK00'. $this->latestBatchId();
        $paymentBatch->status = 0;
        $paymentBatch->created_by = 2;
        $paymentBatch->save();
        
        foreach ($riders as $rider) {
            $paymentBatchItem = new PaySlipRiderBatchItem();
            $paymentBatchItem->rider_id = $rider->id;
            $paymentBatchItem->pay_slip_rider_batch_id =  $paymentBatch->id;
            $paymentBatchItem->amount = $rider->book_balance;
            $paymentBatchItem->status = 0;
            // Query to fetch the IDs
            $tripIds = DB::table('rider_trips')
                ->where('rider_id', $rider->id)
                ->where('status', '!=', 4)
                ->pluck('id')
                ->toArray();
                
            // Update their status to 1
            DB::table('rider_trips')
                    ->whereIn('id', $tripIds)
                    ->update(['status' => 2]); 
            
            // Convert the IDs to a comma-separated string
            $commaSeparatedIds = implode(',', $tripIds);
            $paymentBatchItem->trip_ids = $commaSeparatedIds;
            $paymentBatchItem->created_by = 2;
            $paymentBatchItem->save();
            $rider->book_balance = ($rider->book_balance - $rider->book_balance);
            $rider->save();
            
        }
        
        
        return redirect()->back()->with('success', __('Bulk payment successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        

    }

    public function bulkPaymentShow($id)
    {
        if(\Auth::user()->can('manage payment rider')){
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Payslip Not Found.'));
        }
        
        $payslip = PaySlipRiderBatch::where('id', $id)->first();
        if (!empty($payslip->reference)) {
            $this->updatePaylipTxn($payslip->reference);
        }
        
        $payslip = PaySlipRiderBatch::where('id', $id)->first();
        $payslipItems = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', '=', $id)->get();
        
        $result = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $id)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $failed = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $id)
        ->where('txn_status', 'FAILED')
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $reversed = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $id)
        ->where('status', 6)
        ->selectRaw('COUNT(*) as total_count, SUM(amount) as total_sum')
        ->first();
        
        $totalCount = $result->total_count; // Total number of items
        $totalSum = $result->total_sum;    // Total sum of 'amount' column
        
        $failedCount = $failed->total_count;
        $failedTotalSum = $failed->total_sum;
        
        $reversedCount = $reversed->total_count;
        $reveresedTotalSum = $reversed->total_sum;
        return view('rider.payslip_show', compact('payslip', 'payslipItems', 'totalCount', 'totalSum', 'failedCount', 'failedTotalSum', 'reversedCount'));
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
        
        if(\Auth::user()->can('generate bulk payment rider')){
            $payslip = PaySlipRiderBatchItem::findOrFail($id);
        
            // Return view or JSON response
            return view('rider.payslip_edit', compact('payslip'));
        
        }else{
            return redirect()->back()->with('error', __('Permission denied.'));
        } 
    }
    

    
    public function paymentBatchItemtDelete(PaySlipRiderBatchItem $payslip)
    {
        $payslipBatch = PaySlipRiderBatch::findOrFail($payslip->pay_slip_rider_batch_id);
        
        if ($payslipBatch->status > 0) {
            return redirect()->back()->with('error', __('Not Allow. Batch already approved.'));
        }
    
        if (\Auth::user()->can('generate bulk payment rider')) {
            if ($payslip->created_by == \Auth::user()->creatorId()) {
                $payslip->delete();
    
                return redirect()->route('rider.payslips.show', \Crypt::encrypt($payslipBatch->id))
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
        if(\Auth::user()->can('approve payment rider')){
             try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            $payslip = PaySlipRiderBatch::where('id', $id)->first();
            $payslip->status = 1;
            $payslip->save();
            
            PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $id)
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
        if(\Auth::user()->can('initialise payment rider')){
            try {
                $id = Crypt::decrypt($payslipId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            
            $payslipBatch = PaySlipRiderBatch::find(Crypt::decrypt($payslipId));
            
            // Prepare the payload
            $beneficiaries = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $id)->get();
            $payLoad = [];
            foreach ($beneficiaries as $beneficiary) {
                $metaData = [
                    'riderId' => $beneficiary->rider->id,
                    'paymentBatchItemId' => $beneficiary->id,
                ];
                
                $ref = $this->generateValidReference($payslipBatch->end_date);
                
                $payLoad[] = [
                    'reference' => $ref.'-'.$beneficiary->id,
                    'narration' => $payslipBatch->end_date. '-RIDER-PAYOUT',  
                    'destinationAccountNumber' => $beneficiary->rider->bank_account, 
                    'destinationBankCode' => $beneficiary->rider->bank_code, 
                    'amount' => $beneficiary->amount,
                    'metaData' => $metaData,
                    'currency' => 'NGN'
                ];
            }
    
            
            //Prepare post data
            $REF = 'TPCRW_';
            $batchRef = $this->generateValidReference($REF);
             $postData = [
                'title' => 'Bulk Payment to Riders',
                'batchReference' =>  $batchRef,
                'narration' => 'SEB-Riders-Payout',
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
                    $payslip = PaySlipRiderBatch::where('id', $id)->first();
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
        if (!\Auth::user()->can('authorise payment rider')) {
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
            $payslipBatch = PaySlipRiderBatch::findOrFail($payslipBatchId);
            
            if ($payslipBatch->status == 4 ) {
                return redirect()->back()->with('error', __('Paid already!'));
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
                PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $payslipBatch->id)
                    ->update(['status' => 4]);
                
                $batchItems = PaySlipRiderBatchItem::where('pay_slip_rider_batch_id', $payslipBatch->id)->get();

                foreach ($batchItems as $batchItem) {
                    $rider = Rider::find($batchItem->rider_id);
                    
                    if ($rider) {
                        $rider->balance = $rider->balance - $batchItem->amount;
                        $rider->save();
                    }
                
                    if (!empty($batchItem->trip_ids)) {
                        $tripIds = explode(",", $batchItem->trip_ids);
                
                        foreach ($tripIds as $tripId) {
                            $tripId = trim($tripId); // remove whitespace
                            $trip = RiderTrip::find($tripId);
                
                            if ($trip) {
                                $trip->status = 3;
                                $trip->save();
                            }
                        }
                    }
                }

    
                // Request bulk transfer transactions
                // $transactions = $monnify->getBulkTransferTransactions($responseBody->batchReference);
                // $transactions = $transactions->getData();
                // $transactionData = $transactions->details->responseBody;
    
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
         if(\Auth::user()->can('initialise payment rider')) {
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
    
                $hasSuccessOrFailed = false;
                $batchIds = [];
    
                foreach ($batchItems as $item) {
                    PaySlipRiderBatchItem::where('id', $item['payslipId'])->update([
                        'txn_status' => $item['status'],
                        'reference' => $item['reference'],
                        'txn_description' => $item['transactionDescription'],
                    ]);
    
                    if (in_array($item['status'], ['SUCCESS', 'FAILED'])) {
                        $hasSuccessOrFailed = true;
                    }
    
                    $batchIds[] = $item['payslipId'];
                }
    
                if ($hasSuccessOrFailed && !empty($batchIds)) {
                    // Get unique batch_id(s) from the items
                    $batchIds = array_unique(
                        PaySlipRiderBatchItem::whereIn('id', $batchIds)->pluck('pay_slip_rider_batch_id')->toArray()
                    );
    
                    // Update status = 4 for all involved batch_ids
                    PaySlipRiderBatch::whereIn('id', $batchIds)->update(['status' => 4]);
                    PaySlipRiderBatchItem::whereIn('pay_slip_rider_batch_id', $batchIds)->update(['status' => 4]); 
                }
    
                return;
            }
    
            // Wait before retrying
            sleep($retryDelay);
        }
    
        // If still no response after max retries 
        return false;
    }
    
    // public function updatePaylipTxn($batchRef)
    // {
    //     $monnify = new Monnify();
    //     $maxRetries = 5;
    //     $retryDelay = 5; // Wait 5 seconds before retrying
    
    //     for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
    //         $result = $monnify->getBulkTransferTransactions($batchRef);
    //         $response = $result->getData();
    
    //         if (isset($response->details->responseBody->content)) {
    //             $batchItems = array_map(function ($item) {
    //                 if (preg_match('/-(\d+)$/', $item->reference, $matches)) {
    //                     return [
    //                         'payslipId' => $matches[1],
    //                         'reference' => $item->reference,
    //                         'transactionDescription' => $item->transactionDescription ?? '',
    //                         'status' => $item->status,
    //                     ];
    //                 }
    //                 return null;
    //             }, $response->details->responseBody->content);
    
    //             $batchItems = array_filter($batchItems);
    
    //             foreach ($batchItems as $item) {
    //                 PaySlipRiderBatchItem::where('id', $item['payslipId'])->update([ 
    //                     'txn_status' => $item['status'],
    //                     'reference' => $item['reference'],
    //                     'txn_description' => $item['transactionDescription'],
    //                 ]);
    //             }
    //             return;
    //         }
    
    //         // Wait before retrying
    //         sleep($retryDelay);
    //     }
    
    //     // If still no response after max retries, return an error
    //     // return redirect()->back()->with('error', __('Failed to fetch transaction details after multiple attempts.'));
    //     return false;
    // }
    
    public function resendToken(Request $request)
    {
        if(\Auth::user()->can('resend token rider')){
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
    
        /**
     * Payslip reversal page 
     * Requires OTP to proceed
     */
    public function reversPaylipPage($id)
    {
        if (\Auth::user()->can('reverse fail payment rider')) {
            $payslip = PaySlipRiderBatchItem::find(Crypt::decrypt($id));
    
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
    
                return view('rider.reversalpage', compact('payslip'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to send OTP email: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    public function processReversal(Request $request)
    {
        if (\Auth::user()->can('reverse fail payment rider')) {
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
    
        $payslip = PaySlipRiderBatchItem::find(Crypt::decrypt($request->payslip_id));
    
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
                    Log::info("Processing reversal for Rider payslip ID: {$payslip->id}");
    
                    $tripIds = explode(",", $payslip->trip_ids);
                    foreach ($tripIds as $tripId) {
                        // Query and update Purchase model
                        $trip = RiderTrip::find($tripId);
                        if ($trip) {
                            $trip->status = 1;
                            $trip->save();
                            
                        } else {
                            Log::warning("Trip not found for ID: {$tripId}");
                        }
                    }
    
                    // Update payslip status
                    $payslip->status = 6;
                    $payslip->save();
    
                    // Credit Vendor balance
                    $rider = Rider::find($payslip->rider_id);
                    if ($rider) {
                        $rider->balance += $payslip->amount;
                        $rider->book_balance += $payslip->amount;
                        $rider->save();
                    } else {
                        Log::warning("Rider not found for ID: {$payslip->rider_id}");
                        throw new \Exception("Rider not found.");
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
}
