<?php

namespace App\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\RuntimeException;

class ShortUrlParse extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'short-url:parse {shortCode : The short code to parse}
                           {--d|domain= : The domain to which the short URL is attached.}
                           {--S|server=default : The shlink server config to use from ~/.shlinkrc}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Returns the long URL behind a short code';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Load server configs
        $serverUrl = config("servers.{$this->option('server')}.url");
        $apiKey = config("servers.{$this->option('server')}.apiKey");

        // Ensure minimum config exists
        if (!$serverUrl) throw new RuntimeException("\"{$this->option('server')}\" server \"url\" has not be configured.");

        // Get any options and add them to the params array
        $params = Arr::only(
            array_merge(
                config("servers.{$this->option('server')}", []),
                Arr::where($this->options(), function ($value) { return $value !== null; })
            ),
            ['domain']
        );

        $shortCode = $this->argument('shortCode');

        if (str_contains($shortCode, '/')) {
            $url = parse_url($shortCode));
            $params['domain'] = $url['host'];
            $shortCode = substr($url['path'], 1);
        }

        // Make request to api
        $response = Http::withHeaders(['x-api-key' => $apiKey])
            ->get("{$serverUrl}/rest/v2/short-urls/{$shortCode}", $params);

        // Throw error if not a successful response
        if (!$response->ok()) {
            throw new RuntimeException($response->json()['detail']);
        }

        $this->line($response['longUrl']);

        return 0;
    }
}
