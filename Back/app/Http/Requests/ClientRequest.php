<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
            "nomComplete"=>"required | string",
            "telephone"=>"required |regex:/^(7[76508]{1})(\\d{7})$/ |unique:clients",
        ];
    }

    public function messages()
    {
        return [
            "nomComplete.required" => "Veuillez saisir le nomComplet",
            "telephone.required" => "Le telephone est obligatoire",
            "telephone.unique" => "Ce numero est deja pris",
        ];
    }
}
