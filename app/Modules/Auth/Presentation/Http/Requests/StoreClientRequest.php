<?php

namespace App\Modules\Auth\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'country' => 'required_with:city,street|string|max:255',
            'city' => 'required_with:country,street|string|max:255',
            'street' => 'required_with:country,city|string|max:255',
            'region' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'agree_to_newsletter' => 'boolean',
            'preferred_language' => 'string|size:2|in:ru,en',
            'external_id' => 'nullable|string|max:255',
            // User fields
            'name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_names' => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ];
    }
}
