<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('books')->insert($this->getData());
        $this->getData();
    }

    private function getData()
    {
        
        $faker = Faker::create('ru_RU');

        $data = [];
        for ($i=0; $i<10;$i++){
            $data[] = [
                'name' => $faker->sentence(rand(3, 10)),
                'author' => $faker->sentence(rand(3, 10)),
                'description' => $faker->text(rand(50, 50))
            ];
        }
        return $data;
    }
}
