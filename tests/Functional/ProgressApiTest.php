<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Assignment;
use App\Repository\SubmissionRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProgressApiTest extends WebTestCase
{
    use CurriculumFixtureTrait;

    public function testProgressEndpointReturnsNextAssignment(): void
    {
        $client = static::createClient();
        $user = $this->createUserAndLogin($client);
        $token = $this->issueToken($user);

        $fixture = $this->persistCurriculumFixture('live');
        $second = $this->addSecondAssignment($fixture['lesson']);

        $client->request('GET', '/api/progress', [], [], ['HTTP_Authorization' => sprintf('Bearer %s', $token)]);

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($fixture['assignment']->getCode(), $payload['nextAssignment']['code']);
        self::assertCount(2, $payload['assignments']);
        self::assertSame('pending', $payload['assignments'][0]['status']);
        self::assertSame('pending', $payload['assignments'][1]['status']);

        $repository = static::getContainer()->get(SubmissionRepository::class);
        $initialCount = $repository->count([]);

        $client->jsonRequest('POST', '/api/submissions', [
            'assignment' => $fixture['assignment']->getCode(),
            'status' => 'pass',
            'cliVersion' => '0.2.0',
            'kitVersion' => 1,
            'checks' => [['id' => 'node', 'status' => 'pass']],
            'prompts' => ['remediation' => 'Restarted Docker'],
            'system' => ['os' => 'macOS'],
            'logs' => 'ok',
        ], ['HTTP_Authorization' => sprintf('Bearer %s', $token)]);

        self::assertResponseStatusCodeSame(202);

        self::assertSame($initialCount + 1, $repository->count([]));

        $client->request('GET', '/api/progress', [], [], ['HTTP_Authorization' => sprintf('Bearer %s', $token)]);
        $progress = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($second->getCode(), $progress['nextAssignment']['code']);
        self::assertSame('passed', $progress['assignments'][0]['status']);
    }

    private function addSecondAssignment(\App\Entity\Lesson $lesson): Assignment
    {
        $second = (new Assignment())
            ->setTitle('Second Assignment')
            ->setCode(sprintf('%s-%s', self::ASSIGNMENT_CODE_PREFIX, substr(bin2hex(random_bytes(8)), -8)))
            ->setDescription('Second step')
            ->setDisplayOrder(2);

        $lesson->addAssignment($second);
        $em = $this->entityManager();
        $em->persist($second);
        $em->flush();

        return $second;
    }
}
