<?php

namespace App\Modules\Shared\Application\Interfaces;

interface SettingRepositoryInterface
{
    public function get(string $module, string $key, mixed $default = null): ?array;
    public function set(string $module, string $key, array $value): void;
    public function delete(string $module, string $key): void;
}
