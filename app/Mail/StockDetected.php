<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use function storage_path;

class StockDetected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $store,
        protected string $path
    ) {
    }

    public function build()
    {
        return $this
            ->markdown('emails.stock', ['store' => $this->store])
            ->attach($this->path);
    }
}
