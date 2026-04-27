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
            'full_name' => (string) $this->resource->getFullName(),
            'last_name' => $this->resource->getFullName()->getLastName(),
            'first_name' => $this->resource->getFullName()->getFirstName(),
            'middle_name' => $this->resource->getFullName()->getMiddleName(),
            'phone' => (string) $this->resource->getPhone(),
            'email' => (string) $this->resource->getEmail(),
            'birth_date' => $this->resource->getBirthDate()?->format('Y-m-d'),
            'gender' => $this->resource->getGender()?->getValue(),
            'address' => $this->resource->getAddress() ? [
                'country' => $this->resource,
                'region' => $this->resource,
                'city' => $this->resource,
                'street' => $this->resource,
                'postal_code' => $this->resource,
                'full' => (string) $this->resource->getAddress(),
            ] : null,
            'is_active' => $this->resource->isActive(),
            'agree_to_newsletter' => $this->resource->getAgreeToNewsletter(),
            'preferred_language' => $this->resource->getPreferredLanguage(),
            'external_id' => $this->resource->getExternalId(),
        ];
    }
}
