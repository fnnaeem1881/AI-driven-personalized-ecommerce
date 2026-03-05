<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Extended Permission model that adds a group_name column.
 *
 * @property string      $name        Full permission name, e.g. "view products"
 * @property string      $guard_name  Guard, e.g. "web"
 * @property string|null $group_name  Logical group, e.g. "products"
 */
class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Scope: filter by group */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group_name', $group);
    }

    /** All distinct groups sorted alphabetically */
    public static function allGroups(): array
    {
        return static::select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();
    }
}
