<?php

namespace App\Support;

class ExportableFieldsConfig
{
    public static function getFieldsForModel(string $model): array
    {
        return match($model) {
            'role' => self::getRoleFields(),
            'user' => self::getUserFields(),
            'category' => self::getCategoryFields(),
            'item' => self::getItemFields(),
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

    private static function getCategoryFields(): array
    {
        return [
            'name' => [
                'key' => 'name',
                'label' => 'Nama Kategori',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['nama', 'name', 'nama kategori', 'category name'],
            ],
            'code' => [
                'key' => 'code',
                'label' => 'Kode Kategori',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['kode', 'code', 'kode kategori', 'category code'],
            ],
            'items_count' => [
                'key' => 'items_count',
                'label' => 'Jumlah Item',
                'exportable' => true,
                'importable' => false,
                'aliases' => ['jumlah item', 'items count', 'total item'],
            ],
            'created_at' => [
                'key' => 'created_at',
                'label' => 'Tanggal Dibuat',
                'exportable' => true,
                'importable' => false,
            ],
        ];
    }

    private static function getItemFields(): array
    {
        return [
            'sku' => [
                'key' => 'sku',
                'label' => 'SKU',
                'exportable' => true,
                'importable' => false,
            ],
            'name' => [
                'key' => 'name',
                'label' => 'Nama Item',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['nama', 'name', 'nama item', 'nama barang'],
            ],
            'category' => [
                'key' => 'category',
                'label' => 'Kategori',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['kategori', 'category'],
            ],
            'description' => [
                'key' => 'description',
                'label' => 'Deskripsi',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['deskripsi', 'description', 'keterangan'],
            ],
            'price' => [
                'key' => 'price',
                'label' => 'Harga',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['harga', 'price'],
            ],
            'stock_quantity' => [
                'key' => 'stock_quantity',
                'label' => 'Stok',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['stok', 'stock', 'jumlah stok', 'quantity'],
            ],
            'metadata' => [
                'key' => 'metadata',
                'label' => 'Metadata',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['metadata', 'atribut', 'spesifikasi'],
            ],
            'is_active' => [
                'key' => 'is_active',
                'label' => 'Status Aktif',
                'exportable' => true,
                'importable' => true,
                'aliases' => ['status', 'aktif', 'is_active', 'active'],
            ],
            'created_at' => [
                'key' => 'created_at',
                'label' => 'Tanggal Dibuat',
                'exportable' => true,
                'importable' => false,
            ],
        ];
    }
}
