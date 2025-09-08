<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'rules', 'points'
    ];
    protected $casts = [
        'rules' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot(['progress', 'unlocked_at', 'meta'])
            ->withTimestamps();
    }
}
