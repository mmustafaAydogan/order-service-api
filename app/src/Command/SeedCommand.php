<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Seed database with initial data')]
class SeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('data-dir', InputArgument::REQUIRED, 'Path to the data directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dataDir = $input->getArgument('data-dir');

        try {
            $authorsMap = $this->seedAuthors($output, $dataDir);
            $categoriesMap = $this->seedCategories($output, $dataDir);
            $this->seedProducts($output, $dataDir, $authorsMap, $categoriesMap);
        } catch (\Throwable $throwable) {
            $output->writeln(sprintf("%s <--> %s <--> %s", $throwable->getMessage(), $throwable->getFile(), $throwable->getLine()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function seedAuthors(OutputInterface $output, string $dataDir): array
    {
        if ($this->em->getRepository(Author::class)->count() > 0) {
            $output->writeln('Authors already seeded, skipping.');
            return [];
        }

        $data = $this->readJson($dataDir, 'authors.json');
        $map = [];

        foreach ($data as $row) {
            $author = new Author();
            $author->setAuthorName($row['author_name']);
            $this->em->persist($author);
            $map[$row['author_id']] = $author;
        }

        $this->em->flush();
        $output->writeln(sprintf('Seeded %d authors.', count($map)));

        return $map;
    }

    private function seedCategories(OutputInterface $output, string $dataDir): array
    {
        if ($this->em->getRepository(Category::class)->count() > 0) {
            $output->writeln('Categories already seeded, skipping.');
            return [];
        }

        $data = $this->readJson($dataDir, 'categories.json');
        $map = [];

        foreach ($data as $row) {
            $category = new Category();
            $category->setCategoryTitle($row['category_title']);
            $this->em->persist($category);
            $map[$row['category_id']] = $category;
        }

        $this->em->flush();
        $output->writeln(sprintf('Seeded %d categories.', count($map)));

        return $map;
    }

    private function seedProducts(OutputInterface $output, string $dataDir, array $authorsMap, array $categoriesMap): void
    {
        if ($this->em->getRepository(Product::class)->count() > 0) {
            $output->writeln('Products already seeded, skipping.');
            return;
        }

        if (empty($authorsMap) || empty($categoriesMap)) {
            $output->writeln('Skipping products: authors or categories map is empty.');
            return;
        }

        $data = $this->readJson($dataDir, 'products.json');

        foreach ($data as $row) {
            $product = new Product();
            $product->setTitle($row['title']);
            $product->setListPrice($row['list_price']);
            $product->setStockQuantity($row['stock_quantity']);
            $product->setAuthor($authorsMap[$row['author_id']]);
            $product->setCategory($categoriesMap[$row['category_id']]);
            $this->em->persist($product);
        }
        $this->em->flush();
        $output->writeln(sprintf('Seeded %d products.', count($data)));
    }

    private function readJson(string $dataDir, string $filename): array
    {
        return json_decode(file_get_contents($dataDir . '/' . $filename), true);
    }
}
