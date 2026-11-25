<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CourseControllerTest extends WebTestCase
{
    #[DataProvider('courseMutationRequests')]
    public function testAnonymousUsersCannotMutateCourses(string $method, string $uri): void
    {
        $client = self::createClient();
        $client->request($method, $uri);

        self::assertResponseRedirects('/login');
    }

    #[DataProvider('courseMutationRequests')]
    public function testStudentsCannotMutateCourses(string $method, string $uri): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request($method, $uri);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreatingACourseShowsAMessage(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/new');
        $client->submitForm('Save', [
            'course[label]' => 'Cours flash '.uniqid(),
            'course[icon]' => 'code',
            'course[iconColor]' => '#000000',
            'course[iconColorAlt]' => '#ffffff',
        ]);

        self::assertResponseRedirects('/course');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert.theme-success', 'Cours créé.');
    }

    public function testProfessorCanEditACourse(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/html/edit');
        $client->submitForm('Update', [
            'course[label]' => 'HTML',
            'course[icon]' => 'code',
            'course[iconColor]' => '#e34f26',
            'course[iconColorAlt]' => '#ffffff',
        ]);

        self::assertResponseRedirects('/course');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert.theme-success', 'Cours enregistré.');
    }

    public function testDeleteTokenIsBoundToTheCourseId(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));
        $courses = self::getContainer()->get(CourseRepository::class);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $targetCourse = $courses->findOneBy(['slug' => 'html']) ?? self::fail('Missing fixture course.');
        $targetId = $targetCourse->getId() ?? self::fail('Missing fixture course id.');
        $targetSlug = $targetCourse->getSlug() ?? self::fail('Missing fixture course slug.');
        $numericSlug = (string) $targetId;

        $client->request('GET', '/course/new');
        $client->submitForm('Save', [
            'course[label]' => $numericSlug,
            'course[icon]' => 'code',
            'course[iconColor]' => '#000000',
            'course[iconColorAlt]' => '#ffffff',
        ]);
        self::assertResponseRedirects('/course');

        $numericCourse = $courses->findOneBy(['slug' => $numericSlug]) ?? self::fail('Missing numeric-slug course.');
        $numericCourseId = $numericCourse->getId() ?? self::fail('Missing numeric-slug course id.');
        $crawler = $client->request('GET', '/course/'.$numericSlug.'/overview');
        $token = $crawler->filter('form[action="/course/'.$numericSlug.'"] input[name="_token"]')->attr('value')
            ?? self::fail('Missing course deletion token.');

        $client->request('POST', '/course/'.$targetSlug, ['_token' => $token]);

        self::assertResponseRedirects('/course');
        $entityManager->clear();
        self::assertNotNull($courses->find($targetId));

        $client->request('POST', '/course/'.$numericSlug, ['_token' => $token]);

        self::assertResponseRedirects('/course');
        $entityManager->clear();
        self::assertNull($courses->find($numericCourseId));
        self::assertNotNull($courses->find($targetId));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function courseMutationRequests(): iterable
    {
        yield 'new form' => ['GET', '/course/new'];
        yield 'new submission' => ['POST', '/course/new'];
        yield 'edit form' => ['GET', '/course/html/edit'];
        yield 'edit submission' => ['POST', '/course/html/edit'];
        yield 'delete submission' => ['POST', '/course/html'];
    }
}
