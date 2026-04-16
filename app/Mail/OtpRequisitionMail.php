<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpRequisitionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $payslip;
    public $totalAmount;

    public function __construct($otp, $payslip, $totalAmount)
    {
        $this->otp = $otp;
        $this->payslip = $payslip;
        $this->totalAmount = $totalAmount;
    }

    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->view('email.otp-requisition-approve')
                    ->with([
                        'otp' => $this->otp,
                        'payslip' => $this->payslip,
                        'totalAmount' => $this->totalAmount
                    ]);
    }
}