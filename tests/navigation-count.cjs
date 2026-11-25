'use strict';

// Render the actual PHP, construct its DOM, and run the unchanged browser boot.
// This stub covers the DOM used by navigation; it is not a visual browser test.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { spawnSync } = require('node:child_process');

const taskRoot = path.resolve(__dirname, '..');
const candidateRoot = path.join(taskRoot, 'wp-content/plugins/tbs-pig-laboratory');
const phpSource = path.join(candidateRoot, 'work-header.php');
const jsSource = path.join(candidateRoot, 'dist', 'items-menu.js');
// PHP_CLI may point to a local PHP executable; otherwise use PHP from PATH.
const php = process.env.PHP_CLI || 'php';
const script = fs.readFileSync(jsSource, 'utf8');

function decode(text) {
  return text.replace(/&(?:amp|lt|gt|quot|apos|#\d+|#x[\da-f]+);/gi, entity => {
    const named = { '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&apos;': "'" };
    if (named[entity]) return named[entity];
    return String.fromCodePoint(entity[2].toLowerCase() === 'x' ? parseInt(entity.slice(3, -1), 16) : Number(entity.slice(2, -1)));
  });
}

function makeDOM(html) {
  let document;
  class Element {
    constructor(tagName = 'div') {
      this.tagName = tagName.toUpperCase();
      this.attributes = {};
      this.dataset = {};
      this.children = [];
      this.parentElement = null;
      this.events = new Map();
      this.style = {
        setProperty(name, value) { this[name] = value; },
        removeProperty(name) { delete this[name]; },
      };
      this.classList = {
        add: name => { if (!this.className.split(/\s+/).includes(name)) this.className = (this.className + ' ' + name).trim(); },
        toggle: (name, force) => {
          const classes = this.className.split(/\s+/).filter(Boolean);
          const enabled = force === undefined ? !classes.includes(name) : force;
          this.className = classes.filter(value => value !== name).concat(enabled ? name : []).join(' ');
          return enabled;
        },
      };
    }
    get className() { return this.attributes.class || ''; }
    set className(value) { this.attributes.class = value; }
    get id() { return this.attributes.id || ''; }
    set id(value) { this.attributes.id = value; }
    get href() { return this.attributes.href || ''; }
    set href(value) { this.attributes.href = value; }
    get textContent() { return this.children.map(child => typeof child === 'string' ? child : child.textContent).join(''); }
    set textContent(value) { this.children = [String(value)]; }
    set innerHTML(value) { this.children = [value.replace(/<[^>]*>/g, '')]; }
    setAttribute(name, value) {
      this.attributes[name] = String(value);
      if (name.startsWith('data-')) this.dataset[name.slice(5).replace(/-([a-z])/g, (_, letter) => letter.toUpperCase())] = String(value);
    }
    getAttribute(name) { return Object.hasOwn(this.attributes, name) ? this.attributes[name] : null; }
    removeAttribute(name) { delete this.attributes[name]; }
    append(...children) {
      for (const child of children) {
        if (typeof child !== 'string') {
          if (child.parentElement) child.parentElement.children = child.parentElement.children.filter(item => item !== child);
          child.parentElement = this;
        }
        this.children.push(child);
      }
    }
    replaceChildren(...children) {
      this.children.forEach(child => { if (typeof child !== 'string') child.parentElement = null; });
      this.children = [];
      this.append(...children);
    }
    replaceWith(replacement) {
      const parent = this.parentElement;
      parent.children.splice(parent.children.indexOf(this), 1, replacement);
      replacement.parentElement = parent;
      this.parentElement = null;
    }
    descendants() { return this.children.flatMap(child => typeof child === 'string' ? [] : [child, ...child.descendants()]); }
    matches(selector) {
      if (selector === ':hover') return false;
      const parts = selector.split('.');
      if (parts[0] && this.tagName.toLowerCase() !== parts[0].toLowerCase()) return false;
      return parts.slice(1).every(name => this.className.split(/\s+/).includes(name));
    }
    querySelectorAll(selector) {
      return this.descendants().filter(element => selector.split(',').some(item => {
        const parts = item.trim().split(/\s+/);
        if (!element.matches(parts.pop())) return false;
        let ancestor = element.parentElement;
        while (parts.length) {
          const target = parts.pop();
          while (ancestor && !ancestor.matches(target)) ancestor = ancestor.parentElement;
          if (!ancestor) return false;
          ancestor = ancestor.parentElement;
        }
        return true;
      }));
    }
    querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
    contains(other) { return this === other || this.descendants().includes(other); }
    closest(selector) { return this.matches(selector) ? this : this.parentElement?.closest(selector) || null; }
    addEventListener(type, callback) {
      if (!this.events.has(type)) this.events.set(type, []);
      this.events.get(type).push(callback);
    }
    emit(type, values = {}) {
      const event = { target: this, preventDefault() {}, ...values };
      (this.events.get(type) || []).forEach(callback => callback(event));
    }
    focus() { document.activeElement = this; }
    getBoundingClientRect() { return { top: 30, left: 1200, right: 1400, bottom: 60 }; }
  }
  const body = new Element('body');
  const wrapper = new Element();
  wrapper.className = 'wp-site-blocks';
  body.append(wrapper);
  const stack = [wrapper];
  for (const token of html.match(/<[^>]*>|[^<]+/g) || []) {
    if (token.startsWith('</')) { stack.pop(); continue; }
    if (token.startsWith('<')) {
      const tag = token.match(/^<([a-z][\w-]*)/i)?.[1];
      if (!tag) continue;
      const element = new Element(tag);
      for (const match of token.matchAll(/([\w-]+)="([^"]*)"/g)) element.setAttribute(match[1], decode(match[2]));
      stack[stack.length - 1].append(element);
      if (!/^(?:br|img|input|link|meta|hr)$/i.test(tag) && !token.endsWith('/>')) stack.push(element);
    } else stack[stack.length - 1].append(decode(token));
  }
  document = new Element('document');
  document.body = body;
  document.documentElement = new Element('html');
  document.activeElement = body;
  document.hidden = false;
  document.querySelectorAll = selector => body.querySelectorAll(selector);
  document.querySelector = selector => body.querySelector(selector);
  document.getElementById = id => body.descendants().find(element => element.id === id) || null;
  document.createElement = tagName => new Element(tagName);
  return document;
}

