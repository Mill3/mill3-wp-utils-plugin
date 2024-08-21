jQuery( document ).ready( function( $ ) {
  var wrapper = document.querySelector('.user-avatar-wrap');
  if( !wrapper ) return;

  var button = wrapper.querySelector('#avatar');
  if( !button ) return;

  var hidden_field = wrapper.querySelector('#avatar_media_id');
  var image = wrapper.querySelector('.avatar-preview img');
  var remove = wrapper.querySelector('.avatar-remove-btn');

  var uploader;

  function createUploader() {
      uploader = wp.media({
          title: wrapper.getAttribute('data-uploader-title'),
          library: { type: 'image' },
          multiple: false
      });

      uploader.on('select', function() {
          var attachment = uploader.state().get('selection').first().toJSON();

          // update avatar preview
          image.removeAttribute('srcset');
          image.src = attachment.sizes.thumbnail.url;

          // update hidden field
          hidden_field.value = attachment.id;
      });

      uploader.on('open', function() {
          if( hidden_field.value ) {
              var selection = uploader.state().get('selection');
              var attachment = wp.media.attachment(hidden_field.value);
                  attachment.fetch();
              
              selection.add( attachment ? [attachment] : [] );
          }
      });
  }

  button.addEventListener('click', function(event) {
      event.preventDefault();

      if( !uploader ) createUploader();
      uploader.open();
  });

  if( remove ) {
      remove.addEventListener('click', function() {
          hidden_field.value = "";
      });
  }
});
