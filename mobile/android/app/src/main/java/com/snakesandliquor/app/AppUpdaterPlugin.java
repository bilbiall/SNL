package com.snakesandliquor.app;

import android.content.Intent;
import android.net.Uri;
import android.os.Build;
import android.provider.Settings;

import androidx.core.content.FileProvider;

import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.MessageDigest;

/**
 * In-app updater: downloads the new APK inside the app (no browser), checks its SHA-256, then hands it to Android's
 * installer. Android never lets a normal app install silently — the person taps "Install" once, and "Update" keeps their data.
 */
@CapacitorPlugin(name = "AppUpdater")
public class AppUpdaterPlugin extends Plugin {
    private volatile boolean busy = false;

    @PluginMethod
    public void install(final PluginCall call) {
        final String url = call.getString("url");
        final String sha = call.getString("sha256", "");
        if (url == null || !url.startsWith("https://")) { call.reject("Bad update address"); return; }
        if (busy) { call.reject("Already downloading"); return; }
        // Android asks once for "install unknown apps" for this app — send people to that switch first
        if (Build.VERSION.SDK_INT >= 26 && !getContext().getPackageManager().canRequestPackageInstalls()) {
            Intent s = new Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES, Uri.parse("package:" + getContext().getPackageName()));
            s.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            getContext().startActivity(s);
            JSObject r = new JSObject(); r.put("needsPermission", true); call.resolve(r);
            return;
        }
        busy = true;
        new Thread(() -> {
            try {
                File dir = new File(getContext().getCacheDir(), "updates");
                if (!dir.exists()) dir.mkdirs();
                File[] old = dir.listFiles(); if (old != null) for (File f : old) f.delete();
                File out = new File(dir, "update.apk");
                HttpURLConnection c = (HttpURLConnection) new URL(url).openConnection();
                c.setConnectTimeout(15000); c.setReadTimeout(30000); c.setInstanceFollowRedirects(true);
                if (c.getResponseCode() != 200) throw new Exception("Server answered " + c.getResponseCode());
                long total = c.getContentLengthLong(), got = 0; int lastPct = -1;
                MessageDigest md = MessageDigest.getInstance("SHA-256");
                try (InputStream in = c.getInputStream(); FileOutputStream fo = new FileOutputStream(out)) {
                    byte[] buf = new byte[32768]; int n;
                    while ((n = in.read(buf)) > 0) {
                        fo.write(buf, 0, n); md.update(buf, 0, n); got += n;
                        int pct = total > 0 ? (int) (got * 100 / total) : -1;
                        if (pct != lastPct) { lastPct = pct; JSObject p = new JSObject(); p.put("percent", pct); notifyListeners("progress", p); }
                    }
                }
                if (total > 0 && got != total) throw new Exception("The download was cut short — try again");
                StringBuilder hex = new StringBuilder(); for (byte b : md.digest()) hex.append(String.format("%02x", b));
                if (sha != null && sha.length() > 0 && !hex.toString().equalsIgnoreCase(sha)) throw new Exception("The file on the server is damaged (checksum mismatch)");
                Uri uri = FileProvider.getUriForFile(getContext(), getContext().getPackageName() + ".fileprovider", out);
                Intent i = new Intent(Intent.ACTION_VIEW);
                i.setDataAndType(uri, "application/vnd.android.package-archive");
                i.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION | Intent.FLAG_ACTIVITY_NEW_TASK);
                getContext().startActivity(i);
                busy = false;
                JSObject r = new JSObject(); r.put("started", true); call.resolve(r);
            } catch (Exception e) {
                busy = false;
                call.reject(e.getMessage() == null ? "Download failed" : e.getMessage());
            }
        }).start();
    }
}
