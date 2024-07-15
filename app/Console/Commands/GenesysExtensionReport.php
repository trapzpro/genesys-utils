<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenesysExtensionReport extends Command
{
    protected $signature = 'genesys:extension-report';
    protected $description = 'Generate a report of Genesys Cloud extensions';

    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $accessToken;

    public function __construct()
    {
        parent::__construct();
        $this->clientId = config('services.genesys.client_id');
        $this->clientSecret = config('services.genesys.client_secret');
        $this->baseUrl = config('services.genesys.base_url');
    }

    public function handle()
    {
        $this->authenticate();
        $extensions = $this->getAllExtensions();
        $this->generateExcelReport($extensions);
    }

    private function authenticate()
    {
        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            $this->error('Failed to authenticate with Genesys Cloud API');
            exit(1);
        }

        $this->accessToken = $response->json()['access_token'];
    }

    private function getAllExtensions()
    {
        $extensions = [];
        $pageSize = 200;
        $pageNumber = 1;

        while (true) {
            $response = Http::withToken($this->accessToken)->get("{$this->baseUrl}/api/v2/telephony/providers/edges/extensions", [
                'pageSize' => $pageSize,
                'pageNumber' => $pageNumber,
                'sortBy' => 'number',
                'sortOrder' => 'asc',
            ]);

            if ($response->failed()) {
                $this->error("Error fetching extensions: {$response->status()}");
                exit(1);
            }

            $data = $response->json();
            $extensions = array_merge($extensions, $data['entities']);

            if (count($data['entities']) < $pageSize) {
                break;
            }

            $pageNumber++;
        }

        return $extensions;
    }

    private function generateExcelReport($extensions)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Extension', 'State', 'Number', 'Owner Name', 'Owner Type', 'Division Name', 'Extension Pool ID'];
        $sheet->fromArray($headers, null, 'A1');

        // Populate data
        $row = 2;
        foreach ($extensions as $ext) {
            $sheet->fromArray([
                $ext['number'],
                $ext['state'],
                $ext['number'],
                $ext['owner']['name'] ?? '',
                $ext['owner']['type'] ?? '',
                $ext['owner']['division']['name'] ?? '',
                $ext['extensionPool']['id'] ?? '',
            ], null, "A{$row}");
            $row++;
        }

        // Save the file
        $writer = new Xlsx($spreadsheet);
        $filename = 'extension_report_' . date('Ymd_His') . '.xlsx';
        $writer->save(storage_path('app/' . $filename));

        $this->info("Report saved as {$filename}");
    }
}
