<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Infrastructure\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\StaffEntity;
class FreelanceResource extends JsonResource
{
    /** @var Freelance */
    public $resource;
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->full_name,
            'position' => $this->resource->position,
            'personal_phone' => (string) $this->resource->personal_phone,
            'personal_email' => (string) $this->resource->personal_email,
            'is_active' => is_null($this->resource->termination_date),
            // Можно включить связанного пользователя
            'is_user' => $this->whenLoaded('user', function () {
                !is_null($this->resource->user);
            }),
        ];
    }
}
