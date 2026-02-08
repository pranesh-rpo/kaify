<?php

namespace Database\Seeders;

use App\Helpers\SslHelper;
use App\Models\Server;
use Illuminate\Database\Seeder;

class CaSslCertSeeder extends Seeder
{
    public function run()
    {
        Server::chunk(200, function ($servers) {
            foreach ($servers as $server) {
                $existingCaCert = $server->sslCertificates()->where('is_ca_certificate', true)->first();

                if (! $existingCaCert) {
                    $caCert = SslHelper::generateSslCertificate(
                        commonName: 'Kaify CA Certificate',
                        serverId: $server->id,
                        isCaCertificate: true,
                        validityDays: 10 * 365
                    );
                } else {
                    $caCert = $existingCaCert;
                }
                $caCertPath = config('constants.kaify.base_config_path').'/ssl/';

                $commands = collect([
                    "mkdir -p $caCertPath",
                    "chown -R 9999:root $caCertPath",
                    "chmod -R 700 $caCertPath",
                    "rm -rf $caCertPath/kaify-ca.crt",
                    "echo '{$caCert->ssl_certificate}' > $caCertPath/kaify-ca.crt",
                    "chmod 644 $caCertPath/kaify-ca.crt",
                ]);

                remote_process($commands, $server);
            }
        });
    }
}
