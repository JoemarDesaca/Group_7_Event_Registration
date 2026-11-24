<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isOrganizer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'email', 'max:255'],
            'tickets' => ['nullable', 'array'],
            'tickets.*' => ['required', 'integer', 'min:0', 'max:10'],
            'add_ons' => ['nullable', 'array'],
            'add_ons.*' => ['required', 'integer', 'min:0', 'max:10'],
            'discount_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
