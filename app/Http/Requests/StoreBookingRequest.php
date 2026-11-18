<?php

namespace App\Http\Requests;

use App\Enums\EventType;
use App\Rules\UgandanPhoneNumber;
use App\Services\BookingAvailability;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the public booking form. Field names follow the client's frontend
 * (fullname, whatsapp, date, location, guests, details); bookingDetails() maps
 * them to the booking's column names.
 */
class StoreBookingRequest extends FormRequest
{
    /**
     * Name of the hidden honeypot field that real visitors never fill in.
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
        $availability = app(BookingAvailability::class);

        return [
            'fullname' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'phone' => ['required', 'string', new UgandanPhoneNumber],
            'whatsapp' => ['nullable', 'string', new UgandanPhoneNumber],
            'event_type' => ['required', Rule::enum(EventType::class)],
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$availability->earliestBookableDate()->toDateString(),
                'before_or_equal:'.$availability->latestBookableDate()->toDateString(),
            ],
            'location' => ['required', 'string', 'min:3', 'max:255'],
            'guests' => ['required', 'integer', 'min:1', 'max:'.config('lekobosi.bookings.max_guests')],
            'details' => ['nullable', 'string', 'max:2000'],
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
        $minLeadDays = (int) config('lekobosi.bookings.min_lead_days');

        return [
            'fullname.required' => 'Enter your full name.',
            'event_type.required' => 'Choose the type of event.',
            'event_type.enum' => 'Choose one of the event types in the list.',
            'date.required' => 'Choose the date of your event.',
            'date.after_or_equal' => $minLeadDays === 1
                ? 'Choose a date from tomorrow onwards.'
                : "Choose a date at least {$minLeadDays} days from today.",
            'date.before_or_equal' => 'We take bookings up to two years ahead. Choose an earlier date.',
            'location.required' => 'Tell us where the event will take place.',
            'guests.required' => 'Enter the expected number of guests.',
            'guests.max' => 'For more than :max guests, please call us to plan the event together.',
            self::HONEYPOT.'.prohibited' => 'We could not accept this request. Please try again.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fullname' => 'full name',
            'whatsapp' => 'WhatsApp number',
            'date' => 'event date',
            'location' => 'event location',
            'guests' => 'number of guests',
            'details' => 'additional requirements',
        ];
    }

    /**
     * Tidy free text and normalise phone numbers before validation.
     */
    protected function prepareForValidation(): void
    {
        $phone = PhoneNumber::normalize($this->input('phone')) ?? $this->input('phone');
        $whatsapp = trim((string) $this->input('whatsapp'));

        $this->merge([
            'fullname' => $this->cleanText('fullname'),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => $phone,
            'whatsapp' => $whatsapp === '' ? null : (PhoneNumber::normalize($whatsapp) ?? $whatsapp),
            'location' => $this->cleanText('location'),
            'details' => $this->cleanText('details') ?: null,
        ]);
    }

    /**
     * The validated booking, keyed by the booking's own column names.
     *
     * @return array{
     *     customer_name: string,
     *     customer_email: ?string,
     *     customer_phone: string,
     *     whatsapp_number: ?string,
     *     event_type: EventType,
     *     event_date: CarbonImmutable,
     *     event_location: string,
     *     guest_count: int,
     *     extra_requirements: ?string,
     * }
     */
    public function bookingDetails(): array
    {
        $validated = $this->validated();

        return [
            'customer_name' => $validated['fullname'],
            'customer_email' => $validated['email'],
            'customer_phone' => $validated['phone'],
            'whatsapp_number' => $validated['whatsapp'] ?? $validated['phone'],
            'event_type' => EventType::from($validated['event_type']),
            'event_date' => CarbonImmutable::createFromFormat('Y-m-d', $validated['date'])->startOfDay(),
            'event_location' => $validated['location'],
            'guest_count' => (int) $validated['guests'],
            'extra_requirements' => $validated['details'] ?? null,
        ];
    }

    /**
     * Trim a free-text field and strip any HTML from it.
     */
    private function cleanText(string $key): string
    {
        return trim(strip_tags((string) $this->input($key)));
    }
}
