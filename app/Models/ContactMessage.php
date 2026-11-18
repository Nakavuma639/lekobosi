<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message sent from the public contact form. Nothing is mass-assignable.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_read' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * The admin who marked the message as read.
     *
     * @return BelongsTo<User, $this>
     */
    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    /**
     * Mark the message as read by the given admin. Already-read messages keep
     * their original reader and time.
     */
    public function markAsReadBy(User $user): void
    {
        if ($this->is_read) {
            return;
        }

        $this->is_read = true;
        $this->read_at = now();
        $this->read_by = $user->id;
        $this->save();
    }

    /**
     * Messages nobody has marked as read yet.
     *
     * @param  Builder<ContactMessage>  $query
     */
    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('is_read', false);
    }
}
