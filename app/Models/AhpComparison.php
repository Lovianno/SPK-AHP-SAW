<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhpComparison extends Model
{
    protected $fillable = ['criterion_a_id', 'criterion_b_id', 'value'];

    public function criterionA(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_a_id');
    }

    public function criterionB(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_b_id');
    }

    protected function casts(): array
    {
        return ['value' => 'decimal:12'];
    }
}
