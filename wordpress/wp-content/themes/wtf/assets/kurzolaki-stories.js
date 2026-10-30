// Współrzędne odnoszą się do pełnych ilustracji, niezależnie od kadru miniatur.
(() => {
  const targets = {
    szczypawka: [[.55, .10], [.29, .56]],
    lajnosnur: [[.58, .15], [.30, .54]],
    galganek: [[.43, .46], [.61, .51]],
    mieczak: [[.36, .43], null],
    trutka: [null, [.82, .83]],
    soczek: [null, null],
    mazia: [null, [.77, .84]],
    kichacz: [[.69, .47], null],
    glodomor: [null, [.51, .25]],
    guma: [[.16, .25], null],
    plesniak: [[.76, .73], null],
    plujka: [[.48, .81], null],
    utykacz: [[.20, .44], null],
    farfocel: [null, null],
    kudlacz: [[.49, .14], null],
    mol: [null, [.84, .78]],
    puca: [null, null],
    landrynka: [null, [.63, .28]],
  };
  const ns = 'http://www.w3.org/2000/svg';
  const node = (tag, attrs) => {
    const el = document.createElementNS(ns, tag);
    Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, String(value)));
    return el;
  };
  document.querySelectorAll('.dust-stories .story-art').forEach(figure => {
    const chapter = figure.closest('.story-chapter');
    const points = targets[chapter?.id];
    const img = figure.querySelector('.story-illustration .story-character');
    const notes = [...figure.querySelectorAll(':scope > .story-aside')];
    if (!points || !img || notes.length !== 2 || !points.some(Boolean)) return;
    const svg = node('svg', {class: 'story-callout-lines', 'aria-hidden': 'true', focusable: 'false'});
    figure.append(svg);
    const markerId = `story-arrow-${chapter.id}`;
    let pending = false;
    const draw = () => {
      pending = false;
      if (!img.naturalWidth || !img.naturalHeight) return;
      const box = figure.getBoundingClientRect();
      const rect = img.getBoundingClientRect();
      const scale = Math.min(rect.width / img.naturalWidth, rect.height / img.naturalHeight);
      const width = img.naturalWidth * scale, height = img.naturalHeight * scale;
      const left = rect.left - box.left + (rect.width - width) / 2;
      const top = rect.top - box.top + (rect.height - height) / 2;
      svg.setAttribute('viewBox', `0 0 ${box.width} ${box.height}`);
      const defs = node('defs', {});
      const marker = node('marker', {id: markerId, viewBox: '0 0 10 10', refX: 8.5, refY: 5, markerWidth: 8, markerHeight: 8, orient: 'auto', markerUnits: 'userSpaceOnUse'});
      marker.append(node('path', {d: 'M 1 1 L 9 5 L 1 9', fill: 'none', stroke: '#555d49', 'stroke-width': 1.8, 'stroke-linecap': 'round', 'stroke-linejoin': 'round'}));
      defs.append(marker);
      svg.replaceChildren(defs);
      points.forEach((point, index) => {
        if (!point) return;
        const label = notes[index].getBoundingClientRect();
        const sx = label.left - box.left + label.width / 2;
        const sy = label.bottom - box.top + 4;
        const tx = left + point[0] * width, ty = top + point[1] * height;
        // Prowadzenie po stronie dymka ogranicza linie przecinające twarz.
        const side = sx < box.width / 2 ? -1 : 1;
        const longArrow = point[1] > .35;
        const lane = side < 0 ? Math.max(8, left + width * .025) : Math.min(box.width - 8, left + width * .975);
        const cx = longArrow ? lane : Math.max(6, Math.min(box.width - 6, tx + side * Math.min(70, box.width * .14)));
        const d = `M ${sx} ${sy} C ${longArrow ? lane : sx} ${sy + (ty - sy) * .55}, ${cx} ${ty}, ${tx} ${ty}`;
        svg.append(node('path', {d, class: 'story-callout-halo', fill: 'none', stroke: '#fffaf0', 'stroke-width': 4.5, opacity: .8}));
        svg.append(node('path', {d, class: 'story-callout-path', fill: 'none', stroke: '#555d49', 'stroke-width': 1.7, 'stroke-linecap': 'round', 'marker-end': `url(#${markerId})`, 'data-note': index, 'data-target-x': point[0], 'data-target-y': point[1]}));
      });
    };
    const schedule = () => { if (!pending) { pending = true; requestAnimationFrame(draw); } };
    const observer = new ResizeObserver(schedule);
    observer.observe(figure);
    observer.observe(img);
    notes.forEach(note => observer.observe(note));
    img.addEventListener('load', schedule);
    document.fonts.ready.then(schedule);
    schedule();
  });
})();
