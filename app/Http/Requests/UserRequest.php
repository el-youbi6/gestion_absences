<?php

namespace App\Http\Requests;

use App\Services\YearService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $anneeId = YearService::getSessionYearId();
        $user = $this->route('user');

        return [
            'nom' => ['required', 'string', 'max:45'],
            'prenom' => ['required', 'string', 'max:45'],
            'cin' => ['required', 'string', 'max:12', Rule::unique('users', 'cin')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(['surveillant', 'stagiaire', 'formateur'])],
            'password' => ['required', 'string', 'min:8'],
            'groupe_id' => [
                'nullable',
                'required_if:role,stagiaire',
                Rule::exists('groupes', 'id')->where('annee_scolaire_id', $anneeId),
            ],
        ];
    }
}
