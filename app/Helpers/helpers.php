<?php

use App\Helpers\AuditLogHelper;

if (!function_exists('audit_log')) {
    /**
     * Helper function untuk mencatat audit log
     *
     * @param string $action
     * @param string $entityType
     * @param int|null $entityId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return void
     */
    function audit_log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLogHelper::log($action, $entityType, $entityId, $oldValues, $newValues);
    }
}

if (!function_exists('audit_log_create')) {
    function audit_log_create(string $entityType, int $entityId, array $newValues): void
    {
        AuditLogHelper::logCreate($entityType, $entityId, $newValues);
    }
}

if (!function_exists('audit_log_update')) {
    function audit_log_update(string $entityType, int $entityId, array $oldValues, array $newValues): void
    {
        AuditLogHelper::logUpdate($entityType, $entityId, $oldValues, $newValues);
    }
}

if (!function_exists('audit_log_delete')) {
    function audit_log_delete(string $entityType, int $entityId, array $oldValues): void
    {
        AuditLogHelper::logDelete($entityType, $entityId, $oldValues);
    }
}

if (!function_exists('audit_log_upload')) {
    function audit_log_upload(string $entityType, int $entityId, string $filePath): void
    {
        AuditLogHelper::logUpload($entityType, $entityId, $filePath);
    }
}

if (!function_exists('audit_log_verify')) {
    function audit_log_verify(string $entityType, int $entityId, string $result, ?string $notes = null): void
    {
        AuditLogHelper::logVerification($entityType, $entityId, $result, $notes);
    }
}

if (!function_exists('audit_log_status')) {
    function audit_log_status(string $entityType, int $entityId, string $oldStatus, string $newStatus): void
    {
        AuditLogHelper::logStatusChange($entityType, $entityId, $oldStatus, $newStatus);
    }
}

if (!function_exists('apply_sort')) {
    /**
     * Terapkan orderBy dari query string (sort & direction) dengan whitelist kolom.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param array $columns daftar kolom yang boleh di-sortir
     * @param string $default default kolom sortir
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    function apply_sort($query, array $columns, string $default = 'created_at')
    {
        $sort = request('sort', $default);
        $dir  = request('direction', 'desc');
        if (!in_array($sort, $columns, true)) {
            $sort = $default;
        }
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        return $query->orderBy($sort, $dir);
    }
}