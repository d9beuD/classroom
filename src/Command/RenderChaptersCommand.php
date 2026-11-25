<?php

namespace App\Command;

use App\Markdown\ChapterRenderer;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:chapters:render',
    description: 'Renders the HTML of every chapter again (e.g. after a change to the renderer)',
)]
final readonly class RenderChaptersCommand
{
    public function __construct(
        private ChapterRepository $chapters,
        private ChapterRenderer $renderer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $chapters = $this->chapters->findAll();

        foreach ($io->progressIterate($chapters) as $chapter) {
            $chapter->setRenderedContent($this->renderer->render($chapter, force: true));
        }
        $this->entityManager->flush();

        $io->success(\sprintf('%d chapter(s) rendered.', \count($chapters)));

        return Command::SUCCESS;
    }
}
