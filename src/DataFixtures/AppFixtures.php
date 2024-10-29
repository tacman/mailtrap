<?php

namespace App\DataFixtures;

use App\Factory\CustomerFactory;
use App\Factory\TripFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        CustomerFactory::createMany(5);

        TripFactory::createOne([
            'name' => 'Visit Krypton',
            'slug' => 'krypton',
            'tagLine' => "Explore this advanced culture's science and technology museums and bring home some crystalline souvenirs!",
        ]);
        TripFactory::createOne([
            'name' => 'See the Pleiades',
            'slug' => 'pleiades',
            'tagLine' => 'Get an up-close look at the more than 1,000 starts that make up the Pleiades.',
        ]);
        TripFactory::createOne([
            'name' => 'Culinary Tour on the ISS',
            'slug' => 'iss',
            'tagLine' => 'Try freeze-dried, thermo-stabilized, and irradiated foods on this unique culinary adventure!',
        ]);
        TripFactory::createOne([
            'name' => 'Arrakis at sunset',
            'slug' => 'arrakis',
            'tagLine' => 'Rolling over the sands, you can see spice in the air!',
        ]);
        TripFactory::createOne([
            'name' => 'Swim Planet Miller',
            'slug' => 'miller',
            'tagLine' => 'This trip is recommended for expert level swimmers!',
        ]);
        TripFactory::createOne([
            'name' => 'Robotics Camp on Cybertron',
            'slug' => 'cybertron',
            'tagLine' => 'Try your hand at creating your own vehicle transformers!',
        ]);
    }
}
