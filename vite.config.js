import { defineConfig } from 'vite';
import { existsSync, readdirSync } from 'node:fs';
import path from 'node:path';

const themeRoot = import.meta.dirname;
const blocksRoot = path.resolve(themeRoot, 'blocks');
const blockStyles = Object.fromEntries(
  readdirSync(blocksRoot, { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .filter((entry) => existsSync(path.join(blocksRoot, entry.name, 'style.scss')))
    .map((entry) => [
      `block-${entry.name}`,
      path.join(blocksRoot, entry.name, 'style.scss'),
    ]),
);

export default defineConfig({
  base: './',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        app: path.resolve(themeRoot, 'src/js/app.js'),
        editor: path.resolve(themeRoot, 'src/styles/editor.scss'),
        ...blockStyles,
      },
    },
  },
  server: {
    cors: true,
    strictPort: true,
    port: 5173,
  },
});
