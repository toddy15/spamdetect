<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Toddy15\SpamDetect\Models\Token;

class TokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Token::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'token' => $this->faker->word(),
            'count_ham' => $this->faker->randomNumber(),
            'count_spam' => $this->faker->randomNumber(),
        ];
    }
}
