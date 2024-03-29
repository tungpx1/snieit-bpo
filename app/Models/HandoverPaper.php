<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandoverPaper extends Model
{
    use HasFactory;
    protected $fillable = [
        'link',
        'sender_id',
        'receiver_id',
        'asset_tag',
        'number_of_report',
        'is_verify',
        'type'
    ];

    public function sender() {
        return $this->belongsTo('App\Models\User', 'sender_id');
    }

    public function receiver() {
        return $this->belongsTo('App\Models\User', 'receiver_id');
    }
    public function checkedout_asset_tag() {
        return $this->belongsTo('App\Models\Asset', 'asset_tag');
    }
}

