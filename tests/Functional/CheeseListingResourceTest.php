<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing()
    {
       $client = self::createClient();

       $client->request('POST', '/api/cheeses', [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => [],
       ]);

       $this->assertResponseStatusCodeSame(401);

       $user = new User();
       $user->setEmail('cheeseplease@example.com');
       $user->setUsername('cheeseplease');
       $user->setPassword('$2y$13$fsbWP0wKvy0f4aAhlhkMG.Xbc6DxzpblQiUXYdTB4yucbps0GeYjK');

       $em = static::getContainer()->get(EntityManagerInterface::class);
       $em->persist($user);
       $em->flush();

       $client->request('POST', '/login', [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => [
            'email' => 'cheeseplease@example.com',
            'password' => 'foo',
        ],
       ]);

       $this->assertResponseStatusCodeSame(204);



    }
}