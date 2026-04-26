<?php

namespace App\Modules\Auth\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreStaffRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'work_phone' => 'nullable|string|max:20',
            'personal_phone' => 'nullable|string|max:20',
            'work_email' => 'nullable|email|max:255',
            'hire_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'telegram_chat_id' => 'nullable|string|max:255',

            'notes' => 'nullable|string',
            // Поля для User
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_names' => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ];
    }
}
