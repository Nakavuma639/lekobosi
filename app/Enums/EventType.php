<?php

namespace App\Enums;

/**
 * Event types offered on the client's booking form. Labels are the client's
 * own wording and are what customers see.
 */
enum EventType: string
{
    case Wedding = 'wedding';
    case IntroductionCeremony = 'introduction_ceremony';
    case BirthdayParty = 'birthday_party';
    case CorporateEvent = 'corporate_event';
    case Conference = 'conference';
    case Other = 'other';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Wedding => 'Wedding',
            self::IntroductionCeremony => 'Introduction Ceremony',
            self::BirthdayParty => 'Birthday Party',
            self::CorporateEvent => 'Corporate Event',
            self::Conference => 'Conference',
            self::Other => 'Other',
        };
    }
}
