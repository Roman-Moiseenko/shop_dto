<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use App\Modules\Auth\Infrastructure\Models\Staff;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\StaffEntity;
class StaffResource extends JsonResource
{
    /** @var Staff */
    public $resource;
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => (string) $this->resource->full_name,
            'position' => $this->resource->position,
            'department' => $this->resource->department,
            'work_phone' => (string) $this->resource->work_phone,
            'work_email' => (string) $this->resource->work_email,
            'is_active' => is_null($this->resource->termination_date),
            // Можно включить связанного пользователя
            'is_user' => $this->whenLoaded('user', function () {
                !is_null($this->resource->user);
            }),
        ];
    }
}
