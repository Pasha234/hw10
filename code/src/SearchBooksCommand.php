<?php

namespace Pasha234\Hw10;

use Pasha234\Hw10\ElasticSearchHelper;
use Pasha234\Hw10\Models\Book;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;

class SearchBooksCommand extends Command
{
    protected static $defaultName = 'app:search';

    protected function configure(): void
    {
        $this->setDescription('Делает поиск по Elasticsearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $search = $this->askSearchString($input, $output);
        $price = $this->askPrice($input, $output);
        $category = $this->askCategory($input, $output);

        if (!empty($price) && !is_numeric($price)) {
            $output->writeln('<error>Цена должна быть числом.</error>');
            return Command::FAILURE;
        }

        $searchInfoString = !empty($price) ? 
            "Ищем книги по запросу '{$search}' с максимальной ценой {$price}..." :
            "Ищем книги по запросу '{$search}'...";

        $output->writeln($searchInfoString);

        try {
            $response = ElasticSearchHelper::searchBooks($search, $price, $category);
        } catch (\Exception $e) {
            $output->writeln('<error>Ошибка обращения к Elasticsearch: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $this->outputResponse($response, $output);

        return Command::SUCCESS;
    }

    protected function askSearchString(InputInterface $input, OutputInterface $output): ?string
    {
        /** @var QuestionHelper */
        $helper = $this->getHelper('question');

        $searchQuestion = new Question('Введите название: ');
        return $helper->ask($input, $output, $searchQuestion);
    }

    protected function askPrice(InputInterface $input, OutputInterface $output): ?string
    {
        /** @var QuestionHelper */
        $helper = $this->getHelper('question');

        $priceQuestion = new Question('Введите максимальную цену (опционально): ');
        return $helper->ask($input, $output, $priceQuestion);
    }

    protected function outputResponse($response, OutputInterface $output)
    {
        if (empty($response)) {
            $output->writeln('<comment>Ничего не найдено.</comment>');
        } else {
            foreach ($response as $hit) {
                $this->outputItem($hit, $output);
            }
        }
    }

    protected function outputItem(Book $source, OutputInterface $output)
    {
        $source = $source->toArray();
        $output->writeln("→ {$source['title']} (категория - {$source['category']}) — {$source['price']} ₽");

        if (!empty($source['stock']) && is_array($source['stock'])) {
            foreach ($source['stock'] as $shop => $qty) {
                $output->writeln("   - {$shop}: {$qty} в наличии");
            }
        } else {
            $output->writeln("   - Нет информации о наличии");
        }

        $output->writeln("");
    }

    protected function askCategory(InputInterface $input, OutputInterface $output): ?string
    {
        $categories = ElasticSearchHelper::getCategories();

        if (empty($categories)) {
            $output->writeln('<comment>Категории не найдены, фильтрация не будет применена.</comment>');
            return null;
        }

        /** @var QuestionHelper */
        $helper = $this->getHelper('question');

        $question = new \Symfony\Component\Console\Question\ChoiceQuestion(
            'Выберите категорию (или оставьте пустым для всех):',
            array_merge(['(все)'], $categories),
            0
        );
        $question->setErrorMessage('Категория %s недопустима.');

        $selected = $helper->ask($input, $output, $question);

        return $selected === '(все)' ? null : $selected;
    }
}