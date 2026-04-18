<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class FinancialAuditLog extends Model
{
    protected $table = 'financial_audit_logs';

    protected $fillable = ['model_type', 'model_id', 'action', 'user_id', 'ip_address', 'changes'];

    protected $casts = ['changes' => 'array'];

    public static function record(string $action, Model $model): void
    {
        static::create([
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'action'     => $action,
            'user_id'    => Auth::id(),
            'ip_address' => Request::ip(),
            'changes'    => $action === 'updated' ? $model->getDirty() : null,
        ]);
    }
}
