<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'step_id',
        'step_image_path',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }
}
