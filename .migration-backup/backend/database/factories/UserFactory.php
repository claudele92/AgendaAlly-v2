<?php

namespace Database\Factories;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birthday' => $this->faker->date(),
            'email_verified_at' => now(),
            'active' => 1,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * A deliberately explicit state for local demo identities only.
     * Callers must supply a reserved .test email and local-only password;
     * ordinary factories keep their existing defaults.
     *
     * @param array<string, mixed> $attributes
     */
    public function developmentAccount(string $email, string $password, array $attributes = []): static
    {
        if (!str_ends_with(strtolower($email), '@agendaally.test') || strlen($password) < 16) {
            throw new \InvalidArgumentException(
                'Development factory accounts require an @agendaally.test email and a 16-character local-only password.'
            );
        }

        return $this->state(array_merge($attributes, [
            'email' => $email,
            'phone' => '+12025550100',
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'active' => 1,
        ]));
    }
}
