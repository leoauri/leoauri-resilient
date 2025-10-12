var switchOn = document.getElementById('nav-on-switch');
var switchOff = document.getElementById('nav-off-switch');
var navElement = document.getElementById('nav-sidebar');


function documentListener(event) {
  // ignore the bubbling of the on-switch click
  if (event.defaultPrevented) return;

  let clickTracer = event.target;

  do {
    if (clickTracer == navElement) {
      return;
    }
    clickTracer = clickTracer.parentNode;
  } while (clickTracer);

  // clicked outside of nav-sidebar
  navElement.classList.remove('shown');
  document.removeEventListener('click', documentListener);
}


switchOn.addEventListener('click', function(event) {
  event.preventDefault();
  navElement.classList.add('shown');

  document.addEventListener('click', documentListener);
});

switchOff.addEventListener('click', function(event) {
  event.preventDefault();
  navElement.classList.remove('shown');
});
