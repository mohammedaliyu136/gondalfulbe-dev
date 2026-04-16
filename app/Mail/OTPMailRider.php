<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $payslip;

    public function __construct($otp, $payslip)
    {
        $this->otp = $otp;
        $this->payslip = $payslip;
    }

    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->view('email.otp')
                    ->with([
                        'otp' => $this->otp,
                        'payslip' => $this->payslip
                    ]);
    }
}