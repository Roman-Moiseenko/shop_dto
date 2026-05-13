<?php

namespace App\Modules\Content\Application\DTOs\SettingSite\Header;

use App\Modules\Content\Application\DTOs\SettingSite\MenuPositionSaveData;
use Spatie\LaravelData\Attributes\Validation\{ArrayType, BooleanType, Nullable, Required, StringType};
use Spatie\LaravelData\Data;

class HeaderSettingsSaveData extends Data
{
    public function __construct(
        #[Required, StringType] public readonly string $siteName,
        #[Nullable, StringType] public readonly ?string $slogan,
        #[Nullable, StringType] public readonly ?string $logoUuid,
        #[Required, ArrayType]
        /** @var MenuPositionSaveData[] */
        public readonly array $menuPositions,
        #[Required, BooleanType] public readonly bool $searchEnabled = false,
        #[Nullable, StringType] public readonly ?string $searchPlaceholder,
        #[Nullable, StringType] public readonly ?string $searchActionUrl,
    ) {}
}
