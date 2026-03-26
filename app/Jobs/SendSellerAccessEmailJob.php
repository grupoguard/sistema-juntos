<?php

namespace App\Jobs;

use App\Mail\SellerAccessMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSellerAccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $password;
    public string $loginUrl;

    public $tries = 3;

    public function __construct(string $name, string $email, string $password, string $loginUrl)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(new SellerAccessMail(
            name: $this->name,
            email: $this->email,
            password: $this->password,
            loginUrl: $this->loginUrl
        ));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar email de acesso do seller', [
            'email' => $this->email,
            'message' => $exception->getMessage(),
        ]);
    }
}