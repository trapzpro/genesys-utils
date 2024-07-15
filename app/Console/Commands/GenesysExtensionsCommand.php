<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use League\Csv\Writer;
use League\Csv\Reader;

class GenesysExtensionsCommand extends Command
{
    protected $signature = 'genesys:extensions';
    protected $description = 'Retrieve Genesys Voice Cloud extensions and write to CSV';

    private $apiUrl = 'https://api.usw2.pure.cloud/api/v2/telephony/providers/edges/extensions';
    private $accessToken = 'ybbGRrKJ2IF8maYzqg1CCdSrIwk1_f2LX6kPqQmlVLmx-qQSIeclEhSYisXZB14BP7btaE_8WHNiJvFjk0xBnw';
    private $csvFilePath = 'genesys_extensions.csv';

    public function handle()
    {
        $this->info('Starting Genesys Extensions retrieval...');

        $existingExtensions = $this->getExistingExtensions();
        $writer = $this->setupCsvWriter();

        $pageSize = 100;
        $pageNumber = 1;
        $totalProcessed = 0;

        do {
            $response = $this->fetchExtensions($pageSize, $pageNumber);
            $extensions = $response['entities'] ?? [];

            foreach ($extensions as $extension) {
                if (!in_array($extension['number'], $existingExtensions)) {
                    $this->processExtension($extension, $writer);
                    $existingExtensions[] = $extension['number'];
                    $totalProcessed++;
                }
            }

            $this->info("Processed page {$pageNumber}. Total processed: {$totalProcessed}");
            $pageNumber++;
        } while (!empty($extensions));

        $this->info('Finished processing all extensions.');
    }

    private function fetchExtensions($pageSize, $pageNumber)
    {
        $response = Http::withToken($this->accessToken)
            ->get($this->apiUrl, [
                'pageSize' => $pageSize,
                'pageNumber' => $pageNumber,
            ]);

        if (!$response->successful()) {
            $this->error('Failed to fetch extensions: ' . $response->body());
            exit(1);
        }

        return $response->json();
    }

    private function setupCsvWriter()
    {
        $writer = Writer::createFromPath($this->csvFilePath, 'a+');

        if (filesize($this->csvFilePath) === 0) {
            $writer->insertOne(['Extension Number', 'state', 'Owner Name', 'Owner Type', 'Owner Email', 'Extension Pool ID']);
        }

        return $writer;
    }

    private function getExistingExtensions()
    {
        if (!file_exists($this->csvFilePath)) {
            return [];
        }

        $reader = Reader::createFromPath($this->csvFilePath, 'r');
        $reader->setHeaderOffset(0);

        return array_column(iterator_to_array($reader), 'Extension Number');
    }

    private function processExtension($extension, $writer)
    {
        $writer->insertOne([
            $extension['number'] ?? '',
            $extension['state'] ?? '',
            $extension['owner']['name'] ?? '',
            $extension['ownerType'] ?? '',
            $extension['owner']['email'] ?? '',
            $extension['extensionPool']['id'] ?? '',
        ]);

        $this->line("Processed extension: {$extension['number']}");
    }
}
