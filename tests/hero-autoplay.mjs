import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const source = readFileSync(new URL('../blocks/hero/view.js', import.meta.url), 'utf8');
const { default: register } = await import('data:text/javascript,' + encodeURIComponent(source));
let create;
register({ data: (_name, factory) => { create = factory; } });
let callback;
let listener;
let reduced = false;
globalThis.document = { hidden: false };
globalThis.window = {
  matchMedia: () => ({
    matches: reduced,
    addEventListener: (_event, fn) => { listener = fn; },
    removeEventListener: () => { listener = null; },
  }),
  setInterval: (fn, delay) => {
    assert.equal(delay, 6000);
    callback = fn;
    return 1;
  },
  clearInterval: () => { callback = null; },
};

const manual = create(3);
manual.init();
assert.equal(manual.timer, null);
manual.destroy();
const single = create(1, true);
single.init();
assert.equal(single.timer, null);
single.destroy();
const slider = create(3, true);
slider.init();
callback();
assert.equal(slider.active, 1);
slider.go(2);
callback();
assert.equal(slider.active, 0);
for (const flag of ['hovered', 'focused', 'paused']) {
  slider[flag] = true;
  slider.sync();
  assert.equal(slider.timer, null);
  slider[flag] = false;
  slider.sync();
  assert.notEqual(slider.timer, null);
}
document.hidden = true;
slider.sync();
assert.equal(slider.timer, null);
document.hidden = false;
slider.sync();
listener({ matches: true });
assert.equal(slider.timer, null);
listener({ matches: false });
assert.notEqual(slider.timer, null);
slider.destroy();
assert.equal(callback, null);
assert.equal(listener, null);
reduced = true;
const accessible = create(3, true);
accessible.init();
assert.equal(accessible.timer, null);
accessible.destroy();
console.log('Hero autoplay: defaults, timing, wrapping, pauses, reduced motion and cleanup passed.');
