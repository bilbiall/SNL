const { app, BrowserWindow, protocol, net, shell, Menu } = require('electron');
const path = require('path');
const { pathToFileURL } = require('url');

const WWW = path.join(__dirname, 'www');
const HOME = 'app://snl/index.html';

protocol.registerSchemesAsPrivileged([
  { scheme: 'app', privileges: { standard: true, secure: true, supportFetchAPI: true, corsEnabled: true, stream: true } },
]);

if (!app.requestSingleInstanceLock()) { app.quit(); }

let win = null;

function createWindow() {
  win = new BrowserWindow({
    width: 1280, height: 800, minWidth: 900, minHeight: 600,
    backgroundColor: '#0b0a16',
    title: 'Snakes & Liquor',
    icon: path.join(__dirname, 'build', 'icon.png'),
    autoHideMenuBar: true,
    webPreferences: { preload: path.join(__dirname, 'preload.js'), contextIsolation: true, nodeIntegration: false, sandbox: true, additionalArguments: ['--snl-version=' + require('./app-version.json').versionName + '|' + require('./app-version.json').versionCode], autoplayPolicy: 'no-user-gesture-required' },
  });
  Menu.setApplicationMenu(null);
  win.loadURL(HOME);
  win.on('app-command', (e, cmd) => { if (cmd === 'browser-backward' && win.webContents.canGoBack()) win.webContents.goBack(); });
  // anything that isn't the game itself opens in the normal browser
  win.webContents.setWindowOpenHandler(({ url }) => { if (/^https?:/.test(url)) shell.openExternal(url); return { action: 'deny' }; });
  win.webContents.on('will-navigate', (e, url) => { if (!url.startsWith('app://snl/') && !/^https:\/\/(www\.)?snakesandliquor\.co\.ke\//.test(url)) { e.preventDefault(); if (/^https?:/.test(url)) shell.openExternal(url); } });
  win.webContents.on('before-input-event', (e, input) => {
    if (input.type !== 'keyDown') return;
    if (input.alt && input.key === 'ArrowLeft' && win.webContents.canGoBack()) win.webContents.goBack();
    if (input.key === 'F11') win.setFullScreen(!win.isFullScreen());
    if (input.key === 'Escape' && win.isFullScreen()) win.setFullScreen(false);
  });
}

app.whenReady().then(() => {
  protocol.handle('app', (req) => {
    let p = decodeURIComponent(new URL(req.url).pathname);
    if (p === '/' || p === '') p = '/index.html';
    const file = path.normalize(path.join(WWW, p));
    if (!file.startsWith(WWW)) return new Response('Forbidden', { status: 403 });
    return net.fetch(pathToFileURL(file).toString());
  });
  createWindow();
  app.on('activate', () => { if (!BrowserWindow.getAllWindows().length) createWindow(); });
});

app.on('second-instance', () => { if (win) { if (win.isMinimized()) win.restore(); win.focus(); } });
app.on('window-all-closed', () => app.quit());
