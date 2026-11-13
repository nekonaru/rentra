<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewBukti(User $user, Booking $booking): bool
    {
        return $user->rental?->id === $booking->rental_id;
    }

    public function terima(User $user, Booking $booking): bool
    {
        return $user->rental?->id === $booking->rental_id
            && $booking->status === BookingStatus::MenungguVerifikasi;
    }

    public function tolak(User $user, Booking $booking): bool
    {
        return $user->rental?->id === $booking->rental_id
            && $booking->status === BookingStatus::MenungguVerifikasi;
    }

    public function kelola(User $user, Booking $booking): bool
    {
        return $user->rental?->id === $booking->rental_id;
    }
}
