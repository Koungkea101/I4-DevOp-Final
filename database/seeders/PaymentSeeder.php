<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create payments for approved/completed bookings
        $bookings = Booking::whereIn('status', ['approved', 'completed'])->get();

        foreach ($bookings as $booking) {
            Payment::factory()->create(['booking_id' => $booking->id]);
        }
    }
}
