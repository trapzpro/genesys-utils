<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;
use Exception;
use Throwable;

class ApiComparisonService
{
    private Api $firstApi;
    private Api $secondApi;
    private array $outputColumns;
    private array $comparisonProperties;

    public function __construct(
        Api $firstApi,
        Api $secondApi,
        array $outputColumns,
        $comparisonProperties
    ) {
        $this->firstApi = $firstApi;
        $this->secondApi = $secondApi;
        $this->outputColumns = $this->validateOutputColumns($outputColumns);
        $this->comparisonProperties = $this->normalizeComparisonProperties($comparisonProperties);
    }

    private function validateOutputColumns(array $outputColumns): array
    {
        foreach ($outputColumns as $column) {
            if (!isset($column['api']) || !isset($column['property'])) {
                throw new Exception("Invalid output column configuration. Each column must have 'api' and 'property' keys.");
            }
            if (!in_array($column['api'], ['first', 'second'])) {
                throw new Exception("Invalid API specified in output column. Must be 'first' or 'second'.");
            }
        }
        return $outputColumns;
    }

    private function normalizeComparisonProperties($comparisonProperties): array
    {
        if (is_string($comparisonProperties)) {
            return [$comparisonProperties => $comparisonProperties];
        }
        return $comparisonProperties;
    }

    public function generateComparisonCsv(string $outputPath): void
    {
        try {
            $firstApiItems = $this->getAllItemsFromFirstApi();
            $comparisonResults = $this->compareWithSecondApi($firstApiItems);
            $this->generateCsv($comparisonResults, $outputPath);
        } catch (Exception $e) {
            Log::error('Failed to generate comparison CSV: ' . $e->getMessage());
            throw new Exception('Failed to generate comparison CSV. Please check the logs for more details.');
        }
    }

    private function getAllItemsFromFirstApi(): array
    {
        $allItems = [];
        $nextUrl = $this->firstApi->getBaseUrl();
        $pageCount = 0;

        while ($nextUrl && $pageCount < 100) { // Safeguard against infinite loops
            try {
                $response = Http::withHeaders($this->firstApi->getHeaders())
                    ->timeout($this->firstApi->getTimeout())
                    ->{strtolower($this->firstApi->getHttpMethod())}($nextUrl, $this->firstApi->getQueryParams());

                if ($response->failed()) {
                    throw new Exception('HTTP request failed: ' . $response->status());
                }

                $data = $response->json();

                // Use the dataKey if specified, otherwise use the whole response
                $items = $this->firstApi->getDataKey() ? ($data[$this->firstApi->getDataKey()] ?? []) : $data;

                if (!is_array($items)) {
                    throw new Exception('Unexpected data format from first API');
                }

                $allItems = array_merge($allItems, $items);

                // Use the nextPageKey if specified, otherwise assume no more pages
                $nextUrl = $this->firstApi->getNextPageKey() ? ($data[$this->firstApi->getNextPageKey()] ?? null) : null;
                $pageCount++;
            } catch (Exception $e) {
                Log::error('Error fetching data from first API: ' . $e->getMessage());
                throw new Exception('Failed to fetch all items from the first API. Please check the logs for more details.');
            }
        }

        if ($pageCount >= 100) {
            Log::warning('Reached maximum page count when fetching from first API');
        }

        return $allItems;
    }

    private function compareWithSecondApi(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            try {
                $firstApiId = $item[$this->firstApi->getIdProperty()] ?? null;

                if ($firstApiId === null) {
                    throw new Exception("Missing ID property '{$this->firstApi->getIdProperty()}' in item from first API");
                }

                $response = Http::withHeaders($this->secondApi->getHeaders())
                    ->timeout($this->secondApi->getTimeout())
                    ->{strtolower($this->secondApi->getHttpMethod())}(
                        $this->secondApi->getBaseUrl(),
                        array_merge($this->secondApi->getQueryParams(), [$this->secondApi->getIdProperty() => $firstApiId])
                    );

                if ($response->failed()) {
                    throw new Exception('HTTP request failed: ' . $response->status());
                }

                $secondApiData = $response->json();

                $exists = !empty($secondApiData);

                $result = [
                    'exists' => $exists ? 'Yes' : 'No'
                ];

                foreach ($this->outputColumns as $column) {
                    $columnName = $column['property'];
                    if ($column['api'] === 'first') {
                        $result[$columnName] = $item[$columnName] ?? null;
                    } elseif ($column['api'] === 'second') {
                        $result[$columnName] = $exists ? ($secondApiData[$columnName] ?? null) : null;
                    }
                }

                foreach ($this->comparisonProperties as $firstApiProp => $secondApiProp) {
                    $comparisonKey = "{$firstApiProp}_comparison";
                    if ($exists) {
                        $result[$comparisonKey] =
                            ($item[$firstApiProp] ?? '') ===
                            ($secondApiData[$secondApiProp] ?? '') ? 'Match' : 'Mismatch';
                    } else {
                        $result[$comparisonKey] = 'N/A';
                    }
                }

                $results[] = $result;
            } catch (Exception $e) {
                Log::error('Error processing item: ' . json_encode($item) . '. Error: ' . $e->getMessage());
                // Continue processing other items
            }
        }

        return $results;
    }

    private function generateCsv(array $data, string $outputPath): void
    {
        try {
            $csv = Writer::createFromPath($outputPath, 'w+');

            // Add header row
            $headerRow = array_merge(
                ['exists'],
                array_map(function ($column) {
                    return $column['property'];
                }, $this->outputColumns),
                array_map(function ($prop) {
                    return "{$prop}_comparison";
                }, array_keys($this->comparisonProperties))
            );
            $csv->insertOne($headerRow);

            // Add data rows
            foreach ($data as $row) {
                $csv->insertOne($row);
            }
        } catch (Throwable $e) {
            Log::error('Error generating CSV: ' . $e->getMessage());
            throw new Exception('Failed to generate CSV file. Please check the logs for more details.');
        }
    }
}
