<?php

namespace Database\Factories;

use App\Enums\ContactStatus;
use App\Enums\ContactSubject;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContactMessage> */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'subject' => fake()->randomElement(ContactSubject::cases()),
            'message' => fake()->paragraph(),
            'privacy_accepted_at' => now(),
            'status' => ContactStatus::New,
        ];
    }
}
