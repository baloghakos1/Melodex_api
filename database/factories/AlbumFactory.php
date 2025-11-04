<?php

namespace Database\Factories;

use App\Models\Album;  // ne felejtse el importálni
use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlbumFactory extends Factory
{

    use RefreshDatabase;
    protected $model = Album::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
	{
		return [
			'name' => $this->faker->unique()->word(),
            'year' => $this->faker->unique()->word(),
            'genre' => $this->faker->unique()->word(),
            'artist_id' => Artist::factory()
		];
	}
}
