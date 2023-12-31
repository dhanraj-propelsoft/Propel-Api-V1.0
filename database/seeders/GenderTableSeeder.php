<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pims_person_genders')->insert(array(
            array(
                'gender' => "Male",
            ),
            array(
                'gender' => "FeMale",
            ),  array(
                'gender' => "Third Gender",
            )
        ));
    }
}
