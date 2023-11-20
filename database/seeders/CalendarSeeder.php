<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('calendars')->insert([
            'client_name' => "Teszt Géza",
            'start_date' => '2023-09-08',
            'end_date' => null,
            'recurrence' => 'no_repeat',
            'weekday' => null,
            'daytime' => '8:00-10:00'
        ]);

        DB::table('calendars')->insert([
            'client_name' => "Teszt Béla",
            'start_date' => '2023-01-01',
            'end_date' => null,
            'recurrence' => 'even_weeks',
            'weekday' => 'monday',
            'daytime' => '8:00-10:00'
        ]);

        DB::table('calendars')->insert([
            'client_name' => "Teszt Gizi",
            'start_date' => '2023-01-01',
            'end_date' => null,
            'recurrence' => 'odd_weeks',
            'weekday' => 'wednesday',
            'daytime' => '12:00-16:00'
        ]);

        DB::table('calendars')->insert([
            'client_name' => "Teszt Jolán",
            'start_date' => '2023-01-01',
            'end_date' => null,
            'recurrence' => 'every_week',
            'weekday' => 'friday',
            'daytime' => '11:00-16:00'
        ]);

        DB::table('calendars')->insert([
            'client_name' => "Teszt Jóska",
            'start_date' => '2023-06-01',
            'end_date' => '2023-11-30',
            'recurrence' => 'every_week',
            'weekday' => 'thursday',
            'daytime' => '16:00-20:00'
        ]);

    }
}
