<?php

namespace App\Modules\Content\Application\DTOs\SettingSite\Footer;

use App\Modules\Content\Application\DTOs\SettingSite\MenuPositionSaveData;
use Spatie\LaravelData\Attributes\Validation\{ArrayType, Nullable, Required, StringType};
use Spatie\LaravelData\Data;

class FooterSettingsSaveData extends Data
{
    public function __construct(
        #[Required, StringType]
        public readonly string $copyright,
        #[Nullable, StringType]
        public readonly ?string $description = null,
        #[Required, ArrayType]
        /** @var MenuPositionSaveData[] */
        public readonly array $menuPositions = [],
    ) {}
}
