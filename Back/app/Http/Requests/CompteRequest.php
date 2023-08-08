<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "id" => "required |numeric",
            "fournisseur" => "required |string",
        ];
    }
    public function messages()
    {
        return [
            "id.required" => "Veuillez saisir le numero",
            "fournisseur.required" => "Le type de fournisseur est obligatoire",
        ];
    }
}
