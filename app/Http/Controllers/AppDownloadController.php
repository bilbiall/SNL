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

    /** The Windows installer, if one has been uploaded to public/downloads. */
    private function windowsInfo(): ?array
    {
        $meta = public_path('downloads/desktop-version.json');
        $v = is_file($meta) ? (json_decode(file_get_contents($meta), true) ?: []) : [];
        $file = $v['file'] ?? null;
        if (!$file || !preg_match('/^[A-Za-z0-9._-]+\.exe$/', $file) || !is_file(public_path('downloads/' . $file))) {
            return null;
        }

        return ['versionName' => $v['versionName'] ?? '1.0.0', 'file' => $file, 'sizeBytes' => filesize(public_path('downloads/' . $file))];
    }

    public function page()
    {
        return view('app', ['apk' => $this->info(), 'win' => $this->windowsInfo()]);
    }

    public function windows()
    {
        $info = $this->windowsInfo();
        abort_unless($info, 404, 'The Windows app is not available yet.');

        return response()->download(public_path('downloads/' . $info['file']), 'Snakes-and-Liquor-Setup.exe', [
            'Content-Type' => 'application/vnd.microsoft.portable-executable',
            'Cache-Control' => 'no-cache',
        ]);
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
