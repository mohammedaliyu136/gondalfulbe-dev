<?php

namespace App\Monnify;

use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Monnify
{
    private $secret_key;
    private $public_key;
    private $is_enabled;
    
    public $mode;
    
    private $base_url;
    private $transaction_url;
    private $auth_url;
    private $banklist_url;
    private $validateaccount_url;
    
    private $source_account;

    public function __construct()
    {
        $payment_setting = Utility::getCompanyPaymentSetting(2);
        
        $this->mode = isset($payment_setting['monnify_mode']) ? $payment_setting['monnify_mode'] : 'sandbox';
        $this->secret_key = isset($payment_setting['monnify_secret_key']) ? $payment_setting['monnify_secret_key'] : '';
        $this->public_key = isset($payment_setting['monnify_public_key']) ? $payment_setting['monnify_public_key'] : '';
        $this->source_account = isset($payment_setting['monnify_source_account']) ? $payment_setting['monnify_source_account'] : '';

        if($this->mode === 'live'){
            $this->base_url = "https://api.monnify.com/api/v2";
            $this->auth_url = "https://api.monnify.com/api/v1/auth/login";
            $this->banklist_url = "https://api.monnify.com/api/v1/banks";
            $this->validateaccount_url = "https://api.monnify.com/api/v1/disbursements/account/validate";
        } else {
            $this->base_url = "https://sandbox.monnify.com/api/v2";
            $this->auth_url = "https://sandbox.monnify.com/api/v1/auth/login";
            $this->banklist_url = "https://sandbox.monnify.com/api/v1/banks";
            $this->validateaccount_url = "https://sandbox.monnify.com/api/v1/disbursements/account/validate";
        }
    }

    private function authenticate()
    {
        try {
            $auth_string = base64_encode("{$this->public_key}:{$this->secret_key}");

            $response = Http::withHeaders([
                'Authorization' => "Basic {$auth_string}",
                'Content-Type' => 'application/json',
            ])->post($this->auth_url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['responseBody']['accessToken'] ?? null;
            }

            Log::error('Monnify Authentication Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Monnify Authentication Exception: ' . $e->getMessage());
            return null;
        }
    }
    
    public function bankList()
    {
        $authToken = $this->authenticate();
    
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->get($this->banklist_url);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to fetch bank list',
                'details' => $response->json()
            ], 500);
        }
    
        return response()->json([
            'message' => 'Bank list fetched successfully',
            'details' => $response->json()
        ]);
    }
    
    public function validateBankAccount($bankCode, $accountNumber)
    {
        $authToken = $this->authenticate();
    
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }

        $queryParams = [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode
        ];
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->get($this->validateaccount_url, $queryParams);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to validate bank account',
                'details' => $response->json()
            ], 500);
        }
    
        return response()->json([
            'message' => 'Bank account validated successfully',
            'details' => $response->json()
        ]);
    }
    
    public function getSingleTransaction($transactionReference)
    {
        $authToken = $this->authenticate();
        
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    
        $url = $this->base_url . '/disbursements/single/summary';
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->get($url, ['reference' => $transactionReference]);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to retrieve transaction details',
                'details' => $response->json(),
            ], 500);
        }
    
        return response()->json([
            'message' => 'Transaction retrieved successfully',
            'details' => $response->json(),
        ]);
    }

    public function bulkPaymentInitialise($postData)
    {
        $authToken = $this->authenticate();
        
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
        
        $postData['sourceAccountNumber'] = $this->source_account;

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->post($this->base_url . '/disbursements/batch', $postData);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Bulk payment initiation failed',
                'details' => $response->json()
            ], 500);
        }

        $responseData = $response->json();
        $batchReference = $responseData['responseBody']['batchReference'] ?? null;
        
        return response()->json([
            'message' => 'Bulk payment initiated successfully',
            'batchReference' => $batchReference,
            'details' => $responseData
        ]);
    }
    
    public function bulkPaymentAuthorize($batchReference, $otp)
    {
        $authToken = $this->authenticate();
    
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    
        $postData = [
            'reference' => $batchReference,
            'authorizationCode' => $otp
        ];
    
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->post($this->base_url . '/disbursements/batch/validate-otp', $postData);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Bulk payment authorization failed',
                'details' => $response->json()
            ], 500);
        }
    
        return response()->json([
            'message' => 'Bulk payment authorized successfully',
            'details' => $response->json()
        ]);
    }
    
    public function getBulkTransferTransactions($batchReference)
    {
        $authToken = $this->authenticate();
        
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    
        $url = $this->base_url . '/disbursements/bulk/' . urlencode($batchReference).'/transactions';
    
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->get($url, ['pageSize' => 500]);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to retrieve bulk transfer transactions',
                'details' => $response->json(),
            ], 500);
        }
    
        return response()->json([
            'message' => 'Bulk transfer transactions retrieved successfully',
            'details' => $response->json(),
        ]);
    }

    public function resendToken($reference)
    {
        $authToken = $this->authenticate();
    
        if (!$authToken) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    
        $postData = [
            'batchReference' => $reference,
        ];
    
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$authToken}",
            'Content-Type' => 'application/json',
        ])->post($this->base_url . '/disbursements/batch/resend-otp', $postData);
    
        if ($response->failed()) {
            return response()->json([
                'message' => 'System unable to resend OTP',
                'details' => $response->json()
            ], 500);
        }
    
        return response()->json([
            'message' => 'OTP resent successfully',
            'details' => $response->json()
        ]);
    }

    private function generateValidReference()
    {
        return 'payment_' . str_replace('.', '_', uniqid()); 
    }
}
?>