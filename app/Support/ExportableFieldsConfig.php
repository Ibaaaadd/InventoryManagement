<?php

namespace App\Support;

class ExportableFieldsConfig
{
    public static function getFieldsForModel(string $model): array
    {
        return match($model) {
            'role' => self::getRoleFields(),
            'user' => self::getUserFields(),
            default => [],
        };
    }

    public static function getExportableFields(string $model): array
    {
        return array_filter(self::getFieldsForModel($model), fn($field) => $field['exportable']);
    }

    public static function getImportableFields(string $model): array
    {
        return array_filter(self::getFieldsForModel($model), fn($field) => $field['importable']);
    }

    public static function findFieldByAlias(string $model, string $alias): ?string
    {
        $alias = strtolower(trim($alias));
        
        foreach (self::getFieldsForModel($model) as $key => $field) {
            if (!isset($field['aliases'])) {
                continue;
            }
            
            foreach ($field['aliases'] as $fieldAlias) {
                if (strtolower($fieldAlias) === $alias) {
                    return $key;
                }
            }
        }
        
        return null;
    }

    private static function getRoleFields(): array
    {
        return [
            'name' => [
                'key' => 'name',
                'label' => 'Nama Role',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['nama', 'name', 'nama role', 'role name'],
            ],
            'description' => [
                'key' => 'description',
                'label' => 'Deskripsi',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['deskripsi', 'description', 'keterangan', 'desc'],
            ],
            'users_count' => [
                'key' => 'users_count',
                'label' => 'Jumlah User',
                'exportable' => true,
                'importable' => false,
                'aliases' => ['jumlah user', 'users count', 'total user', 'user count'],
            ],
        ];
    }

    private static function getUserFields(): array
    {
        return [
            'name' => [
                'key' => 'name',
                'label' => 'Nama',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['nama', 'name', 'nama lengkap', 'full name'],
            ],
            'email' => [
                'key' => 'email',
                'label' => 'Email',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['email', 'e-mail', 'alamat email'],
            ],
            'role' => [
                'key' => 'role',
                'label' => 'Role',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['role', 'jabatan', 'posisi', 'peran'],
            ],
            'is_active' => [
                'key' => 'is_active',
                'label' => 'Status Aktif',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['status', 'aktif', 'is_active', 'active', 'status aktif'],
            ],
            'created_at' => [
                'key' => 'created_at',
                'label' => 'Tanggal Dibuat',
                'exportable' => true,
                'importable' => false,
                'aliases' => ['tanggal dibuat', 'created at', 'tanggal buat', 'dibuat'],
            ],
        ];
    }
}
