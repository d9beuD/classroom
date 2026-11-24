<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CourseControllerTest extends WebTestCase
{
    public function testCreatingACourseShowsAMessage(): void
    {
        $client = self::createClient();
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
}
