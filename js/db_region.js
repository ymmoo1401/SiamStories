/* SIAM STORIES: read-only frontend integration. No edits to existing styling. */
(() => {
  'use strict';
  const region = ({ 'isan.html': 'isan', 'central.html': 'central',
    'east.html': 'north', 'south.html': 'south' })[
      location.pathname.split('/').pop().toLowerCase()
    ];
  const legacyScript = 'js/styleIsan.js?v=2';
  const loadLegacy = () => {
    if (window.initTraditionSliders) {
      window.initTraditionSliders();
      return;
    }
    const s = document.createElement('script');
    s.src = legacyScript;
    s.onerror = () => console.error('Cannot load', legacyScript);
    document.body.appendChild(s);
  };
  if (!region) { loadLegacy(); return; }

  const el = (tag, cls, text) => {
    const node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text != null) node.textContent = String(text);
    return node;
  };
  const imagePath = path => {
    const p = String(path || '').trim().replace(/^\/+/, '');
    // Require local asset paths; don't allow javascript: or external URLs from DB.
    return /^(Img\/|uploads\/)/i.test(p) && !p.includes('..') ? p : '';
  };
  const getList = async url => {
    const response = await fetch(url, {cache: 'no-store', credentials: 'same-origin'});
    if (!response.ok) throw new Error('HTTP ' + response.status);
    const payload = await response.json();
    if (!payload.success || !Array.isArray(payload.data)) throw new Error('Invalid API response');
    return payload.data;
  };
  const renderTraditions = records => {
    const track = document.querySelector('#tradition-track');
    if (!track) return;
    const fragment = document.createDocumentFragment();
    records.forEach(item => {
      const card = el('article', 'card');
      const photo = el('div', 'card-img');
      const img = imagePath(item.image);
      if (img) photo.style.backgroundImage = `url(${JSON.stringify(img)})`;
      const body = el('div', 'card-body');
      body.append(el('h3', '', item.name), el('p', '', item.detail));
      card.append(photo, body);
      fragment.appendChild(card);
    });
    track.replaceChildren(fragment);
  };
  const renderFoods = records => {
    const deck = document.querySelector('#food-deck');
    const menu = document.querySelector('#food .food-menu');
    const counter = document.querySelector('#food .food-counter');
    const status = document.querySelector('#food [data-food-status]');
    if (!deck || !menu || !counter || !status) return;
    const slides = document.createDocumentFragment();
    const buttons = document.createDocumentFragment();
    const regionTitle = {north:'NORTH',isan:'ISAN',central:'CENTRAL',south:'SOUTH'}[region];
    records.forEach((item, index) => {
      const slide = el('article', 'food-slide');
      slide.id = `food-slide-${index}`;
      slide.setAttribute('role','group');
      slide.setAttribute('aria-roledescription','slide');
      slide.setAttribute('aria-label',`${index + 1} จาก ${records.length}: ${item.name}`);
      slide.hidden = index !== 0;
      slide.inert = index !== 0;
      const figure = el('figure','food-photo');
      const img = el('img');
      img.src = imagePath(item.image) || 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
      img.alt = String(item.name || '');
      img.decoding = 'async';
      figure.append(img, el('figcaption','',`${regionTitle} SIGNATURE · ${String(index+1).padStart(2,'0')}`));
      const copy = el('div','food-copy');
      copy.append(el('span','food-eyebrow',`${regionTitle} / ${String(index+1).padStart(2,'0')}`), el('h3','',item.name), el('p','',item.detail));
      const tags = el('div','food-tags');
      const parts = Array.isArray(item.tags_list) ? item.tags_list : String(item.tags || '').split(',');
      parts.map(t=>String(t).trim()).filter(Boolean).forEach(t=>tags.append(el('span','',t)));
      copy.append(tags);
      slide.append(figure,copy);
      slides.append(slide);
      const button = el('button','food-choice',item.name);
      button.type='button';
      button.dataset.foodIndex=String(index);
      button.setAttribute('aria-controls', slide.id);
      button.setAttribute('aria-pressed', String(index === 0));
      buttons.append(button);
    });
    deck.replaceChildren(slides);
    menu.replaceChildren(buttons);
    counter.textContent = `01 / ${String(records.length).padStart(2,'0')}`;
    status.textContent = `${records[0].name} เมนูที่ 1 จาก ${records.length}`;
  };
  (async () => {
    try {
      const [traditions, foods] = await Promise.all([
        getList(`api/traditions.php?region=${encodeURIComponent(region)}`),
        getList(`api/foods.php?region=${encodeURIComponent(region)}`)
      ]);
      // An empty API list is authoritative: deleted records must not reappear.
      // However food carousel requires at least one slide; if empty, keep legacy markup.
      renderTraditions(traditions);
      if (foods.length) renderFoods(foods);
      else console.warn('No foods in DB for region',region);
    } catch (error) {
      console.error('Could not load database content; keeping original HTML.', error);
    } finally {
      loadLegacy();
    }
  })();
})();
