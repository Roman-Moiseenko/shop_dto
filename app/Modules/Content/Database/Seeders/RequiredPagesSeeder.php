<?php

namespace App\Modules\Content\Database\Seeders;

use App\Modules\Content\Infrastructure\Models\Page;
use Illuminate\Database\Seeder;

class RequiredPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Главная',
                'content_type' => 'widget_based',
                'status' => 'published',
                'published_at' => now(),
                'meta' => null,
                'template' => 'default',
            ]
        );
    }
}
