<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CurriculumPageTest extends WebTestCase
{
    use CurriculumFixtureTrait;

    public function testCoursesPageRendersCatalogFromDatabase(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('live');

        $crawler = $client->request('GET', '/courses');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($fixture['course']->getTitle(), $crawler->filter('body')->text());
        self::assertStringContainsString($fixture['lesson']->getTitle(), $crawler->filter('body')->text());
    }

    public function testLessonPageShowsAssignments(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('live');

        $crawler = $client->request('GET', sprintf('/lessons/%s', $fixture['lesson']->getSlug()));

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($fixture['assignment']->getCode(), $crawler->filter('body')->text());
    }

    public function testAssignmentPageShowsCliDetails(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('live');

        $crawler = $client->request('GET', sprintf('/assignments/%s', $fixture['assignment']->getCode()));

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($fixture['assignment']->getTitle(), $crawler->filter('body')->text());
        self::assertStringContainsString('$ swew doctor', $crawler->filter('body')->text());
    }

    public function testDraftLessonIsNotReachable(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('draft');

        $client->request('GET', sprintf('/lessons/%s', $fixture['lesson']->getSlug()));

        self::assertResponseStatusCodeSame(404);
    }
}
