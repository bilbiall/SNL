<?php

namespace App\Http\Controllers;

/**
 * The Android app download: a proper /app page (what it is, how to install it) and a direct file route that
 * always sends the APK with the right type + filename, whatever the web host's MIME settings are.
 */
class AppDownloadController extends Controller
{
    private function info(): ?array
    {
        $apk = public_path('downloads/snakes-and-liquor.apk');
        $meta = public_path('downloads/version.json');
        if (!is_file($apk)) {
            return null;
        }
        $v = is_file($meta) ? (json_decode(file_get_contents($meta), true) ?: []) : [];

        return [
            'versionName' => $v['versionName'] ?? '1.0.0',
            'sizeBytes'   => filesize($apk),
            'sha256'      => $v['sha256'] ?? hash_file('sha256', $apk),
            'builtAt'     => $v['builtAt'] ?? date('c', filemtime($apk)),
        ];
    }

    public function page()
    {
        return view('app', ['apk' => $this->info()]);
    }

    public function android()
    {
        $file = public_path('downloads/snakes-and-liquor.apk');
        abort_unless(is_file($file), 404, 'The Android app is not available yet.');

        return response()->download($file, 'Snakes-and-Liquor.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
