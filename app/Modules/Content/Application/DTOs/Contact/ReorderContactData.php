<?php

namespace App\Modules\Content\Application\DTOs\Contact;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\{
    Required, IntegerType
};

class ReorderContactData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        #[Required, IntegerType]
        public readonly int $newSort,
    ) {}
}
