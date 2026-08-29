<?php

declare(strict_types=1);

use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\EventType;

beforeEach(function () {
    AvailabilityRule::factory()->create([
        'weekday' => bookingDate()->format('w'),
        'start_time' => '09:00:00',
        'end_time' => '14:00:00',
    ]);
});

it('reports no conflict when the slot is free', function () {
    $booking = Booking::factory()->create();

    expect($booking->overlapsConfirmedBooking())->toBeFalse();
});

it('reports conflict when another confirmed booking overlaps the same time', function () {
    $eventType = EventType::factory()->duration30()->create();
    $date = bookingDate()->format('Y-m-d');

    Booking::factory()
        ->forEventType($eventType)
        ->onDate($date)
        ->atTime('10:00', 30)
        ->confirmed()
        ->create();

    $pending = Booking::factory()
        ->forEventType($eventType)
        ->onDate($date)
        ->atTime('10:15', 30)
        ->create();

    expect($pending->overlapsConfirmedBooking())->toBeTrue();
});

it('reports no conflict with cancelled or pending bookings', function () {
    $eventType = EventType::factory()->duration30()->create();
    $date = bookingDate()->format('Y-m-d');

    Booking::factory()
        ->forEventType($eventType)
        ->onDate($date)
        ->atTime('10:00', 30)
        ->cancelled()
        ->create();

    $anotherPending = Booking::factory()
        ->forEventType($eventType)
        ->onDate($date)
        ->atTime('10:15', 30)
        ->create();

    expect($anotherPending->overlapsConfirmedBooking())->toBeFalse();
});

it('does not consider the booking itself a conflict', function () {
    $booking = Booking::factory()->confirmed()->create();

    expect($booking->overlapsConfirmedBooking())->toBeFalse();
});
