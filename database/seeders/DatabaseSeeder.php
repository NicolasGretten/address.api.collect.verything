<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Address;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Address::factory()->create([
            'id'=> 'address-00000000-0000-0000-000000000000',
            'title' => fake()->name(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => null,
            'zip_code' => fake()->postcode(), // password
            'city' => fake()->city(),
            'country' => fake()->country(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ]);
    }
}
