<?php

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
        $this->call(WegmansStoreSeeder::class);
        $this->call(HarrisTeeterStoreSeeder::class);
        $this->call(KrogerStoreSeeder::class);
        $this->call(HEBStoreSeeder::class);
        $this->call(PriceChopperStoreSeeder::class);
        $this->call(FoodLionStoreSeeder::class);
        $this->call(WeisMarketsStoreSeeder::class);
        $this->call(AlbertsonsStoreSeeder::class);
        $this->call(GiantEagleStoreSeeder::class);
    }
}
