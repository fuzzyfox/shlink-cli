<?php

namespace App\Commands;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Exception\RuntimeException;

class ShortUrlGenerate extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'short-url:generate {longUrl?}
                           {--t|tags=* : Tags to apply to the new short URL}
                           {--s|validSince= : The date from which this short URL will be valid. If someone tries to access it before this date, it will not be found.}
                           {--u|validUntil= : The date until which this short URL will be valid. If someone tries to access it after this date, it will not be found.}
                           {--c|customSlug= : If provided, this slug will be used instead of generating a short code}
                           {--m|maxVisits= : This will limit the number of visits for this short URL.}
                           {--f|findIfExists : This will force existing matching URL to be returned if found, instead of creating a new one.}
                           {--F|noFindIfExists : This will force NO matching of existing URLs, always creating a new one. (overrides --findIfExists)}
                           {--d|domain= : The domain to which this short URL will be attached.}
                           {--l|shortCodeLength= : The length for generated short code (it will be ignored if --customSlug was provided).}
                           {--S|server=default : The shlink server config to use from ~/.shlinkrc}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generates a short URL for provided long URL and returns it';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws RuntimeException
     * @throws RequestException
     */
    public function handle()
    {
        // Fetch longUrl from STDIN if it was piped in
        stream_set_blocking(STDIN, 0);
        $longUrl = fgets(STDIN);
        stream_set_blocking(STDIN, 1);

        // Load server configs
        $serverUrl = config("servers.{$this->option('server')}.url");
        $apiKey = config("servers.{$this->option('server')}.apiKey");

        // Ensure minimum config exists
        if (!$serverUrl) throw new RuntimeException("\"{$this->option('server')}\" server \"url\" has not be configured.");

        // Ensure we have a longUrl
        $longUrl = trim($this->argument('longUrl') ?? $longUrl ?: $this->ask('URL to shorten?'));
        if (!$longUrl) throw new RuntimeException('Not enough arguments (missing: "longUrl").');

        // Get any options and add them to the params array
        $params = Arr::only(
            array_merge(
                config("servers.{$this->option('server')}", []),
                Arr::where($this->options(), function ($value) { return $value !== null; })
            ),
            ['tags', 'validSince', 'validUntil', 'customSlug', 'maxVisits', 'findIfExists', 'domain', 'shortCodeLength']
        );

        if (Arr::get($params, 'maxVisits'))
            Arr::set($params, 'maxVisits', (int) Arr::get($params, 'maxVisits'));

        if (Arr::get($params, 'shortCodeLength'))
            Arr::set($params, 'shortCodeLength', (int) Arr::get($params, 'shortCodeLength'));

        if ($this->option('noFindIfExists') && Arr::get($params, 'findIfExists')) {
            $params['findIfExists'] = false;
        }

        // Add longUrl to params
        $params['longUrl'] = $longUrl;

        // Make request to api
        $response = Http::withHeaders(['x-api-key' => $apiKey])
            ->post("{$serverUrl}/rest/v2/short-urls", $params);

        // Throw error if not a successful response
        if (!$response->ok()) {
            throw new RuntimeException($response->json()['detail']);
        }

        // Output short url if successful
        $this->line($response['shortUrl']);

        return 0;
    }
}
