(function(){
  const form = document.querySelector('form#module-finder');
  if( !form ) return;

  const blocks = form.querySelector('.mill3-wp-utils-plugin__moduleFinder__blocks select');
  if( !blocks ) return;

  let submitted = false;
  
  // automatically submit form when select value change
  blocks.addEventListener('change', function(event) {
    const value = event.currentTarget.value;
    if( !value || submitted ) return;

    submitted = true;
    form.submit();
  });

})();
