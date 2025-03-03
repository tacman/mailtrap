<?php

namespace App\Tests\Functional;

use App\Factory\TripFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TripTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    public function testViewTrips(): void
    {
        TripFactory::createOne([
            'name' => 'Visit the ISS',
            'slug' => 'iss',
            'tagLine' => 'The International Space Station',
        ]);

        $this->browser()
            ->visit('/')
            ->assertSuccessful()
            ->assertSee('Visit the ISS')
            ->assertSee('The International Space Station')
            ->click('Visit the ISS')
            ->assertOn('/trip/iss')
            ->assertSeeIn('h1', 'Visit the ISS')
            ->assertSee('The International Space Station')
        ;
    }
}
