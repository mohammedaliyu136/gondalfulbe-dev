<?php

namespace Modules\SponsorPortal\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Sponsor extends Authenticatable
{
    use Notifiable;

    protected $table  = 'sponsors';
    protected $guard  = 'sponsor';

    protected $fillable = [
        'sponsor_code', 'organization_name', 'contact_person', 'email',
        'password', 'phone', 'logo_path', 'organization_type',
        'country', 'status', 'created_by_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    const ORG_TYPES = ['NGO', 'Government', 'Corporate', 'Individual'];

    public function projects()       { return $this->hasMany(SponsorProject::class, 'sponsor_id'); }
    public function activeProjects() { return $this->projects()->where('status', 'Active'); }

    public static function generateSponsorCode(): string
    {
        $count = static::count() + 1;
        return 'SP-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
