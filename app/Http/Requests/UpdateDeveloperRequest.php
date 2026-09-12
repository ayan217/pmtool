<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesDeveloperProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeveloperRequest extends FormRequest
{
    use ValidatesDeveloperProfile;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->profileRules($this->route('developer')?->id);
    }
}
