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
            'id' => $this->resource->getId(),
            'full_name' => (string)$this->resource,
            'last_name' => $this->resource->getLastName(),
            'first_name' => $this->resource->getFirstName(),
            'middle_name' => $this->resource->getMiddleName(),
            'phone' => (string)$this->resource,
            'email' => (string)$this->resource,
            'birth_date' => $this->resource?->format('Y-m-d'),
            'gender' => $this->resource?->getValue(),
            'address' => $this->resource ? [
                'country' => $this->resource,
                'region' => $this->resource,
                'city' => $this->resource,
                'street' => $this->resource,
                'postal_code' => $this->resource,
                'full' => (string)$this->resource,
            ] : null,
            'is_active' => $this->resource,
            'agree_to_newsletter' => $this->resource,
            'preferred_language' => $this->resource,
            'external_id' => $this->resource,
        ];
    }
}
