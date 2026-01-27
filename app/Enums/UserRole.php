<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Staff = 'staff';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Staff => 'Staff',
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
        };
    }

    /**
     * Get the color for the role badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::User => 'gray',
            self::Staff => 'info',
            self::Admin => 'warning',
            self::SuperAdmin => 'danger',
        };
    }

    /**
     * Check if this role can access the admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this, [self::Staff, self::Admin, self::SuperAdmin]);
    }

    /**
     * Check if this role is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Check if this role is admin or higher.
     */
    public function isAdminOrHigher(): bool
    {
        return in_array($this, [self::Admin, self::SuperAdmin]);
    }

    /**
     * Get all roles as options for select fields.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($role) => [
            $role->value => $role->label(),
        ])->toArray();
    }

    /**
     * Get admin-assignable roles (excludes super_admin for safety).
     */
    public static function assignableByAdmin(): array
    {
        return [
            self::User->value => self::User->label(),
            self::Staff->value => self::Staff->label(),
            self::Admin->value => self::Admin->label(),
        ];
    }
}
