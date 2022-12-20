<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class CheeseListingResourceTest extends ApiTestCase
{
    public function testCreateCheeseListing()
    {
       $client = self::createClient();

       $client->request('POST', '/api/cheeses', [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => [],
       ]);

       $this->assertResponseStatusCodeSame(401);
    }
}