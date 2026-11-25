<?php

namespace App\Tests\Storage;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Storage\CourseImageStorage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final class CourseImageStorageTest extends KernelTestCase
{
    /** A 1×1 transparent PNG. */
    public const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private string $stored = '';

    public static function storeImage(Course $course, string $originalName, string $id): string
    {
        $file = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($file, base64_decode(self::PNG));
        self::getContainer()->get(CourseImageStorage::class)->store($course, new File($file), $originalName, $id);

        return self::getContainer()->getParameter('kernel.project_dir').'/public'.CourseImageStorage::PUBLIC_PATH.'/'.$course->getId();
    }

    protected function tearDown(): void
    {
        new Filesystem()->remove($this->stored);
        parent::tearDown();
    }

    public function testStoredImageIsListedFirstWithItsSluggedNameAndPublicUrl(): void
    {
        $course = self::getContainer()->get(CourseRepository::class)->findOneBy(['slug' => 'html']) ?? self::fail('Missing fixture course.');
        $directory = self::storeImage($course, 'Schéma du DOM.png', 'abcdef1234567890');
        $this->stored = $directory.'/schema-du-dom-abcdef12.png';
        // Storing the same upload again writes the same file.
        self::storeImage($course, 'Schéma du DOM.png', 'abcdef1234567890');

        $images = self::getContainer()->get(CourseImageStorage::class)->list($course);

        $this->assertFileExists($this->stored);
        $this->assertSame(['name' => 'schema-du-dom', 'url' => '/uploads/courses/'.$course->getId().'/schema-du-dom-abcdef12.png'], $images[0]);
        $this->assertCount(1, array_filter($images, fn (array $image): bool => 'schema-du-dom' === $image['name']));
    }
}
