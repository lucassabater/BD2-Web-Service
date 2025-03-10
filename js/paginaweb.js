// Selección de elementos
const menuButton = document.getElementById('menuButton');
const menu = document.getElementById('menu');

// Función para mostrar/ocultar el menú
menuButton.addEventListener('click', () => {
  if (menu.style.display === 'none' || menu.style.display === '') {
    menu.style.display = 'block';
  } else {
    menu.style.display = 'none';
  }
});
