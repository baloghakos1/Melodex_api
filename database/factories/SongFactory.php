<?php

namespace Database\Factories;

use App\Models\Song;
use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SongFactory extends Factory
{
    use RefreshDatabase;
    protected $model = Song::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
	{
		return [
			'name' => $this->faker->unique()->word(),
            'songwriter' => $this->faker->unique()->word(),
            'album_id' => Album::factory()
		];
	}
}
