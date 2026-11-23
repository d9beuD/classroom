<?php

namespace App\Tests\Command;

use App\Command\RenderChaptersCommand;
use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionType;
use App\Markdown\ChapterRenderer;
use App\Markdown\RendererClient;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;

final class RenderChaptersCommandTest extends TestCase
{
    public function testEveryChapterIsRenderedAgainAndSaved(): void
    {
        $chapters = [
            (new Chapter())->addSection((new ChapterSection())->setType(ChapterSectionType::Paragraph)->setContent('Un')->setPosition(0)),
            (new Chapter())->setRenderedContent('<p>Ancien rendu</p>')
                ->addSection((new ChapterSection())->setType(ChapterSectionType::Paragraph)->setContent('Deux')->setPosition(0)),
        ];

        $repository = $this->createStub(ChapterRepository::class);
        $repository->method('findAll')->willReturn($chapters);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');
        $renderer = new ChapterRenderer(new RendererClient(new MockHttpClient(), 'http://renderer:3000', new NullLogger()));

        $application = new Application();
        $application->addCommand(new RenderChaptersCommand($repository, $renderer, $entityManager));
        $tester = new CommandTester($application->find('app:chapters:render'));

        $this->assertSame(0, $tester->execute([]));
        $this->assertStringContainsString('2 chapter(s) rendered.', $tester->getDisplay());
        $this->assertSame("<p>Un</p>\n", $chapters[0]->getRenderedContent());
        $this->assertSame("<p>Deux</p>\n", $chapters[1]->getRenderedContent());
    }
}
