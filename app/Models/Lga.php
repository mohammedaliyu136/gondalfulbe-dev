<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lga extends Model
{
    use HasFactory;

    protected $fillable = ['state_id', 'name'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
