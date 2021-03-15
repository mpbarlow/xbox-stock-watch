<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HidesTracks;
use App\Mail\StockDetected;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Nesk\Rialto\Data\JsFunction;

use function collect;
use function config;
use function public_path;
use function rand;

class CheckGame extends Command
{
    use HidesTracks;

    protected $signature = 'check:game';

    protected $description = 'Check Game for XAA stock';

    public function handle()
    {
        [$browser, $page] = $this->setupBrowser();

        $page->goto('https://xboxallaccess.game.co.uk/new-to-xbox-all-access', [
            'waitUntil' => 'networkidle2'
        ]);

        $this
            ->elementContaining(
                $page->querySelectorAll('.selection-card'),
                $page,
                'h2.mat-card-title',
                'new'
            )
            ->click();

        $page->waitFor(rand(500, 3000));

        $this
            ->elementContaining(
                $page->querySelectorAll('.mat-raised-button'),
                $page,
                'span.mat-button-wrapper',
                'Continue'
            )
            ->click();

        $page->waitFor(rand(500, 3000));

        $this
            ->elementContaining(
                $page->querySelectorAll('.selection-card'),
                $page,
                'h2.mat-card-title',
                'Home'
            )
            ->click();

        $page->waitFor(rand(500, 3000));

        $this
            ->elementContaining(
                $page->querySelectorAll('.mat-raised-button'),
                $page,
                'span.mat-button-wrapper',
                'Continue'
            )
            ->click();

        $page->waitFor(rand(500, 3000));

        $seriesX = $this->elementContaining(
            $page->querySelectorAll('.selection-card'),
            $page,
            'h2',
            'SERIES X'
        );

        $page->screenshot(['path' => $path = public_path('game.png')]);

        if ($seriesX !== null) {
            $this->line('Stock detected at Game!');

            $lastNotification = Notification::where('store', 'game')->latest()->first();

            if ($lastNotification === null || $lastNotification?->created_at->diffInHours() > 1) {
                $this->line('Sending email...');

                Mail::to(config('mail.from.address'))->send(new StockDetected("Game", $path));

                Notification::create(['store' => 'game']);
            }
        } else {
            $this->line('No stock detected at Game.');
        }

        $browser->close();
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     * @noinspection JSUnresolvedVariable
     * @noinspection PhpDocSignatureInspection
     */
    protected function elementContaining(mixed $elements, mixed $page, string $innerSelector, string $term)
    {
        return collect($elements)->filter(fn($el) => $page->evaluate(
            JsFunction::createWithParameters(['el'])->body(
                "return el.querySelector('{$innerSelector}')?.innerHTML.includes('{$term}') ?? false;"
            ),
            $el
        ))->first();
    }
}
