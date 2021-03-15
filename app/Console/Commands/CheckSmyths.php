<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HidesTracks;
use App\Mail\StockDetected;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Nesk\Rialto\Data\JsFunction;

use function config;
use function public_path;
use function rand;

class CheckSmyths extends Command
{
    use HidesTracks;

    protected $signature = 'check:smyths';

    protected $description = "Check Smyth's for XAA stock";

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle()
    {
        [$browser, $page] = $this->setupBrowser();

        $page->goto('https://www.smythstoys.com/uk/en-gb/shop-xaa', [
            'waitUntil' => 'networkidle2'
        ]);

        $page->waitForSelector('#delivery-channel-hd');

        $page->waitFor(rand(500, 3000));

        $page->querySelector('#delivery-channel-hd')->click();

        $page->waitFor(rand(500, 3000));

        $element = $page->querySelector('.errorSelectAnother');

        $text = $page->evaluate(
            JsFunction::createWithParameters(['el'])
                ->body('return el.innerHTML;'),
            $element
        );

        $page->screenshot(['path' => $path = public_path('smyths.png')]);

        if (! str_contains($text, 'Apologies')) {
            $this->line("Stock detected at Smyth's!");

            $lastNotification = Notification::where('store', 'smyths')->latest()->first();

            if ($lastNotification === null || $lastNotification?->created_at->diffInHours() > 1) {
                $this->line('Sending email...');

                Mail::to(config('mail.from.address'))->send(new StockDetected("Smyth's", $path));

                Notification::create(['store' => 'smyths']);
            }
        } else {
            $this->line("No stock detected at Smyth's.");
        }

        $browser->close();
    }
}
