<?php

namespace App\Modules\Auth\Presentation\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Domain\Entities\ClientEntity;
class ClientResource extends JsonResource
{
    /** @var ClientEntity */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'full_name' => (string)$this->resource->fullName,
            'phone' => (string)$this->resource->phone,
            'email' => (string)$this->resource->email,

            'gender' => $this->resource?->gender->getValue(),
            'address' => $this->resource->address ? [
                'country' => $this->resource->address->country,
                'region' => $this->resource->address->region,
                'city' => $this->resource->address->city,
                'street' => $this->resource->address->street,
            ] : null,
            'is_active' => $this->resource->isActive,
            'is_consent' => $this->resource->dataConsent->active,
        ];
    }
}
