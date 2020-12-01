<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Masseur;

class MasseursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch. (Can't do it on tables with foreign key)
        //\App\Models\Masseur::truncate();

        $faker = \Faker\Factory::create();

        // And now, let's create a few articles in our database:
        for ($i = 0; $i < 3; $i++) {
            Masseur::create([
                'name' => $faker->name,
            ]);
        }
    }
}
