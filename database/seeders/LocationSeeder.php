<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/turkiye_il_ilce.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("turkiye_il_ilce.json not found at: $jsonPath");
            return;
        }

        $cities = json_decode(file_get_contents($jsonPath), true);
        if (!$cities) {
            $this->command->error('Failed to parse JSON data');
            return;
        }

        $progress = $this->command->getOutput()->createProgressBar(count($cities));
        $progress->start();

        foreach ($cities as $item) {
            $il = $item[0];
            $ilSlug = $item[1];
            $ilceler = $item[2];

            $parent = Location::create([
                'name' => $il,
                'slug' => $ilSlug,
                'parent_id' => null,
            ]);

            foreach ($ilceler as $ilce) {
                $ilceSlug = \Str::slug($ilce);
                Location::create([
                    'name' => $ilce,
                    'slug' => $ilSlug . '-' . $ilceSlug,
                    'parent_id' => $parent->id,
                ]);
            }

            $progress->advance();
        }

        $progress->finish();
        $this->command->newLine();
        $this->command->info(count($cities) . ' il yüklendi.');
    }
}
