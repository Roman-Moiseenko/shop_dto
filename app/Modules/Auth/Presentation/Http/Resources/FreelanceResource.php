<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Infrastructure\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\StaffEntity;
class FreelanceResource extends JsonResource
{
    /** @var FreelanceEntity */
    public $resource;
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->fullName,
            'position' => $this->resource->position,
            'personal_phone' => $this->resource->personalPhone?->getValue(),
            'personal_email' => $this->resource->personalEmail?->value,
            'is_active' => $this->resource->isActive,
            'is_user' => $this->resource->user != null,
        ];
    }
}
