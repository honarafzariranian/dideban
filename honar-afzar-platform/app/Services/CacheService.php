<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CacheService
{
    /**
     * Cache prefix for the application
     */
    protected string $prefix = 'honar_afzar';

    /**
     * Default cache TTL (Time To Live) in minutes
     */
    protected int $defaultTTL = 60;

    /**
     * Get a cached value or compute and cache it
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $fullKey = $this->getFullKey($key);
        $ttl = $ttl ?? $this->defaultTTL;

        return Cache::remember($fullKey, now()->addMinutes($ttl), $callback);
    }

    /**
     * Get a cached value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($this->getFullKey($key), $default);
    }

    /**
     * Set a cached value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $fullKey = $this->getFullKey($key);
        $ttl = $ttl ?? $this->defaultTTL;

        return Cache::put($fullKey, $value, now()->addMinutes($ttl));
    }

    /**
     * Delete a cached value
     */
    public function forget(string $key): bool
    {
        return Cache::forget($this->getFullKey($key));
    }

    /**
     * Delete all cached values for a prefix
     */
    public function flush(string $prefix = ''): void
    {
        $fullPrefix = $this->getFullKey($prefix);
        
        // Note: This is a simplified flush. In production, use a proper cache driver
        // that supports tag-based flushing or pattern-based deletion
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags([$fullPrefix])->flush();
        }
    }

    /**
     * Cache organization-specific data
     */
    public function rememberForOrganization(int $organizationId, string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->remember(
            "org:{$organizationId}:{$key}",
            $callback,
            $ttl
        );
    }

    /**
     * Cache user-specific data
     */
    public function rememberForUser(int $userId, string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->remember(
            "user:{$userId}:{$key}",
            $callback,
            $ttl
        );
    }

    /**
     * Build full cache key
     */
    protected function getFullKey(string $key): string
    {
        return "{$this->prefix}:{$key}";
    }

    /**
     * Increment a cached counter
     */
    public function increment(string $key, int $value = 1): int
    {
        return Cache::increment($this->getFullKey($key), $value);
    }

    /**
     * Decrement a cached counter
     */
    public function decrement(string $key, int $value = 1): int
    {
        return Cache::decrement($this->getFullKey($key), $value);
    }

    /**
     * Check if a key exists in cache
     */
    public function has(string $key): bool
    {
        return Cache::has($this->getFullKey($key));
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => $this->prefix,
            'default_ttl' => $this->defaultTTL,
        ];
    }
}
