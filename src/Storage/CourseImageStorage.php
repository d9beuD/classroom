<?php

namespace App\Storage;

use App\Entity\Course;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * The images of a course, stored in public/uploads/courses/{course id}/ and served as is.
 *
 * A file is named after its original name plus an id suffix, so the list stays readable and
 * storing the same upload twice writes the same file.
 */
final readonly class CourseImageStorage
{
    public const string PUBLIC_PATH = '/uploads/courses';

    public function __construct(
        private SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public'.self::PUBLIC_PATH)]
        private string $directory,
    ) {
    }

    public function store(Course $course, File $file, string $originalName, string $id): void
    {
        $name = $this->slugger->slug(pathinfo($originalName, \PATHINFO_FILENAME))->lower()->truncate(60, cut: false)->toString() ?: 'image';
        $file->move($this->directory.'/'.$course->getId(), \sprintf('%s-%s.%s', $name, substr($id, 0, 8), $file->guessExtension() ?? 'bin'));
    }

    /**
     * @return list<array{name: string, url: string}> newest first
     */
    public function list(Course $course): array
    {
        $files = glob($this->directory.'/'.$course->getId().'/*') ?: [];
        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return array_map(fn (string $file): array => [
            'name' => (string) preg_replace('/-[^-]+\.\w+$/', '', basename($file)),
            'url' => \sprintf('%s/%d/%s', self::PUBLIC_PATH, $course->getId(), rawurlencode(basename($file))),
        ], $files);
    }
}
