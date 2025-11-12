<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CurriculumApiTest extends WebTestCase
{
    use CurriculumFixtureTrait;

    public function testCoursesEndpointReturnsVisibleCoursesOnly(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $live = $this->persistCurriculumFixture('live');
        $preview = $this->persistCurriculumFixture('preview');
        $draft = $this->persistCurriculumFixture('draft');

        $client->request('GET', '/api/courses');

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('courses', $payload);
        $slugs = array_column($payload['courses'], 'slug');
        self::assertContains($live['course']->getSlug(), $slugs);
        self::assertContains($preview['course']->getSlug(), $slugs);
        self::assertNotContains($draft['course']->getSlug(), $slugs, 'draft courses must stay hidden');

        foreach ($payload['courses'] as $coursePayload) {
            self::assertContains($coursePayload['status'], ['live', 'preview']);
        }
    }

    public function testLessonEndpointIncludesAssignments(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('live');

        $client->request('GET', sprintf('/api/lessons/%s', $fixture['lesson']->getSlug()));

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('lesson', $payload);
        self::assertSame($fixture['lesson']->getSlug(), $payload['lesson']['slug']);
        self::assertCount(1, $payload['lesson']['assignments']);
        self::assertSame($fixture['assignment']->getCode(), $payload['lesson']['assignments'][0]['code']);
    }

    public function testAssignmentEndpointProvidesParentContext(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('live');

        $client->request('GET', sprintf('/api/assignments/%s', $fixture['assignment']->getCode()));

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('assignment', $payload);
        self::assertSame($fixture['assignment']->getCode(), $payload['assignment']['code']);
        self::assertSame($fixture['lesson']->getSlug(), $payload['assignment']['lesson']['slug']);
        self::assertSame($fixture['course']->getSlug(), $payload['assignment']['course']['slug']);
    }

    public function testAssignmentEndpointRejectsDraftCourses(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client);

        $fixture = $this->persistCurriculumFixture('draft');

        $client->request('GET', sprintf('/api/assignments/%s', $fixture['assignment']->getCode()));

        self::assertResponseStatusCodeSame(404);
    }
}
