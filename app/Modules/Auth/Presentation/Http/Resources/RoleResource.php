<?php

namespace App\Modules\Auth\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class RoleResource extends JsonResource
{

    /** @var Role */
    public $resource;
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'is_system' => (bool) $this->resource->is_system,
            'permissions' => $this->resource->permissions->pluck('name'),
        ];
    }
}
