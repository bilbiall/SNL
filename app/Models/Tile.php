<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tile extends Model
{
    protected $fillable = [
        'board_id', 'number', 'kind', 'icon', 'label', 'target_number',
        'pos_left', 'pos_top', 'pos_left_mobile', 'pos_top_mobile',
    ];

    protected $casts = [
        'pos_left' => 'float', 'pos_top' => 'float',
        'pos_left_mobile' => 'float', 'pos_top_mobile' => 'float',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function isMovement(): bool
    {
        return in_array($this->kind, ['snake_head', 'ladder_bottom'], true) && $this->target_number;
    }
}
