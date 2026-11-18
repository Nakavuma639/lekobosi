<?php

use App\Actions\Bookings\SubmitBooking;
use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Enums\NotificationStatus;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\BookingApproved;
use App\Notifications\BookingReceived;
use App\Notifications\BookingRejected;
use App\Notifications\NewBookingSubmitted;
use App\Notifications\NewContactMessage;
use App\Support\WhatsAppLink;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;

beforeEach(function () {
    $this->travelTo('2026-11-18 10:00:00');
    config()->set('lekobosi.admin.notify_email', 'owner@lekobosi.test');
});

/**
 * @return array<string, string>
 */
function notificationBookingForm(array $overrides = []): array
{
    return array_merge([
        'fullname' => 'Amina Nakato',
        'email' => 'amina@example.com',
        'phone' => '0772123456',
        'event_type' => 'wedding',
        'date' => '2026-12-12',
        'location' => 'Kyankwanzi',
        'guests' => '150',
    ], $overrides);
}

/**
 * Make SMTP unreachable, as if the mail server were down.
 */
function breakTheMailServer(): void
{
    config()->set('mail.default', 'smtp');
    config()->set('mail.mailers.smtp.host', '127.0.0.1');
    config()->set('mail.mailers.smtp.port', 1);
    config()->set('mail.mailers.smtp.timeout', 1);
}

describe('who gets told what', function () {
    it('emails the owner and the customer when a booking is submitted', function () {
        Notification::fake();

        $this->post(route('bookings.store'), notificationBookingForm());
        $booking = Booking::query()->sole();

        Notification::assertSentOnDemand(NewBookingSubmitted::class, function (NewBookingSubmitted $notification, array $channels, AnonymousNotifiable $notifiable) use ($booking) {
            return $notifiable->routes['mail'] === 'owner@lekobosi.test' && $notification->booking->is($booking);
        });
        Notification::assertSentOnDemand(BookingReceived::class, function (BookingReceived $notification, array $channels, AnonymousNotifiable $notifiable) {
            return $notifiable->routes['mail'] === 'amina@example.com';
        });
    });

    it('emails the customer when the owner approves', function () {
        Notification::fake();
        $booking = Booking::factory()->create(['customer_email' => 'grace@example.com']);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved']);

        Notification::assertSentOnDemand(BookingApproved::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'grace@example.com');
        Notification::assertNotSentTo(new AnonymousNotifiable, BookingRejected::class);
    });

    it('emails the customer the reason when the owner rejects', function () {
        Notification::fake();
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'rejected', 'note' => 'Fully booked that weekend.']);

        Notification::assertSentOnDemand(BookingRejected::class, fn (BookingRejected $notification) => $notification->wasCancelled === false);
    });

    it('tells the customer it was a cancellation when an approved booking is rejected', function () {
        Notification::fake();
        $booking = Booking::factory()->approved()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'rejected', 'note' => 'Venue flooded.']);

        Notification::assertSentOnDemand(BookingRejected::class, fn (BookingRejected $notification) => $notification->wasCancelled === true);
    });

    it('emails the owner when a contact message arrives', function () {
        Notification::fake();

        $this->post(route('contact.store'), [
            'name' => 'Ruth Nankya',
            'email' => 'ruth@example.com',
            'message' => 'Do you cater in Hoima district?',
        ]);

        Notification::assertSentOnDemand(NewContactMessage::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'owner@lekobosi.test');
    });

    it('sends nothing when a booking is refused for a full date', function () {
        Notification::fake();
        Booking::factory()->approved()->on('2026-12-12')->create();

        $this->post(route('bookings.store'), notificationBookingForm());

        Notification::assertNothingSent();
    });

    it('only sends after the database transaction commits', function () {
        $booking = Booking::factory()->create();

        expect((new BookingReceived($booking))->afterCommit)->toBeTrue()
            ->and((new NewBookingSubmitted($booking))->afterCommit)->toBeTrue()
            ->and((new BookingApproved($booking))->afterCommit)->toBeTrue()
            ->and((new BookingRejected($booking))->afterCommit)->toBeTrue();
    });
});

describe('delivery log', function () {
    it('records each email as sent once it is delivered', function () {
        $this->post(route('bookings.store'), notificationBookingForm());
        $booking = Booking::query()->sole();

        $logs = NotificationLog::query()->where('booking_id', $booking->id)->get();

        expect($logs)->toHaveCount(2)
            ->and($logs->pluck('status')->unique()->all())->toBe([NotificationStatus::Sent])
            ->and($logs->pluck('sent_at')->filter())->toHaveCount(2)
            ->and($logs->pluck('event')->sort()->values()->all())->toBe([NotificationLog::EVENT_BOOKING_RECEIVED, NotificationLog::EVENT_BOOKING_SUBMITTED]);
    });

    it('skips and logs the customer email when a booking has no email address', function () {
        $booking = app(SubmitBooking::class)->handle([
            'customer_name' => 'Walk-in Customer',
            'customer_email' => null,
            'customer_phone' => '+256772123456',
            'whatsapp_number' => '+256772123456',
            'event_type' => EventType::Wedding,
            'event_date' => CarbonImmutable::parse('2026-12-12'),
            'event_location' => 'Kyankwanzi',
            'guest_count' => 100,
            'extra_requirements' => null,
        ]);

        $customerLog = NotificationLog::query()->where('booking_id', $booking->id)->where('recipient_type', 'customer')->sole();

        expect($customerLog->status)->toBe(NotificationStatus::Skipped)
            ->and($customerLog->error)->toBe('No email address for this customer.');
    });

    it('skips and logs the owner alert when no admin email is configured', function () {
        config()->set('lekobosi.admin.notify_email', null);

        $this->post(route('bookings.store'), notificationBookingForm());

        $adminLog = NotificationLog::query()->where('recipient_type', 'admin')->sole();

        expect($adminLog->status)->toBe(NotificationStatus::Skipped)
            ->and($adminLog->error)->toBe('ADMIN_NOTIFY_EMAIL is not set.');
    });

    it('shows the delivery log on the booking page', function () {
        $this->post(route('bookings.store'), notificationBookingForm());
        $booking = Booking::query()->sole();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.show', $booking))
            ->assertSee('New booking alert to the owner')
            ->assertSee('Booking received email to the customer')
            ->assertSee('Sent');
    });
});

