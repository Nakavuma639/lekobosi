<?php

namespace App\Http\Requests;

use App\Rules\UgandanPhoneNumber;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Hidden honeypot field that real visitors never fill in.
     */
    public const HONEYPOT = 'website';

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
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['nullable', 'string', new UgandanPhoneNumber],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            self::HONEYPOT => ['prohibited'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter your name.',
            'message.required' => 'Write your message.',
            'message.min' => 'Your message is a little short. Please tell us a bit more (at least 10 characters).',
            self::HONEYPOT.'.prohibited' => 'We could not send this message. Please try again.',
        ];
    }

    /**
     * Tidy free text and normalise the phone number before validation.
     */
    protected function prepareForValidation(): void
    {
        $phone = trim((string) $this->input('phone'));

        $this->merge([
            'name' => $this->cleanText('name'),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => $phone === '' ? null : (PhoneNumber::normalize($phone) ?? $phone),
            'subject' => $this->cleanText('subject') ?: null,
            'message' => $this->cleanText('message'),
        ]);
    }

    /**
     * Trim a free-text field and strip any HTML from it.
     */
    private function cleanText(string $key): string
    {
        return trim(strip_tags((string) $this->input($key)));
    }
}
