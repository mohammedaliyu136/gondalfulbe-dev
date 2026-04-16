<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Loan;
use App\Monnify\Monnify;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\Resignation;
use App\Models\PaySlip;
use App\Models\PaySlipHrBatch;
use App\Models\SaturationDeduction;
use App\Models\Utility;
use App\Models\Termination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Mail\OTPSalaryMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaySlipController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage pay slip') || \Auth::user()->type != 'client' || \Auth::user()->type != 'company')
        {
            $employees = Employee::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->first();

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
            $payslip_batches = PaySlipHrBatch::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->get();
            return view('payslip.index', compact('employees', 'month', 'year', 'payslip_batches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'month' => 'required',
                               'year' => 'required',

                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $month = $request->month;
        $year  = $request->year;


        
        $formate_month_year = $year . '-' . $month;
        
        $paymentBatch = PaySlipHrBatch::where('salary_month', $formate_month_year)->first();
        
        if ($paymentBatch && $paymentBatch->status > 0) {
            return redirect()->back()->with('error', __('This payment batch has already been processed.'));
        }
        
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
        $payslip_employee   = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();

        $resignation = Resignation::where('created_by' , \Auth::user()->creatorId())->where('resignation_date', '<=' , date('Y-m-d'))->pluck('employee_id')->toArray();

        $termination = Termination::where('created_by' , \Auth::user()->creatorId())->where('termination_date', '<=' , date('Y-m-d'))->pluck('employee_id')->toArray();

        if($payslip_employee > count($validatePaysilp))
        {
            // $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->whereNotIn('id', $resignation)->whereNotIn('id', $termination)->whereNot('salary', '<=', 0)->get();
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->whereNotIn('employee_id', $validatePaysilp)->whereNotIn('id', $resignation)->whereNotIn('id', $termination)->whereNot('salary', '<=', 0)->get();

            $employeesSalary = Employee::where('created_by', \Auth::user()->creatorId())->where('salary', '<=', 0)->first();

            // if(!empty($employeesSalary))
            // {
            //     return redirect()->route('payslip.index')->with('error', __('Please set employee salary.'));
            // }

            foreach($employees as $employee)
            {
                $chek = PaySlip::where(['employee_id' => $employee->id, 'salary_month' => $formate_month_year])->first();

                if (!$chek && $chek == null) {
                    $payslipEmployee                       = new PaySlip();
                    $payslipEmployee->employee_id          = $employee->id;
                    $payslipEmployee->net_payble           = $employee->get_net_salary();
                    $payslipEmployee->salary_month         = $formate_month_year;
                    $payslipEmployee->status               = 0;
                    $payslipEmployee->basic_salary         = !empty($employee->salary) ? $employee->salary : 0;
                    $payslipEmployee->allowance            = Employee::allowance($employee->id);
                    $payslipEmployee->commission           = Employee::commission($employee->id);
                    $payslipEmployee->loan                 = Employee::loan($employee->id);
                    $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
                    $payslipEmployee->other_payment        = Employee::other_payment($employee->id);
                    $payslipEmployee->overtime             = Employee::overtime($employee->id);
                    $payslipEmployee->created_by           = \Auth::user()->creatorId();
                    $payslipEmployee->save();

                    //For Notification
                    $setting  = Utility::settings(\Auth::user()->creatorId());
                    $payslipNotificationArr = [
                        'year' =>  $formate_month_year,
                    ];
                    //Slack Notification
                    if(isset($setting['payslip_notification']) && $setting['payslip_notification'] ==1)
                    {
                        Utility::send_slack_msg('new_monthly_payslip', $payslipNotificationArr);
                    }

                    //Telegram Notification
                    if(isset($setting['telegram_payslip_notification']) && $setting['telegram_payslip_notification'] ==1)
                    {
                        Utility::send_telegram_msg('new_monthly_payslip', $payslipNotificationArr);
                    }

                    //webhook
                    $module ='New Monthly Payslip';
                    $webhook=  Utility::webhookSetting($module);
                    if($webhook)
                    {
                        $parameter = json_encode($payslipEmployee);
                        $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);

                        if($status == true)
                        {
                            return redirect()->back()->with('success', __('Payslip successfully created.'));
                        }
                        else
                        {
                            return redirect()->back()->with('error', __('Webhook call failed.'));
                        }
                    }
                }

            }

            return redirect()->route('payslip.index')->with('success', __('Payslip successfully created.'));
        }
        else
        {
            return redirect()->route('payslip.index')->with('error', __('Payslip Already created.'));
        }

    }

    public function destroy($id)
    {
        $payslip = PaySlip::find($id);
    
        if (!$payslip) {
            return response()->json([
                'success' => false,
                'message' => __('Payslip not found.')
            ], 404);
        }
    
        // Ensure pay_slip_hr_batch_id exists before querying
        $paymentBatch = $payslip->pay_slip_hr_batch_id 
            ? PaySlipHrBatch::find($payslip->pay_slip_hr_batch_id)
            : null;
    
        if ($paymentBatch && $paymentBatch->status > 0) {
            return response()->json([
                'success' => false,
                'message' => __('This payment batch has already been processed.')
            ], 400);
        }
    
        //if ($payslip->batch && $payslip->batch->status !== 2) {
            $payslip->delete();
            return response()->json([
                'success' => true,
                'message' => __('Payslip deleted successfully.')
            ], 200);
        //}
    
        // return response()->json([
        //     'success' => false,
        //     'message' => __('Payslip cannot be deleted.')
        // ], 400);
    }


    public function showemployee($paySlip)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.show', compact('payslip'));
    }


    public function search_json(Request $request)
    {

        $formate_month_year = $request->datePicker;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->get()->toarray();

        $data=[];
        if (empty($validatePaysilp))
        {
            return response()->json(['data' => $data]);
        } else {
            $paylip_employee = PaySlip::select(
                [
                    'employees.id',
                    'employees.employee_id',
                    'employees.name',
                    'payslip_types.name as payroll_type',
                    'pay_slips.basic_salary',
                    'pay_slips.net_payble',
                    'pay_slips.id as pay_slip_id',
                    'pay_slips.status',
                    'employees.user_id',
                ]
            )->leftjoin(
                'employees',
                function ($join) use ($formate_month_year) {
                    $join->on('employees.id', '=', 'pay_slips.employee_id');
                    $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
                    $join->leftjoin('payslip_types', 'payslip_types.id', '=', 'employees.salary_type');
                }
            )->where('employees.created_by', \Auth::user()->creatorId())->get();


            foreach ($paylip_employee as $employee) {

                if (Auth::user()->type == 'Employee') {
                    if (Auth::user()->id == $employee->user_id) {
                        $tmp   = [];
                        $tmp[] = $employee->id;
                        $tmp[] = $employee->name;
                        $tmp[] = $employee->payroll_type;
                        $tmp[] = $employee->pay_slip_id;
                        $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                        $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                        if ($employee->status == 1) {
                            $tmp[] = 'paid';
                        } else {
                            $tmp[] = 'unpaid';
                        }
                        $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                        $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                        $data[] = $tmp;
                    }
                } else {

                    $tmp   = [];
                    $tmp[] = $employee->id;
                    $tmp[] = \Auth::user()->employeeIdFormat($employee->employee_id);
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'Paid';
                    }elseif($employee->status == 2){
                         $tmp[] = 'Approved';
                    }
                    else {
                        $tmp[] = 'UnPaid';
                    }
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            }

            return $data;
        }
    }

    public function paysalary($id, $date)
    {
        $employeePayslip = PaySlip::where('employee_id', '=', $id)->where('created_by', \Auth::user()->creatorId())->where('salary_month', '=', $date)->first();

        $account = Employee::find($id);
        Utility::bankAccountBalance($account->account, $employeePayslip->net_payble, 'debit');

        if(!empty($employeePayslip))
        {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            return redirect()->route('payslip.index')->with('success', __('Payslip Payment successfully.'));
        }
        else
        {
            return redirect()->route('payslip.index')->with('error', __('Payslip Payment failed.'));
        }

    }

    public function bulk_pay_create($date)
    {
        $Employees       = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->get();
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        return view('payslip.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }


    
    public function bulkpayment(Request $request, $date)
    {
        DB::beginTransaction();
        try {
            $unpaidEmployees = PaySlip::where('salary_month', $date)
                ->where('created_by', \Auth::user()->creatorId())
                ->where('status', '=', 0)
                ->get();
    

            $paymentBatch = PaySlipHrBatch::where('salary_month', $date)->first();
           if (!$paymentBatch) {
                // Create a new batch if it doesn't exist
                $paymentBatch = new PaySlipHrBatch();
                $paymentBatch->batch_id = 'SAL00' . $this->latestBatchId();
                $paymentBatch->salary_month = $date;
                $paymentBatch->batch_type = 'regular';
                $paymentBatch->status = 0;
                $paymentBatch->created_by = 2;
                $paymentBatch->save();
            }
                
            $batchId = $paymentBatch->id;
            $payLoad = [];
    
            foreach ($unpaidEmployees as $employee) {

                $ref = $this->generateValidReference($date);

                $employee->status = 0;
                // $employee->txn_ref = $ref;
                $employee->pay_slip_hr_batch_id = $batchId;
                $employee->save();
            }
    
    
                DB::commit();
                return redirect()->route('payslip.index')->with('success', __('Payslip Bulk Payment successfully Created.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }


    public function employeepayslip()
    {
        $employees = Employee::where(
            [
                'user_id' => \Auth::user()->id,
            ]
        )->first();

        $payslip = PaySlip::where('employee_id', '=', $employees->id)->get();

        return view('payslip.employeepayslip', compact('payslip'));

    }

    public function pdf($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

       // dd($employee);

        $payslipDetail = Utility::employeePayslipDetail($id,$month);


        return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function send($id, $month)
    {
        $setings = Utility::settings();
//        dd($setings);
        if($setings['payslip_sent'] == 1)
        {
            $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
            $employee = Employee::find($payslip->employee_id);

            $payslip->name  = $employee->name;
            $payslip->email = $employee->email;

            $payslipId    = Crypt::encrypt($payslip->id);
            $payslip->url = route('payslip.payslipPdf', $payslipId);
//            dd($payslip->url);

            $payslipArr = [

                'employee_name'=> $employee->name,
                'employee_email' => $employee->email,
                'payslip_name' =>   $payslip->name,
                'payslip_salary_month' => $payslip->salary_month,
                'payslip_url' =>$payslip->url,

            ];
            $resp = Utility::sendEmailTemplate('payslip_sent', [$employee->id => $employee->email], $payslipArr);



            return redirect()->back()->with('success', __('Payslip successfully sent.') .(($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Payslip successfully sent.'));

    }

    public function payslipPdf($id)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip  = PaySlip::where('id', $payslipId)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($payslip->employee_id);

        return view('payslip.payslipPdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function editEmployee($paySlip)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {


        if(isset($request->allowance) && !empty($request->allowance))
        {
            $allowances   = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach($allowances as $k => $allownace)
            {
                $allowanceData         = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if(isset($request->commission) && !empty($request->commission))
        {
            $commissions   = $request->commission;
            $commissionIds = $request->commission_id;
            foreach($commissions as $k => $commission)
            {
                $commissionData         = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if(isset($request->loan) && !empty($request->loan))
        {
            $loans   = $request->loan;
            $loanIds = $request->loan_id;
            foreach($loans as $k => $loan)
            {
                $loanData         = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if(isset($request->saturation_deductions) && !empty($request->saturation_deductions))
        {
            $saturation_deductionss   = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach($saturation_deductionss as $k => $saturation_deductions)
            {

                $saturation_deductionsData         = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if(isset($request->other_payment) && !empty($request->other_payment))
        {
            $other_payments   = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach($other_payments as $k => $other_payment)
            {
                $other_paymentData         = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if(isset($request->rate) && !empty($request->rate))
        {
            $rates   = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach($rates as $k => $rate)
            {
                $overtime        = Overtime::find($rateIds[$k]);
                $overtime->rate  = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }


        $payslipEmployee                       = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance            = Employee::allowance($payslipEmployee->employee_id);
        $payslipEmployee->commission           = Employee::commission($payslipEmployee->employee_id);
        $payslipEmployee->loan                 = Employee::loan($payslipEmployee->employee_id);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment        = Employee::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime             = Employee::overtime($payslipEmployee->employee_id);
        $payslipEmployee->net_payble           = Employee::find($payslipEmployee->employee_id)->get_net_salary();
        $payslipEmployee->save();

        return redirect()->route('payslip.index')->with('success', __('Employee payroll successfully updated.'));
    }

    public function export(Request $request)
    {
        $name = 'payslip_' . date('Y-m-d i:h:s');
        $data = Excel::download(new PayslipExport($request), $name . '.xlsx'); ob_end_clean();
        return $data;
    }
    
    public function latestBatchId()
    {
        $latest = PaySlipHrBatch::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    
    private function generateValidReference($prefix)
    {
        return trim($prefix . str_replace('.', '_', uniqid())); // Replaces dots with underscores
    }
    
    public function showPayslip($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Payslip Not Found.'));
        }
        // $id = Crypt::decrypt($id); 
        $payslip = PaySlipHrBatch::where('id', $id)->first();
        $payslipItems = PaySlip::where('pay_slip_hr_batch_id', '=', $id)->get();
        
        $result = PaySlip::where('pay_slip_hr_batch_id', $id)
        ->selectRaw('COUNT(*) as total_count, SUM(net_payble) as total_sum')
        ->first();
        
        $failed = PaySlip::where('pay_slip_hr_batch_id', $id)
        ->where('txn_status', 'FAILED')
        ->selectRaw('COUNT(*) as total_count, SUM(net_payble) as total_sum')
        ->first();
        
        $reversed = PaySlip::where('pay_slip_hr_batch_id', $id)
        ->where('status', 6)
        ->selectRaw('COUNT(*) as total_count, SUM(net_payble) as total_sum')
        ->first();
        
        $totalCount = $result->total_count; // Total number of items
        $totalSum = $result->total_sum;    // Total sum of 'amount' column
        
        $failedCount = $failed->total_count;
        $failedTotalSum = $failed->total_sum;
        
        $reversedCount = $reversed->total_count;
        $reveresedTotalSum = $reversed->total_sum;
        return view('payslip.showpayslip', compact('payslip', 'payslipItems', 'totalCount', 'totalSum', 'failedCount', 'failedTotalSum', 'reversedCount'));
    }
    
      /**
     * Payslip approval page 
     * Requires OTP to proceed
     */
    public function approvePayment($id)
    {
        if (\Auth::user()->can('approve payment salary')) {
            $payslip = PaySlipHrBatch::find(Crypt::decrypt($id));
    
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
                Mail::to(\Auth::user()->email)->send(new OTPSalaryMail($otp, $payslip));
                
                // Mail::to([\Auth::user()->email, 'farm.manager@seborefarms.ng'])
                //     ->send(new OTPSalaryMail($otp, $payslip));

    
                return view('payslip.approve-payment', compact('payslip'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to send OTP email: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
        public function processApproval(Request $request)
        {
            if (\Auth::user()->can('approve payment salary')) {
                // Retrieve OTP from session
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
        
                $payslip = PaySlipHrBatch::find(Crypt::decrypt($request->payslip_id));
        
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
                    PaySlip::where('pay_slip_hr_batch_id', $payslip->id)
                        ->update(['status' => 2]);
        
                    // Commit the transaction if everything is successful
                    DB::commit();
        
                    return redirect()->back()->with('success', __('Payslip approved successfully.'));
                } catch (\Exception $e) {
                    // Rollback in case of any errors
                    DB::rollBack();
                    return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        
    public function initialisePayment($payslipId)
    {
        if (\Auth::user()->can('initialise payment salary')) {
            DB::beginTransaction();
        try {
            
            $payslipBatch = PaySlipHrBatch::find(Crypt::decrypt($payslipId));
            
            if (!$payslipBatch) {
                return redirect()->back()->with('error', __('Payslip batch not found.'));
            }
            
            $unpaidEmployees = PaySlip::where('pay_slip_hr_batch_id', $payslipBatch->id)
                ->where('status', '=', 2)
                ->get();
            
            if ($unpaidEmployees->isEmpty()) {
                return redirect()->back()->with('error', __('No unpaid employees found.'));
            }
                
            $batchId = $payslipBatch->id;
            $payLoad = [];
    
            foreach ($unpaidEmployees as $employee) {

                $ref = $this->generateValidReference($payslipBatch->salary_month);
                $payLoad[] = [
                    'reference' => $ref.'-'.$employee->id,
                    'narration' => $payslipBatch->salary_month. '-SALARY',
                    'destinationAccountNumber' => $employee->employees->account_number,
                    'destinationBankCode' => $employee->employees->bank_identifier_code,
                    'amount' => $employee->net_payble,
                    'currency' => 'NGN',
                ];
                // $employee->status = 0;
                $employee->txn_ref = $ref;
                $employee->save();
            }
    
            // Prepare post data
            $REF = $payslipBatch->salary_month.'-';
            $batchRef = $this->generateValidReference($REF);
            $postData = [
                'title' => '-salary',
                'batchReference' => $batchRef,
                'narration' => $payslipBatch->salary_month. '-salary',
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
                $paymentBatch = PaySlipHrBatch::find($batchId);
                $paymentBatch->total_fee = $responseBody->totalFee;
                $paymentBatch->batch_reference = $batchRef;
                $paymentBatch->status = 3;
                $paymentBatch->save();
                $this->updatePaylipTxn($batchRef);
                DB::commit();
                
                return redirect()->route('payslip.index')->with('success', __('Payslip Bulk Payment successfully Innitialised.'));
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', __($response->details->responseMessage));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __($e->getMessage()));
        }
            
        } else {
                return redirect()->back()->with('error', __('Permission denied.'));
        }
        
        
    }

    public function authorisePayment(Request $request)
    {
        if (!\Auth::user()->can('authorise payment salary')) {
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
            $payslipBatch = PaySlipHrBatch::findOrFail($payslipBatchId);
            
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
                PaySlip::where('pay_slip_hr_batch_id', $payslipBatch->id)
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
        if(\Auth::user()->can('resend token salary')){
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
                    PaySlip::where('id', $item['payslipId'])->update([
                        'txn_status' => $item['status'],
                        'txn_ref' => $item['reference'],
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

    
    public function revalidatePaylip($batchRef)
    {
         if(\Auth::user()->can('initialise payment salary')) {
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
    
    public function regenerateFailedTransactions($batchId)
    {
        if(\Auth::user()->can('initialise payment salary')) {
            try {
                $batchId = Crypt::decrypt($batchId);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Payslip Not Found.'));
            }
            $parentPayslipBatch = PaySlipHrBatch::findOrFail($batchId);
            $failedTransactions = PaySlip::where('pay_slip_hr_batch_id', $batchId)
            ->where('txn_status', 'FAILED')
            ->get(); // Retrieve the records
        
            // Create a new batch if it doesn't exist
            $paymentBatch = new PaySlipHrBatch();
            $paymentBatch->batch_id = 'SAL00' . $this->latestBatchId();
            $paymentBatch->salary_month = $parentPayslipBatch->salary_month;
            $paymentBatch->batch_type = 'failed_reprocess';
            $paymentBatch->parent_batch_id = $parentPayslipBatch->id;
            $paymentBatch->status = 0;
            $paymentBatch->created_by = 2; //\Auth::id();
            $paymentBatch->save();
            
            // Update failed transactions with the new batch ID
            $failedTransactions->each(function ($transaction) use ($paymentBatch) {
                $transaction->pay_slip_hr_batch_id = $paymentBatch->id;
                $transaction->save();
            });
                
            return redirect()->route('payslip.index')
                ->with('success', __('Failed transactions have been successfully reprocessed.'));

            
        } else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
