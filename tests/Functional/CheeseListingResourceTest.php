<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\CheeseListing;
use App\Entity\User;
use App\Test\CustomApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing()
    {
       $client = self::createClient();

       $client->request('POST', '/api/cheeses', [
        // 'headers' => ['Content-Type' => 'application/json'],
        'json' => [],
       ]);

       $this->assertResponseStatusCodeSame(401);

    //    $user = new User();
    //    $user->setEmail('cheeseplease@example.com');
    //    $user->setUsername('cheeseplease');
    //    $user->setPassword('$2y$13$fsbWP0wKvy0f4aAhlhkMG.Xbc6DxzpblQiUXYdTB4yucbps0GeYjK');

    //    $em = static::getContainer()->get(EntityManagerInterface::class);
    //    $em->persist($user);
    //    $em->flush();
    
        // $this->createUser('cheeseplease@example.com', 'foo');

    //    $client->request('POST', '/login', [
    //     'headers' => ['Content-Type' => 'application/json'],
    //     'json' => [
    //         'email' => 'cheeseplease@example.com',
    //         'password' => 'foo',
    //     ],
    //    ]);

    //    $this->assertResponseStatusCodeSame(204);

        // $this->logIn($client, 'cheeseplease@example.com', 'foo');

        $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');

        $client->request('POST', '/api/cheeses', [
            // 'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
           ]);
    
           $this->assertResponseStatusCodeSame(400);

    }

    public function testUpdateCheeseListing()
    {
        $client = self::createClient();
        $user1 = $this->createUser('user1@example.com', 'foo');

        $user2 = $this->createUser('user2@example.com', 'foo');

        $cheeseListing = new CheeseListing('Block of chedar');
        $cheeseListing->setOwner($user1);
        $cheeseListing->setPrice(1000);
        $cheeseListing->setTextDescription('yummy');

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        //  test: only the creator can edit a cheese listing
        $this->logIn($client, 'user2@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'title' => 'updated',
                'owner' => '/api/users/'.$user2->getId()
            ]
        ]);
        $this->assertResponseStatusCodeSame(403);
        // var_dump($client->getResponse()->getContent(false));

        //  test: creator can not re-assign cheese listing to other owner
        $this->logIn($client, 'user1@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'title' => 'updated',
                'owner' => '/api/users/'.$user2->getId()
            ]
        ]);
        $this->assertResponseStatusCodeSame(403);
        // var_dump($client->getResponse()->getContent(false));

        $this->logIn($client, 'user1@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'title' => 'updated',
            ]
        ]);
        $this->assertResponseStatusCodeSame(200);

    }
}