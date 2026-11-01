jQuery(function ($) {
  $('.wtfb-select').on('click', function () {
    const field = $(this).closest('.wtfb-image-field');
    const frame = wp.media({ title: 'Wybierz grafikę banera', library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      const item = frame.state().get('selection').first().toJSON();
      field.find('input').val(item.id);
      field.find('.wtfb-image-preview').empty().append($('<img>', { src: item.sizes?.medium?.url || item.url, alt: '', style: 'max-width:240px;height:auto' }));
    });
    frame.open();
  });
  $('.wtfb-remove').on('click', function () {
    const field = $(this).closest('.wtfb-image-field');
    field.find('input').val('');
    field.find('.wtfb-image-preview').empty();
  });
});
