<?php


namespace App\Console\Commands\Concerns;


use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

use function app;
use function compact;
use function count;
use function rand;

trait HidesTracks
{
    protected array $resolutions = [
        [1024, 768],
        [1280, 800],
        [1440, 900],
        [1920, 1080],
        [2560, 1440]
    ];

    /** @noinspection PhpUndefinedMethodInspection */
    protected function setupBrowser()
    {
        $browser = (new Puppeteer())->launch([
            'headless' => ! app()->environment('local'),
            'args'     => [
                '--disable-blink-features=AutomationControlled'
            ]
        ]);

        $page = $browser->newPage();

        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3312.0 Safari/537.36';
        [$width, $height] = $this->resolutions[rand(0, count($this->resolutions) - 1)];

        $width += rand(-150, 150);
        $height += rand(-150, 150);

        $this->line("Setting UA to [{$userAgent}]...");
        $page->setUserAgent($userAgent);

        $this->line("Setting screen res to [{$width}, {$height}]...");
        $page->setViewport(compact('width', 'height'));

        $setup = JsFunction::createWithBody(<<<JS
            Object.defineProperty(navigator, "languages", {
              get: function() {
                return ["en-GB", "en"];
              }
            });
            
            Object.defineProperty(navigator, 'plugins', {
              get: function() {
                return [1, 2, 3, 4, 5];
              }
            });
            
            Object.defineProperty(navigator, 'webdriver', {
              get: function () {
                return false;
              }
            });
            JS
        );

        $page->evaluate($setup);

        return [$browser, $page];
    }
}