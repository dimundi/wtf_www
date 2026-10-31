(function () {
  function init() {
    const publisher = document.getElementById('wtf-social-publisher');
    function updateActive() {
      if (!publisher) return;
      const temporary = Number(publisher.querySelector('[name="wtf_social_temporary_image"]').value) > 0;
      const enabled = document.getElementById('wtf-social-use-temporary').checked;
      document.getElementById('wtf-social-active').textContent = enabled && temporary ? 'Po zapisaniu: grafika tymczasowa.' : enabled ? 'Brak grafiki tymczasowej — używana będzie stała grafika.' : 'Po zapisaniu: stała grafika wydawnictwa.';
    }
    if (publisher) publisher.querySelector('#wtf-social-use-temporary').addEventListener('change', updateActive);
    document.querySelectorAll('[data-social-image-picker]').forEach(function (defaults) {
      defaults.querySelector('[data-select]').addEventListener('click', function () {
        const frame = wp.media({ title: 'Domyślna grafika społecznościowa', library: { type: ['image/jpeg', 'image/png', 'image/webp'] }, multiple: false });
        frame.on('select', function () {
          const attachment = frame.state().get('selection').first().toJSON();
          defaults.querySelector('input').value = attachment.id;
          defaults.querySelector('img').src = attachment.url;
          defaults.querySelector('img').hidden = false;
          updateActive();
        });
        frame.open();
      });
      defaults.querySelector('[data-remove]').addEventListener('click', function () {
        defaults.querySelector('input').value = '';
        defaults.querySelector('img').hidden = true;
        updateActive();
      });
    });
    updateActive();
    const root = document.getElementById('wtf-social-editor');
    if (!root || root.dataset.ready) return;
    root.dataset.ready = '1';
    const fallback = JSON.parse(root.dataset.fallback);
    const title = document.getElementById('wtf-social-title');
    const description = document.getElementById('wtf-social-description');
    const imageId = document.getElementById('wtf-social-image');
    const image = document.getElementById('wtf-social-preview-image');
    function refresh() {
      document.getElementById('wtf-social-preview-title').textContent = title.value.trim() || fallback.title;
      document.getElementById('wtf-social-preview-description').textContent = description.value.trim() || fallback.description;
    }
    title.addEventListener('input', refresh);
    description.addEventListener('input', refresh);
    document.getElementById('wtf-social-select').addEventListener('click', function () {
      const frame = wp.media({ title: 'Grafika podglądu linku', library: { type: ['image/jpeg', 'image/png', 'image/webp'] }, multiple: false });
      frame.on('select', function () {
        const attachment = frame.state().get('selection').first().toJSON();
        imageId.value = attachment.id;
        image.src = attachment.url;
        image.hidden = false;
      });
      frame.open();
    });
    document.getElementById('wtf-social-remove').addEventListener('click', function () {
      imageId.value = '';
      image.src = fallback.image;
      image.hidden = !fallback.image;
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
