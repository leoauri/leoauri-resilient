var switchOn = document.getElementById('nav-on-switch');
var switchOff = document.getElementById('nav-off-switch');
var navElement = document.getElementById('nav-sidebar');

switchOn.addEventListener('click', function(event) {
  event.preventDefault();
  navElement.classList.add('shown');
});

switchOff.addEventListener('click', function(event) {
  event.preventDefault();
  navElement.classList.remove('shown');
});
