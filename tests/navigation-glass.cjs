'use strict';

// Execute the shipped navigation script, including its real RAF docking loop.
// The minimal DOM deliberately omits the unrelated project picker and font logo.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const filename = path.resolve(__dirname, '../wp-content/plugins/tbs-pig-laboratory/dist/items-menu.js');
const source = fs.readFileSync(filename, 'utf8');

function fixture({ context = 'home', reducedMotion = false, hasCorners = true, hasFooter = true, initialScroll = 0 } = {}) {
  const globalEvents = new Map();
  const mediaEvents = new Map();
  const frames = new Map();
  let frameId = 0;
  let now = 0;
  let footerTop = 2000;

  function node(className = '', tagName = 'DIV') {
    const element = {
      className, tagName, dataset: {}, children: [], parentElement: null,
      style: {
        setProperty(name, value) { this[name] = value; },
        getPropertyValue(name) { return this[name] || ''; },
        removeProperty(name) { delete this[name]; },
      },
      append(...children) {
        for (const child of children) {
          if (child.parentElement) child.parentElement.children = child.parentElement.children.filter(item => item !== child);
          child.parentElement = this;
          this.children.push(child);
        }
      },
      querySelector(selector) { return this.querySelectorAll(selector)[0] || null; },
      querySelectorAll(selector) {
        return descendants(this).filter(item => selector.split(',').some(part => matches(item, part.trim())));
      },
      getBoundingClientRect() { return { top: 0, bottom: 0, right: 0, left: 0 }; },
    };
    return element;
  }
  function descendants(element) { return element.children.flatMap(child => [child, ...descendants(child)]); }
  function matches(element, selector) {
    if (selector.startsWith('.') && !selector.includes(' ')) return element.className.split(/\s+/).includes(selector.slice(1));
    return element.tagName.toLowerCase() === selector.toLowerCase();
  }
  function listen(map, type, callback) {
    if (!map.has(type)) map.set(type, []);
    map.get(type).push(callback);
  }
  function emit(map, type) { (map.get(type) || []).forEach(callback => callback({ type })); }

  const body = node('', 'BODY');
  const wrapper = node('wp-site-blocks');
  const header = node('tbs-site-header');
  const top = node('tbs-edge-glass tbs-edge-glass--top');
  const corners = hasCorners ? node('tbs-site-corners') : null;
  const bottom = hasCorners ? node('tbs-edge-glass tbs-edge-glass--bottom') : null;
  const footer = hasFooter ? node('tbs-site-footer', 'FOOTER') : null;
  body.append(wrapper);
  wrapper.append(header, top);
  if (corners) {
    corners.dataset.tbsContext = context;
    wrapper.append(bottom, corners);
  }
  if (footer) wrapper.append(footer);
  const media = {
    matches: reducedMotion,
    addEventListener(type, callback) { listen(mediaEvents, type, callback); },
  };
  const document = {
    body, documentElement: { dataset: {} }, readyState: 'complete', hidden: false,
    querySelectorAll: selector => body.querySelectorAll(selector),
    querySelector: selector => body.querySelector(selector),
    getElementById: id => id === 'footer' ? footer : null,
    addEventListener() {},
  };
  const sandbox = {
    document, innerWidth: 1440, innerHeight: 1000, scrollY: initialScroll, devicePixelRatio: 1,
    location: { href: 'https://example.test/', pathname: '/' }, URL,
    performance: { now: () => now },
    matchMedia: () => media,
    getComputedStyle: () => ({ bottom: '12px' }),
    addEventListener: (type, callback) => listen(globalEvents, type, callback),
    requestAnimationFrame: callback => { const id = ++frameId; frames.set(id, callback); return id; },
    cancelAnimationFrame: id => frames.delete(id),
    setTimeout, clearTimeout,
  };
  sandbox.window = sandbox;
  if (footer) footer.getBoundingClientRect = () => ({ top: footerTop - sandbox.scrollY });
  vm.runInNewContext(source, sandbox, { filename });

  function synchronized() {
    assert.equal(top.style.transform, undefined, 'upper glass stays fixed while lower controls dock');
    assert.equal(top.style.visibility, undefined, 'upper glass remains present at footer');
    if (!corners) return;
    assert.equal(bottom.style.transform, corners.style.transform, 'glass follows the exact control offset every frame');
    assert.equal(bottom.style.visibility, corners.style.visibility, 'glass cannot outlive the controls');
    assert.equal(bottom.style.getPropertyValue('--tbs-glass-strength'), corners.style.opacity, 'glass and controls fade together');
    assert.equal(bottom.style.opacity, undefined, 'do not create a backdrop root by fading the common wrapper');
  }
  function tick() {
    const batch = [...frames.values()];
    frames.clear();
    now += 1000 / 60;
    batch.forEach(callback => callback(now));
    synchronized();
  }
  function settle() {
    let ticks = 0;
    while (frames.size && ticks++ < 200) tick();
    assert.equal(frames.size, 0, 'docking animation settles instead of running forever');
    synchronized();
  }
  function scroll(value) { sandbox.scrollY = value; emit(globalEvents, 'scroll'); }
  function offset() { return Number(corners.style.transform.match(/translateY\(([-\d.]+)px\)/)[1]); }
  return {
    body, wrapper, header, top, corners, bottom, sandbox, media, tick, settle, scroll, offset,
    frameCount: () => frames.size,
    reduce(value) { media.matches = value; emit(mediaEvents, 'change'); },
    resize(height, newFooterTop = footerTop) { sandbox.innerHeight = height; footerTop = newFooterTop; emit(globalEvents, 'resize'); },
  };
}

