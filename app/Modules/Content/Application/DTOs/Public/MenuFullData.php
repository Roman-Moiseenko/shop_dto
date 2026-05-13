<?php

namespace App\Modules\Content\Application\DTOs\Public;

use App\Modules\Content\Application\DTOs\Menu\MenuItemTreeData;
use Spatie\LaravelData\Data;

class MenuFullData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        /** @var MenuItemTreeData[] */
        public readonly array $items,
    ) {}
}
