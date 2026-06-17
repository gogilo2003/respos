<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillSplitItem extends Model
{
    protected $table = 'bill_split_items';

    protected $fillable = [
        'split_id',
        'bill_item_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function split()
    {
        return $this->belongsTo(BillSplit::class, 'split_id');
    }

    public function billItem()
    {
        return $this->belongsTo(BillItem::class, 'bill_item_id');
    }
}
