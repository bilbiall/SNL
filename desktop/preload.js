const { contextBridge } = require('electron');

// version is handed over by main.js (a sandboxed preload can't read files)
const arg = (process.argv.find((a) => a.startsWith('--snl-version=')) || '').slice(14).split('|');

// Marks the page as the desktop app (app-update.js, loader etc. look for window.SNLDesktop).
contextBridge.exposeInMainWorld('SNLDesktop', {
  versionName: arg[0] || '1.0.0',
  versionCode: parseInt(arg[1], 10) || 1,
  site: 'https://snl.moski.org',
  platform: process.platform,
});
