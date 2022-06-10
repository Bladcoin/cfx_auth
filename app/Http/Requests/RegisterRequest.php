<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "email" => "email|required",
            "password" => "required|min:6|max:20"
        ];
    }

    public function messages() {
        return [
            "email.required" => "Поля email обязательно для заполнения",
            "email.email" => "Некорректный формат email",
            "password.required" => "Пароль обязателен для заполнения",
            "password.min" => "Пароль минимум должен содержать 6 символов",
            "password.max" => "Пароль максимум должен содержать 20 символов"
        ];
    }
}
