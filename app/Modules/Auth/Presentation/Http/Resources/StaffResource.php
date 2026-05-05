<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Infrastructure\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\StaffEntity;
class StaffResource extends JsonResource
{
    /** @var StaffEntity */
    public $resource;
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->fullName->getValue(),
            'position' => $this->resource->position,
            'department' => $this->resource->department,
            'work_phone' => $this->resource->workPhone?->getValue(),
            'work_email' => $this->resource->workEmail?->value,
            'is_active' => $this->resource->isActive,
            // Можно включить связанного пользователя
            'is_user' => $this->resource->user != null,
        ];
    }
}