describe('when the mail server is down', function () {
    it('still stores the booking and shows the reference', function () {
        breakTheMailServer();

        $response = $this->post(route('bookings.store'), notificationBookingForm());

        $booking = Booking::query()->sole();
        $response->assertRedirect(route('bookings.confirmation'));
        $this->followRedirects($response)->assertOk()->assertSee($booking->reference);

        expect(NotificationLog::query()->where('booking_id', $booking->id)->pluck('status')->unique()->all())
            ->toBe([NotificationStatus::Failed]);
    });

    it('still saves the owner\'s decision', function () {
        $booking = Booking::factory()->create();
        breakTheMailServer();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.bookings.status.update', $booking), ['status' => 'approved'])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasNoErrors();

        expect($booking->refresh()->status)->toBe(BookingStatus::Approved)
            ->and(NotificationLog::query()->where('booking_id', $booking->id)->sole()->status)->toBe(NotificationStatus::Failed);
    });

    it('still saves contact messages', function () {
        breakTheMailServer();

        $this->post(route('contact.store'), [
            'name' => 'Ruth Nankya',
            'email' => 'ruth@example.com',
            'message' => 'Do you cater in Hoima district?',
        ])->assertRedirect(route('contact.create'));

        expect(ContactMessage::query()->count())->toBe(1)
            ->and(NotificationLog::query()->sole()->status)->toBe(NotificationStatus::Failed);
    });

    it('keeps email addresses out of the application log', function () {
        Log::spy();
        breakTheMailServer();

        $this->post(route('bookings.store'), notificationBookingForm());

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message, array $context) => ! str_contains(json_encode($context), '@'))
            ->atLeast()->once();
    });
});

it('keeps addresses out of the log when a queued email fails in the worker', function () {
    Log::spy();

    report(new TransportException('Failed to authenticate on SMTP server with username "owner@example.com".'));

    Log::shouldNotHaveReceived('error');
    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'Mail transport failed.'
            && ! str_contains(json_encode($context), '@'))
        ->once();
});

describe('email content', function () {
    it('gives the customer their reference and a tracking link', function () {
        $booking = Booking::factory()->on('2026-12-12')->create(['customer_name' => 'Amina Nakato', 'event_type' => 'wedding']);

        $mail = (new BookingReceived($booking))->toMail(new AnonymousNotifiable);

        expect($mail->subject)->toContain($booking->reference)
            ->and(implode(' ', $mail->introLines))->toContain($booking->reference)
            ->and($mail->actionUrl)->toBe(route('bookings.track', ['reference' => $booking->reference]));
    });

    it('includes the quote in the approval email when one was agreed', function () {
        $booking = Booking::factory()->approved()->create(['quoted_amount' => 4_500_000]);

        $mail = (new BookingApproved($booking))->toMail(new AnonymousNotifiable);

        expect(implode(' ', $mail->introLines))->toContain('UGX 4,500,000');
    });

    it('includes the reason in the rejection email', function () {
        $booking = Booking::factory()->rejected()->create(['decision_note' => 'We are away that weekend.']);

        $mail = (new BookingRejected($booking))->toMail(new AnonymousNotifiable);

        expect(implode(' ', $mail->introLines))->toContain('We are away that weekend.');
    });

    it('links the owner straight to the booking to review', function () {
        $booking = Booking::factory()->create();

        $mail = (new NewBookingSubmitted($booking))->toMail(new AnonymousNotifiable);

        expect($mail->actionUrl)->toBe(route('admin.bookings.show', $booking));
    });
});

describe('WhatsApp link', function () {
    it('opens a chat with the customer and the message for the current status', function () {
        $booking = Booking::factory()->approved()->on('2026-12-12')->create([
            'customer_name' => 'Amina Nakato',
            'whatsapp_number' => '+256752000111',
            'event_type' => 'wedding',
        ]);

        $link = WhatsAppLink::forBooking($booking);

        expect($link)->toStartWith('https://wa.me/256752000111?text=')
            ->and(rawurldecode(substr($link, strpos($link, '=') + 1)))
            ->toContain('Hello Amina Nakato, good news!')
            ->toContain($booking->reference)
            ->toContain('Saturday, 12 December 2026');
    });

    it('includes the reason for a rejected booking', function () {
        $booking = Booking::factory()->rejected()->create(['decision_note' => 'Fully booked that weekend.']);

        expect(WhatsAppLink::messageFor($booking))->toContain('Reason: Fully booked that weekend.');
    });

    it('is offered on the admin booking page', function () {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.bookings.show', $booking))
            ->assertSee('Send on WhatsApp')
            ->assertSee(WhatsAppLink::forBooking($booking), escape: true);
    });
});
