<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Allowed MIME types
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv',
    ];

    /**
     * Maximum file size (10MB)
     */
    protected int $maxFileSize = 10485760;

    /**
     * Upload a file
     */
    public function upload(UploadedFile $file, array $data = []): File
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique name
        $name = $data['name'] ?? $file->getClientOriginalName();
        $path = $file->store(
            $data['folder'] ?? 'uploads',
            $data['disk'] ?? 'public'
        );

        return File::create([
            'uuid' => Str::uuid(),
            'organization_id' => $data['organization_id'] ?? auth()->user()->organization_id,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'name' => $name,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => $data['disk'] ?? 'public',
            'collection' => $data['collection'] ?? 'default',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, array $data = []): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $uploadedFiles[] = $this->upload($file, $data);
        }

        return $uploadedFiles;
    }

    /**
     * Delete a file
     */
    public function delete(File $file): bool
    {
        // Delete from storage
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        // Delete from database
        return $file->delete();
    }

    /**
     * Get file URL
     */
    public function getUrl(File $file): string
    {
        return Storage::disk($file->disk)->url($file->path);
    }

    /**
     * Get temporary URL (for private files)
     */
    public function getTemporaryUrl(File $file, int $expiration = 3600): string
    {
        return Storage::disk($file->disk)->temporaryUrl(
            $file->path,
            now()->addSeconds($expiration)
        );
    }

    /**
     * Get files by collection
     */
    public function getByCollection(int $organizationId, string $collection)
    {
        return File::where('organization_id', $organizationId)
                  ->where('collection', $collection)
                  ->where('status', 'active')
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Get files by user
     */
    public function getByUser(int $userId, string $collection = null)
    {
        $query = File::where('user_id', $userId)
                    ->where('status', 'active');

        if ($collection) {
            $query->where('collection', $collection);
        }

        return $query->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Validate file
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                'نوع فایل مجاز نیست: ' . $file->getMimeType()
            );
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException(
                'حجم فایل بیش از حد مجاز است'
            );
        }
    }

    /**
     * Get file statistics
     */
    public function getStats(int $organizationId): array
    {
        $files = File::where('organization_id', $organizationId)
                    ->where('status', 'active')
                    ->get();

        return [
            'total_files' => $files->count(),
            'total_size' => $files->sum('size'),
            'by_type' => $files->groupBy('mime_type')->map(fn($group) => $group->count()),
            'by_collection' => $files->groupBy('collection')->map(fn($group) => $group->count()),
        ];
    }
}
