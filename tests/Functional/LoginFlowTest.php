<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoginFlowTest extends WebTestCase
{
    public function testLoginPageRendersAndAuthenticates(): void
    {
        $client = static::createClient();
        /** @var UserRepository $users */
        $users = static::getContainer()->get(UserRepository::class);

        $existing = $users->findOneBy(['email' => 'test@example.com']);
        $em = $users->getEntityManager();
        if ($existing) {
            $em->remove($existing);
            $em->flush();
        }

        $user = (new User())
            ->setEmail('test@example.com')
            ->setName('Test User')
            ->setRoles(['ROLE_USER'])
            ->setPassword(\password_hash('secret', PASSWORD_BCRYPT));

        $em->persist($user);
        $em->flush();

        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.card-title', 'Authenticate');

        $form = $crawler->selectButton('Enter workspace')->form([
            '_username' => 'test@example.com',
            '_password' => 'secret',
        ]);

        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1.card-title', 'Build the hard parts with calm focus.');
    }
}
