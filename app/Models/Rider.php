<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Rider extends Model
{


    protected $hidden = ['password'];

    protected $fillable   = [
        'rider_id',
        'name',
        'email',
        'password',
        'email_verified_at',
        'contact',
        'license_no',
        'vehicle_type',
        'vehicle_registration',
        'bank_name',
        'image',
        'bank_account',
        'account_name',
        'bank_code',
        'avatar',
        'is_active',
        'collection_centre',
        'created_by',
        'balance',
        'book_balance',
        'billing_name',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_phone',
        'billing_zip',
        'billing_address',
        'amount_per_trip',
        'state_id',
        'lga_id',
        'type'
    ];


    public function payslips()
    {
        return $this->hasMany(PaySlipRiderBatchItem::class, 'rider_id', 'id');
    }
    
    public function trips() {
        return $this->hasMany(RiderTrip::class);
    }
    
    public function pendingTrips()
    {
        return $this->hasMany(RiderTrip::class)->where('status', 0);
    }
    
    /**
     * Compute the sum of balances for all venders.
     *
     * @return float
     */
    public static function getTotalBalance()
    {
        return self::sum('book_balance');
    }
    
    /**
     * Get the count of venders with a balance greater than 0.
     *
     * @return int
     */
    public static function countWithPositiveBalance()
    {
        return self::where('book_balance', '>', 0)->count();
    }
      
    /**
     * Get a list of riders with a positive balance.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRidersWithPositiveBalance()
    {
        return self::where('book_balance', '>', 0)->get();
    }
    
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    
    public function lga()
    {
        return $this->belongsTo(Lga::class, 'lga_id');
    }

}
