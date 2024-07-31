<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessCSV extends Command
{
    protected $signature = 'csv:process {input : Path to input CSV file} {output : Path to output CSV file}';
    protected $description = 'Process CSV file, fill gaps in extension numbers, and write to a new file';

    public function handle()
    {
        $inputFile = $this->argument('input');
        $outputFile = $this->argument('output');

        if (!file_exists($inputFile)) {
            $this->error("Input file not found: $inputFile");
            return 1;
        }

        $inputHandle = fopen($inputFile, 'r');
        $outputHandle = fopen($outputFile, 'w');

        if (!$inputHandle || !$outputHandle) {
            $this->error("Unable to open input or output file");
            return 1;
        }

        $currentExtension = null;
        $headers = fgetcsv($inputHandle);
        fputcsv($outputHandle, $headers);

        while (($row = fgetcsv($inputHandle)) !== false) {
            $extension = (int)$row[0];

            if ($currentExtension !== null) {
                while (++$currentExtension < $extension) {
                    fputcsv($outputHandle, [$currentExtension, 'Unassigned']);
                }
            }

            fputcsv($outputHandle, $row);
            $currentExtension = $extension;
        }

        fclose($inputHandle);
        fclose($outputHandle);

        $this->info("CSV processed successfully. Output written to: $outputFile");
        return 0;
    }
}
