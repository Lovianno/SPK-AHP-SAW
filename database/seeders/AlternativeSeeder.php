<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlternativeSeeder extends Seeder
{
    public function run(): void
    {
        $alternatives = [
            ['Kamila Kost'],
            ['Tripelsi Kost'],
            ['Kost Soedarmo'],
            ['Kost Bu Kusnul'],
            ['Billo Kost'],
            ['Wisma Pusparini'],
            ['Kost Zaze'],
            ['Degrasia Home'],
            ['Lariz Kost'],
            ['Cozy Kost'],
            ['Kost Sadewa Living'],
            ['Kos Medina'],
            ['Nine Coliving'],
            ['Kos Griya Ameera'],
            ['Hi Smart Boarding House'],
            ["D'Kost Medokan"],
            ['The Wood Kost'],
            ['The Alfaeyza Kost'],
            ['Kost Brody'],
            ['Kost Campur Sirimiri'],
            ['Uni House'],
            ['Kost Julian'],
            ['Kost Mandiri'],
            ['Kost We Goona House'],
            ['Kost Bu Rizka'],
            ['Ratna Kost'],
            ['Kost Melfin'],
            ['Kost Smart'],
            ['Kost Morr Premium'],
            ['Kost Mamar'],
        ];

        $now = now();

        foreach ($alternatives as [$name]) {
            $existing = DB::table('alternatives')->where('name', $name)->first();
            DB::table('alternatives')->updateOrInsert(['name' => $name], [
                'name' => $name,
                'created_at' => $existing->created_at ?? $now,
                'updated_at' => $now,
            ]);
        }

    }
}
