<?php

namespace App\Http\Controllers;

use App\Exports\VenderExport;
use App\Imports\VenderImport;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\ServiceProvider AS Vender;
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

class ServiceProvidersController extends Controller
{



    public function index()
    {
        if(\Auth::user()->can('manage service provider'))
        {
            $venders = Vender::where('created_by', \Auth::user()->creatorId())->get();

            return view('service_providers.index', compact('venders'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create service provider'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
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
            return view('service_providers.create', compact('customFields', 'bankList'));        
            
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


   public function store(Request $request)
{
    if (\Auth::user()->can('create service provider')) {
        $rules = [
            'name' => 'required',
            'contact' => 'required',
            'bank_name' => 'required',
            'bank_code' => 'required',
            'bank_account' => 'required',
            'account_name' => 'required',
            'email' => [
                //'required',
                Rule::unique('service_providers')->where(function ($query) {
                    return $query->where('created_by', \Auth::user()->id);
                }),
            ],
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('service-provider.index')->with('error', $messages->first());
        }

        $objVendor = \Auth::user();
        $creator = User::find($objVendor->creatorId());
        $total_vendor = $objVendor->countVenders();
        $plan = Plan::find($creator->plan);
        $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();

        if ($total_vendor < $plan->max_venders || $plan->max_venders == -1) {
            $vender = new Vender();
            $vender->vender_id = $this->venderNumber();
            $vender->name = $request->name;
            $vender->contact = $request->contact;
            $vender->email = $request->email;
            $vender->bank_name = $request->bank_name;
            $vender->bank_code = $request->bank_code;
            $vender->bank_account = $request->bank_account;
            $vender->account_name = $request->account_name;
            $vender->tax_number = $request->tax_number;
            $vender->billing_name = $request->billing_name;
            $vender->billing_country = $request->billing_country;
            $vender->billing_state = $request->billing_state;
            $vender->billing_city = $request->billing_city;
            $vender->billing_phone = $request->billing_phone;
            $vender->billing_zip = $request->billing_zip;
            $vender->billing_address = $request->billing_address;
            $vender->lang = !empty($default_language) ? $default_language->value : '';
            $vender->created_by = \Auth::user()->creatorId();



            // Handle Image Upload
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('uploads/service_providers'), $imageName);
                $vender->image = $imageName;            }

            $vender->save();
            CustomField::saveData($vender, $request->customField);
        } else {
            return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
        }


        return redirect()->route('service-provider.index')->with('success', __('Service provider successfully created.'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


    public function show($ids)
    {
        try {
            $id       = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Service Provider Not Found.'));
        }

        $id     = \Crypt::decrypt($ids);
        $vendor = Vender::find($id);

        return view('service_providers.show', compact('vendor'));
    }


    public function edit($id)
    {
        if(\Auth::user()->can('edit service provider'))
        {
            $vender              = Vender::findOrFail($id);
            $warehouses = Warehouse::all(); // Assuming you have a Warehouse model
            $vender->customField = CustomField::getData($vender, 'vendor');
            $selectedBankCode = $vender->bank_code;
            $selectedWarehouseId = Warehouse::where('name', $vender->collection_centre)->value('id');
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
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

            return view('service_providers.edit', compact('vender', 'customFields', 'bankList', 'selectedBankCode', 'warehouses', 'selectedWarehouseId'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Vender $service_provider)
    {
        if(\Auth::user()->can('edit service provider'))
        {

            $rules = [
                'name' => 'required',
                //'contact' => 'required',
                'bank_name' => 'required',
                'bank_code' => 'required',
                'bank_account' => 'required',
                'account_name' => 'required',
                'email' => [
                    //'required',
                    Rule::unique('service_providers')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id);
                    }),
                ],
            ];


            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('service-provider.index')->with('error', $messages->first());
            }

            $service_provider->name             = $request->name;
            $service_provider->contact          = $request->contact;
            $service_provider->tax_number      = $request->tax_number;
            $service_provider->bank_name = $request->bank_name;
            $service_provider->bank_code = $request->bank_code;
            $service_provider->bank_account = $request->bank_account;
            $service_provider->account_name = $request->account_name;
            $service_provider->created_by       = \Auth::user()->creatorId();
            $service_provider->billing_name     = $request->billing_name;
            $service_provider->billing_country  = $request->billing_country;
            $service_provider->billing_state    = $request->billing_state;
            $service_provider->billing_city     = $request->billing_city;
            $service_provider->billing_phone    = $request->billing_phone;
            $service_provider->billing_zip      = $request->billing_zip;
            $service_provider->billing_address  = $request->billing_address;
            $service_provider->shipping_name    = $request->shipping_name;
            $service_provider->shipping_country = $request->shipping_country;
            $service_provider->shipping_state   = $request->shipping_state;
            $service_provider->shipping_city    = $request->shipping_city;
            $service_provider->shipping_phone   = $request->shipping_phone;
            $service_provider->shipping_zip     = $request->shipping_zip;
            $service_provider->shipping_address = $request->shipping_address;
            
            // Handle Image Upload (if exists)
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($service_provider->image && file_exists(public_path('uploads/service_providers/' . $vender->image))) {
                    unlink(public_path('uploads/service_providers/' . $service_provider->image));
                }
        
                // Save new image
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('uploads/service_providers'), $imageName);
                $service_provider->image = $imageName;
            }
        
            $service_provider->save();
            //CustomField::saveData($vender, $request->customField);

            return redirect()->route('service-provider.index')->with('success', __('Service provider successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Vender $service_provider)
    {
        if(\Auth::user()->can('delete service provider'))
        {
            if($service_provider->created_by == \Auth::user()->creatorId())
            {
                $service_provider->is_active = 0;
                $service_provider->save();

                return redirect()->route('service-provider.index')->with('success', __('Service provider successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function venderNumber()
    {
        $latest = Vender::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->vender_id + 1;
    }


    public function payment(Request $request)
    {

        if(\Auth::user()->can('manage service provider payment'))
        {
            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('user_type', 'Vender')->where('type', 'Payment');
            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $payments = $query->get();

            return view('service_providers.payment', compact('payments', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {

        if(\Auth::user()->can('manage vender transaction'))
        {

            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Vender');

            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $transactions = $query->get();

            return view('vender.transaction', compact('transactions', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

   
 
}
