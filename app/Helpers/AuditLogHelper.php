<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogHelper
{
    /**
     * Mencatat aktivitas ke audit log
     *
     * @param string $action
     * @param string $entityType
     * @param int|null $entityId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $userId
     * @return void
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): void {
        // Jika userId tidak diberikan, ambil dari user yang sedang login
        if ($userId === null && Auth::check()) {
            $userId = Auth::id();
        }

        // Jika masih null, gunakan 0 (system)
        if ($userId === null) {
            $userId = 0;
        }

        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Mencatat aktivitas login
     *
     * @param int $userId
     * @return void
     */
    public static function logLogin(int $userId): void
    {
        self::log('login', 'user', $userId, null, ['login_at' => now()->toDateTimeString()]);
    }

    /**
     * Mencatat aktivitas logout
     *
     * @param int $userId
     * @return void
     */
    public static function logLogout(int $userId): void
    {
        self::log('logout', 'user', $userId, null, ['logout_at' => now()->toDateTimeString()]);
    }

    /**
     * Mencatat aktivitas pembuatan data
     *
     * @param string $entityType
     * @param int $entityId
     * @param array $newValues
     * @return void
     */
    public static function logCreate(string $entityType, int $entityId, array $newValues): void
    {
        self::log('create', $entityType, $entityId, null, $newValues);
    }

    /**
     * Mencatat aktivitas update data
     *
     * @param string $entityType
     * @param int $entityId
     * @param array $oldValues
     * @param array $newValues
     * @return void
     */
    public static function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues): void
    {
        self::log('update', $entityType, $entityId, $oldValues, $newValues);
    }

    /**
     * Mencatat aktivitas delete data
     *
     * @param string $entityType
     * @param int $entityId
     * @param array $oldValues
     * @return void
     */
    public static function logDelete(string $entityType, int $entityId, array $oldValues): void
    {
        self::log('delete', $entityType, $entityId, $oldValues, null);
    }

    /**
     * Mencatat aktivitas upload file
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $filePath
     * @return void
     */
    public static function logUpload(string $entityType, int $entityId, string $filePath): void
    {
        self::log('upload_evidence', $entityType, $entityId, null, [
            'file_path' => $filePath,
            'uploaded_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Mencatat aktivitas verifikasi
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $result
     * @param string|null $notes
     * @return void
     */
    public static function logVerification(string $entityType, int $entityId, string $result, ?string $notes = null): void
    {
        self::log('verify', $entityType, $entityId, null, [
            'result' => $result,
            'notes' => $notes,
            'verified_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Mencatat aktivitas perubahan status
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public static function logStatusChange(string $entityType, int $entityId, string $oldStatus, string $newStatus): void
    {
        self::log('status_change', $entityType, $entityId, 
            ['status' => $oldStatus], 
            ['status' => $newStatus]
        );
    }

    /**
     * Mendapatkan log berdasarkan entity
     *
     * @param string $entityType
     * @param int $entityId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsByEntity(string $entityType, int $entityId, int $limit = 50)
    {
        return AuditLog::with('user')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mendapatkan log berdasarkan user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsByUser(int $userId, int $limit = 50)
    {
        return AuditLog::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mendapatkan log berdasarkan action
     *
     * @param string $action
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsByAction(string $action, int $limit = 50)
    {
        return AuditLog::with('user')
            ->where('action', $action)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}