const results = [];
function check(name, callback) { callback(); results.push(name); }

check('home: initial glass and controls hoisted out of FSE wrappers', () => {
  const page = fixture();
  page.settle();
  for (const element of [page.header, page.top, page.bottom, page.corners]) assert.equal(element.parentElement, page.body);
  assert.equal(page.offset(), 0);
  assert.equal(page.bottom.style.visibility, 'visible');
  assert.equal(page.bottom.style.getPropertyValue('--tbs-glass-strength'), '1');
});

check('home: footer approach, fade, exit and reverse remain synchronized on every RAF', () => {
  const page = fixture();
  page.settle();
  page.scroll(1100);
  page.tick();
  assert.ok(page.offset() < 0 && page.offset() > -96, 'home uses its existing eased docking');
  page.settle();
  assert.equal(page.offset(), -96);
  page.scroll(1850);
  page.settle();
  assert.ok(Math.abs(Number(page.corners.style.opacity) - 0.84) < 1e-10);
  assert.equal(page.bottom.style.visibility, 'visible');
  page.scroll(1900);
  page.settle();
  assert.equal(page.bottom.style.visibility, 'hidden');
  assert.equal(page.bottom.style.getPropertyValue('--tbs-glass-strength'), '0');
  page.scroll(1500);
  page.tick();
  page.settle();
  assert.equal(page.bottom.style.visibility, 'visible');
  assert.equal(page.offset(), -496);
  page.scroll(0);
  page.settle();
  assert.equal(page.offset(), 0);
  assert.equal(page.bottom.style.getPropertyValue('--tbs-glass-strength'), '1');
});

check('reduced motion: direct docking and immediate runtime preference response', () => {
  const reduced = fixture({ reducedMotion: true });
  reduced.settle();
  reduced.scroll(1100);
  reduced.tick();
  assert.equal(reduced.offset(), -96);
  assert.equal(reduced.frameCount(), 0);
  const changing = fixture();
  changing.settle();
  changing.scroll(1100);
  changing.tick();
  assert.notEqual(changing.offset(), -96);
  changing.reduce(true);
  changing.tick();
  assert.equal(changing.offset(), -96);
  assert.equal(changing.frameCount(), 0);
});

check('large scroll jumps and restored deep scroll hide lower glass immediately', () => {
  const page = fixture();
  page.settle();
  page.scroll(2200);
  page.tick();
  assert.equal(page.bottom.style.visibility, 'hidden');
  assert.equal(page.frameCount(), 0);
  const restored = fixture({ initialScroll: 2200 });
  restored.settle();
  assert.equal(restored.bottom.style.visibility, 'hidden');
  assert.equal(restored.bottom.style.getPropertyValue('--tbs-glass-strength'), '0');
});

check('inner layouts and missing footer do not acquire orphan lower glass', () => {
  const inner = fixture({ context: 'inner', hasCorners: false });
  inner.settle();
  inner.scroll(1900);
  inner.settle();
  assert.equal(inner.bottom, null);
  assert.equal(inner.body.querySelector('.tbs-edge-glass--bottom'), null);
  assert.equal(inner.top.parentElement, inner.body);
  const legacy = fixture({ context: 'inner' });
  legacy.settle();
  legacy.scroll(1100);
  legacy.tick();
  assert.equal(legacy.offset(), -96, 'non-home contexts retain direct positioning');
  const noFooter = fixture({ hasFooter: false });
  noFooter.settle();
  noFooter.scroll(10000);
  noFooter.settle();
  assert.equal(noFooter.offset(), 0);
});

check('viewport and footer remeasurement preserves exact shared placement', () => {
  const page = fixture({ reducedMotion: true });
  page.settle();
  page.scroll(1500);
  page.settle();
  assert.equal(page.offset(), -496);
  page.resize(800, 2100);
  page.settle();
  assert.equal(page.offset(), -196);
  assert.equal(page.bottom.style.visibility, 'visible');
});

console.log(JSON.stringify({ status: 'passed', script: path.relative(process.cwd(), filename), checks: results.length, results }, null, 2));
