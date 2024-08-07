<?php

namespace App\Console\Commands;

use App\Services\E911AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use League\Csv\Writer;
use Illuminate\Support\Facades\Log;

class ExportE911DeviceUsers extends Command
{
    protected $signature = 'e911:export-device-users {output : The output CSV file path}';
    protected $description = 'Export all E911 device users to a CSV file';

    private E911AuthService $authService;

    public function __construct(E911AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    public function handle()
    {
        $outputPath = $this->argument('output');

        $this->info('Fetching device users from E911 API...');

        try {
            $deviceUsers = $this->fetchAllDeviceUsers();
            $this->exportToCsv($deviceUsers, $outputPath);
            $this->info("Successfully exported " . count($deviceUsers) . " device users to $outputPath");
        } catch (\Exception $e) {
            $this->error("Failed to export device users: " . $e->getMessage());
            Log::error("E911 device users export failed: " . $e->getMessage());
        }
    }

    private function fetchAllDeviceUsers(): array
    {
        return $this->authService->withToken(function ($token) {
            $allUsers = [];
            $page = 1;
            $perPage = 100; // Adjust based on API limits

            $this->output->progressStart();

            do {
                try {
                    $response = Http::withToken($token)->get(config('services.e911.base_url') . '/admin/deviceusers', [
                        'page' => $page,
                        'per_page' => $perPage,
                    ]);

                    if (!$response->successful()) {
                        throw new \Exception('Failed to fetch device users: ' . $response->body());
                    }

                    $data = $response->json();
                    $users = $data['data'] ?? [];
                    $allUsers = array_merge($allUsers, $users);

                    $this->output->progressAdvance(count($users));

                    $page++;

                    // Add a small delay to respect rate limits
                    usleep(100000); // 100ms delay

                } catch (\Exception $e) {
                    $this->output->progressFinish();
                    Log::error("Error fetching device users on page $page: " . $e->getMessage());
                    throw $e;
                }
            } while (count($users) == $perPage);

            $this->output->progressFinish();

            return $allUsers;
        });
    }

    private function exportToCsv(array $deviceUsers, string $outputPath): void
    {
        try {
            $csv = Writer::createFromPath($outputPath, 'w+');

            // Add CSV header
            $csv->insertOne([
                'ID', 'First Name', 'Last Name', 'Username', 'Email', 'Phone', 'Mobile',
                'Extension', 'Device Type', 'Device ID', 'Device Model', 'Created At', 'Updated At'
            ]);

            $progress = $this->output->createProgressBar(count($deviceUsers));
            $progress->start();

            foreach ($deviceUsers as $user) {
                $csv->insertOne([
                    $user['id'] ?? '',
                    $user['first_name'] ?? '',
                    $user['last_name'] ?? '',
                    $user['username'] ?? '',
                    $user['email'] ?? '',
                    $user['phone'] ?? '',
                    $user['mobile'] ?? '',
                    $user['extension'] ?? '',
                    $user['device_type'] ?? '',
                    $user['device_id'] ?? '',
                    $user['device_model'] ?? '',
                    $user['created_at'] ?? '',
                    $user['updated_at'] ?? '',
                ]);
                $progress->advance();
            }

            $progress->finish();
            $this->newLine();
        } catch (\Exception $e) {
            Log::error("Error writing to CSV file: " . $e->getMessage());
            throw new \Exception("Failed to write to CSV file: " . $e->getMessage());
        }
    }
}
