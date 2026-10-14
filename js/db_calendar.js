/* SIAM STORIES: update the existing calendar array from MySQL API. */
(() => {
  'use strict';
  (async () => {
    try {
      const response = await fetch('api/traditions.php', {cache:'no-store',credentials:'same-origin'});
      if (!response.ok) throw new Error('HTTP '+response.status);
      const payload = await response.json();
      if (!payload.success || !Array.isArray(payload.data)) throw new Error('Invalid API response');
      const list = payload.data.map(item => ({
        region: item.region,
        name: String(item.name || ''),
        province: String(item.province || ''),
        months: Array.isArray(item.months) ? item.months.map(Number).filter(m => m>=1 && m<=12) : [],
        image: String(item.image || ''),
        detail: String(item.detail || ''),
        emoji: '🌸'
      }));
      // traditions and render() are defined by existing calender_script.js
      traditions.splice(0, traditions.length, ...list);
      render();
    } catch (error) {
      console.error('Calendar API failed; original events retained.',error);
    }
  })();
})();
