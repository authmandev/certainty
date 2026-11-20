<?php

namespace App\Console\Commands;

use App\Enums\ReferenceGroup;
use App\Enums\UserRole;
use App\Models\ReferenceOption;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('certainty:check-production')]
#[Description('Check the configuration a production server needs before launch')]
class CheckProductionReadiness extends Command
{
    /**
     * @var list<array{string, string}>
     */
    private array $results = [];

    /**
     * Execute the console command. Failures block launch; warnings are
     * missing client details or settings worth a second look
     * (docs/deployment.md).
     */
    public function handle(): int
    {
        $this->check('Application key is set', filled(config('app.key')));
        $this->check('Debug mode is off', ! config('app.debug'), fail: app()->isProduction());
        $this->check('APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://'), fail: false);
        $this->check('Session cookies are HTTPS-only', (bool) config('session.secure'), fail: false);
        $this->check('Queue is not synchronous (report PDFs run in the background)', config('queue.default') !== 'sync', fail: false);
        $this->check('Mail is sent by a real mailer (password resets)', ! in_array(config('mail.default'), ['log', 'array'], true), fail: false);

        $this->check('Reference lists are seeded (run ReferenceOptionSeeder)', $this->referenceListsSeeded());
        $this->check('At least one administrator exists (certainty:create-admin)', User::query()->where('role', UserRole::Admin)->exists());

        $this->check('Bank transfer details are set (CERTAINTY_BANK_*)', filled(config('certainty.bank.iban')) && filled(config('certainty.bank.name')), fail: false);
        $this->check('Public contact email is set (CERTAINTY_CONTACT_EMAIL)', filled(config('certainty.contact.email')), fail: false);

        foreach (['receipts', 'reports'] as $files) {
            $disk = (string) config("certainty.{$files}.disk");
            $this->check("The {$files} disk ({$disk}) is private and writable", $this->diskIsPrivateAndWritable($disk));
        }

        $this->check('The PDF renderer is available', $this->pdfRendererAvailable());

        $this->table(['Check', 'Result'], $this->results);

        $failures = count(array_filter($this->results, fn (array $row): bool => $row[1] === 'FAIL'));
        $warnings = count(array_filter($this->results, fn (array $row): bool => $row[1] === 'WARN'));

        if ($failures > 0) {
            $this->error("{$failures} check(s) failed, {$warnings} warning(s).");

            return self::FAILURE;
        }

        $this->info($warnings > 0 ? "Ready, with {$warnings} warning(s)." : 'Ready for production.');

        return self::SUCCESS;
    }

    private function check(string $label, bool $passed, bool $fail = true): void
    {
        $this->results[] = [$label, $passed ? 'OK' : ($fail ? 'FAIL' : 'WARN')];
    }

    private function referenceListsSeeded(): bool
    {
        $groups = ReferenceOption::query()->where('is_active', true)->distinct()->pluck('group')->map(fn (ReferenceGroup $group): string => $group->value);

        return collect(ReferenceGroup::cases())->every(fn (ReferenceGroup $group): bool => $groups->contains($group->value));
    }

    private function diskIsPrivateAndWritable(string $disk): bool
    {
        $config = config("filesystems.disks.{$disk}");

        if (! is_array($config) || ($config['visibility'] ?? 'private') === 'public' || ($config['serve'] ?? false) === true || isset($config['url'])) {
            return false;
        }

        try {
            $probe = '.certainty-write-check-'.bin2hex(random_bytes(4));
            Storage::disk($disk)->put($probe, 'ok');
            Storage::disk($disk)->delete($probe);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function pdfRendererAvailable(): bool
    {
        return match (config('laravel-pdf.driver')) {
            'chrome' => is_executable((string) config('laravel-pdf.chrome.chrome_binary')),
            'gotenberg' => filled(config('laravel-pdf.gotenberg.url')),
            'cloudflare' => filled(config('laravel-pdf.cloudflare.api_token')) && filled(config('laravel-pdf.cloudflare.account_id')),
            default => false,
        };
    }
}
