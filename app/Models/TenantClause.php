<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantClause extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'content',
        'type',
        'is_active',
        'print_on_receipt',
        'sort_order',
        'file_path',
        'file_name',
        'file_url',
        'has_file',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'print_on_receipt' => 'boolean',
            'sort_order' => 'integer',
            'has_file' => 'boolean',
        ];
    }
}
