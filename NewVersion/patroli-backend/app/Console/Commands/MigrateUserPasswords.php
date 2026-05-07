<?php

namespace App\Console\Commands;

use App\Helpers\CIDecryption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateUserPasswords extends Command
{
    protected $signature   = 'users:migrate-passwords
                                {--record-owner= : Filter per RecordOwnerID (opsional)}
                                {--dry-run       : Hanya preview, tidak update ke DB}';

    protected $description = 'Decrypt password CI3 → simpan ke TempPassword & LaravelPassword';

    public function handle(): int
    {
        $ciKey       = config('app.ci_encryption_key');
        $dryRun      = $this->option('dry-run');
        $recordOwner = $this->option('record-owner');

        if (empty($ciKey)) {
            $this->error('CI_ENCRYPTION_KEY belum diset di .env');
            return self::FAILURE;
        }

        // 1. Read all users (mirror dari ReadUser CI)
        $query = DB::table('users')->select('id', 'username', 'RecordOwnerID', 'password', 'TempPassword');

        if ($recordOwner) {
            $query->where('RecordOwnerID', $recordOwner);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user ditemukan.');
            return self::SUCCESS;
        }

        $this->info("Total user: {$users->count()}");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $ok     = 0;
        $skip   = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            $bar->advance();

            // Skip jika sudah punya TempPassword
            if (!empty($user->TempPassword)) {
                $skip++;
                continue;
            }

            // 2. Decrypt password CI3
            $plain = CIDecryption::decrypt((string) $user->password, $ciKey);

            if ($plain === false || $plain === '') {
                $failed++;
                $errors[] = "ID {$user->id} ({$user->username}@{$user->RecordOwnerID}): gagal decrypt";
                continue;
            }

            // 3. Update TempPassword & LaravelPassword
            if (!$dryRun) {
                DB::table('users')->where('id', $user->id)->update([
                    'TempPassword'    => $plain,
                    'LaravelPassword' => Hash::make($plain),
                ]);
            }

            $ok++;
        }

        $bar->finish();
        $this->newLine(2);

        // Ringkasan hasil
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Berhasil di-migrate' , $ok],
                ['Skip (sudah ada)'   , $skip],
                ['Gagal decrypt'      , $failed],
            ]
        );

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('Detail gagal:');
            foreach ($errors as $err) {
                $this->line("  • $err");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('--dry-run aktif: tidak ada perubahan yang disimpan ke DB.');
        }

        return self::SUCCESS;
    }
}
