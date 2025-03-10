// Verificar si el parámetro "mode" está en la URL
function checkMode() {
  var urlParams = new URLSearchParams(window.location.search);
  var mode = urlParams.get('mode');

  // Si el mode es "personal", ocultar los campos de la empresa
  if (mode === 'personal') {
    document.getElementById('showCompanyFields').style.display = 'none';  // Ocultar el botón
    document.getElementById('companyFields').style.display = 'none';      // Ocultar los campos
  }
}

// Función para validar los campos del formulario
function validateForm() {
  var email = document.getElementById('email').value;
  var tlf = document.getElementById('tlf').value;

  // Validar formato del correo electrónico
  var formatCorreu = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!formatCorreu.test(email)) {
    alert("El correu electrònic no té un format vàlid.");
    return false;
  }

  // Validar formato del número de teléfono
  var formatTelf = /^\d{9}$/;
  if (!formatTelf.test(tlf)) {
    alert("El número de telèfon no té un format vàlid.");
    return false;
  }

  // Obtener el contenedor de los campos de la empresa
  var companyFields = document.getElementById('companyFields');
  
  // Validar solo si los campos de empresa están visibles
  if (companyFields.style.display === "block") {
    var empNom = document.getElementById('empNom').value;
    var cif = document.getElementById('cif').value;

    // Si los campos de la empresa están visibles, ambos deben estar completos
    if (!empNom || !cif) {
      alert("Has de completar els camps de l'empresa.");
      return false;
    }
  }

  // Si el formulario es válido, lo enviamos
  return true;
}

// Función para mostrar u ocultar los campos de la empresa
function toggleCompanyFields() {
  var companyFields = document.getElementById('companyFields');
  var empNom = document.getElementById('empNom');
  var cif = document.getElementById('cif');
  
  // Si los campos están visibles, los ocultamos y borramos sus valores
  if (companyFields.style.display === 'none') {
    companyFields.style.display = 'block';
  } else {
    companyFields.style.display = 'none';
    // Borrar los valores de los campos de la empresa
    empNom.value = '';
    cif.value = '';
  }
}

// Llamar a la función para verificar el "mode" cuando se cargue la página
checkMode();