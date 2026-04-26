<?php

namespace App\Modules\Auth\Presentation\Http\Requests;

use App\Modules\Auth\Infrastructure\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Проверка прав доступа должна выполняться на уровне middleware или политик
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $staffId = $this->route('staff'); // ID сотрудника из URL
        $user = $staffId ? Staff::find($staffId)?->user : null;
        $userId = $user?->id;

        return [
            // Поля сотрудника
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
            'avatar_path' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            // Поля связанного пользователя (опционально, могут отсутствовать)
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role_names' => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'last_name.required' => 'Фамилия обязательна',
            'first_name.required' => 'Имя обязательно',
            'position.required' => 'Должность обязательна',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'role_names.*.exists' => 'Указанная роль не существует',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Если пароль не передан, удаляем поле из запроса, чтобы не обновлять его на null
        if ($this->input('password') === null) {
            $this->request->remove('password');
        }
    }
}
