// Función para obtener los parámetros de la URL
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  // Esperamos que la página cargue para llenar los datos del producto
  window.onload = function() {
    const producto = getUrlParameter('producto') || 'Producto desconocido';
    const precio = getUrlParameter('precio') || '0.00';
    const imagen = getUrlParameter('imagen') || 'default-product.jpg';

    // Establecemos los valores dinámicamente
    document.getElementById('product-name').innerText = producto;
    document.getElementById('product-price').innerText = `Precio: $${precio}`;
    document.getElementById('product-image').src = 'img/productes/' + imagen;
  }