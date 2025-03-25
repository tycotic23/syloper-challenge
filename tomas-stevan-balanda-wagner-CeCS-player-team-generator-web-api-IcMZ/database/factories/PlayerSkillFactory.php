<?php

namespace Database\Factories;

use App\Enums\PlayerSkill as EnumsPlayerSkill;
use App\Models\PlayerSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerSkillFactory extends Factory
{
    protected $model=PlayerSkill::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'skill' => $this->faker->randomElement(EnumsPlayerSkill::values()),
            'value' => $this->faker->randomNumber(2)
        ];
    }
}
