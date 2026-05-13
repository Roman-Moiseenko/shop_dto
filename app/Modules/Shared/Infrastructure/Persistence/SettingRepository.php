<?php

namespace App\Modules\Shared\Infrastructure\Persistence;

use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Infrastructure\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public function get(string $module, string $key, mixed $default = null): ?array
    {
        $setting = Setting::where('module', $module)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function set(string $module, string $key, array $value): void
    {
        Setting::updateOrCreate(
            ['module' => $module, 'key' => $key],
            ['value' => $value]
        );
    }

    public function delete(string $module, string $key): void
    {
        Setting::where('module', $module)->where('key', $key)->delete();
    }
}