function boot(html, context, readyState) {
  const document = makeDOM(html);
  document.readyState = readyState;
  const frames = new Map();
  let nextFrame = 0;
  const sandbox = {
    document, URL,
    location: { href: context === 'home' ? 'https://example.test/' : 'https://example.test/work/project-1/', pathname: context === 'home' ? '/' : '/work/project-1/' },
    innerWidth: 1440, innerHeight: 1000, scrollY: 0, devicePixelRatio: 1,
    performance: { now: () => 0 },
    matchMedia: () => ({ matches: true, addEventListener() {} }),
    getComputedStyle: () => ({ bottom: '12px' }),
    addEventListener() {},
    requestAnimationFrame: callback => { const id = ++nextFrame; frames.set(id, callback); return id; },
    cancelAnimationFrame: id => frames.delete(id),
    setTimeout, clearTimeout,
  };
  sandbox.window = sandbox;
  const runtime = vm.createContext(sandbox);
  vm.runInContext(script, runtime, { filename: jsSource });
  if (readyState === 'loading') document.emit('DOMContentLoaded');
  for (const callback of frames.values()) callback(16.67);
  return { document, runtime };
}

const expectedCounts = new Map([[0, '00'], [1, '01'], [6, '06'], [9, '09'], [10, '10'], [100, '100']]);
const scenarios = [];
let phpRuns = 0;
for (const [count, printedCount] of expectedCounts) {
  for (const context of ['home', 'inner']) {
    const label = count === 9 ? 'SELECCIÓN & PROYECTOS' : 'PROYECTOS';
    const expectedLabel = `${label} [${printedCount}]`;
    const rendered = spawnSync(php, ['-n', path.join(__dirname, 'render-header.php'), phpSource, String(count), context, label], { encoding: 'utf8' });
    assert.equal(rendered.status, 0, `PHP renderer failed (${php}): ${rendered.error?.message || rendered.stderr || rendered.stdout}`);
    assert.equal(rendered.stderr, '', 'PHP emits no warnings');
    phpRuns++;
    const serverDOM = makeDOM(rendered.stdout);
    const serverHeader = serverDOM.querySelector('.tbs-site-header');
    const serverTrigger = serverHeader.querySelector('.items-toggle');
    assert.equal(serverTrigger.textContent.replace(/\s*\+\s*$/, '').trim(), expectedLabel, `PHP count=${count}, context=${context}`);
    assert.equal(serverHeader.dataset.tbsContext, context);
    assert.equal(JSON.parse(serverHeader.dataset.tbsItems).length, count);
    assert.equal(serverTrigger.href, context === 'home' ? '#portfolio' : 'https://example.test/#portfolio');
    assert.equal(serverDOM.querySelectorAll('.tbs-site-corners').length, context === 'home' ? 1 : 0);

    for (const readyState of ['complete', 'loading']) {
      const { document, runtime } = boot(rendered.stdout, context, readyState);
      const trigger = document.querySelector('.items-toggle');
      assert.equal(trigger.textContent, expectedLabel, `JS count=${count}, context=${context}, readyState=${readyState}`);
      assert.equal(document.querySelector('.tbs-site-header').parentElement, document.body, 'header retains structural hoisting');
      if (count === 0) {
        assert.equal(trigger.tagName, 'A', 'empty menu preserves the fallback anchor');
        assert.equal(document.querySelector('.items-menu'), null, 'empty menu does not create a portal');
      } else {
        const menu = document.querySelector('.items-menu');
        const links = menu.querySelectorAll('a');
        assert.equal(trigger.tagName, 'BUTTON');
        assert.equal(links.length, count, 'counter agrees with rendered valid menu entries');
        assert.equal(menu.id, trigger.getAttribute('aria-controls'));
        assert.equal(links[0].href, 'https://example.test/work/project-1/');
        assert.equal(links[count - 1].href, `https://example.test/work/project-${count}/`);
        assert.equal(links[count - 1].querySelector('.items-menu-number').textContent, printedCount, 'counts at 100 are not truncated');
        assert.equal(links[0].getAttribute('aria-current'), context === 'inner' ? 'page' : null);
        assert.equal(trigger.getAttribute('aria-expanded'), 'false');
        trigger.emit('click');
        assert.equal(trigger.getAttribute('aria-expanded'), 'true', 'menu remains interactive after label update');
        assert.equal(menu.inert, false);
        assert.equal(trigger.textContent, expectedLabel, 'opening menu does not change count');
        trigger.emit('keydown', { key: 'ArrowDown' });
        assert.equal(document.activeElement, links[0], 'keyboard focus still enters the menu');
        document.emit('keydown', { key: 'Escape' });
        assert.equal(trigger.getAttribute('aria-expanded'), 'false');
        assert.equal(document.activeElement, trigger);
      }
      vm.runInContext(script, runtime, { filename: jsSource });
      assert.equal(document.querySelectorAll('.items-menu').length, count ? 1 : 0, 'boot stays idempotent');
      assert.equal(trigger.textContent, expectedLabel);
      scenarios.push({ count, context, readyState, label: expectedLabel });
    }
  }
}
console.log(JSON.stringify({ status: 'passed', phpRenders: phpRuns, javascriptBoots: scenarios.length, counts: [...expectedCounts.keys()], contexts: ['home', 'inner'], readyStates: ['complete', 'loading'], includes: ['empty portfolio', 'three-digit count', 'custom escaped label', 'link destinations', 'menu click and keyboard controls', 'idempotent boot'] }, null, 2));

