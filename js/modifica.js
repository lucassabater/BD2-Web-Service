function validateForms() {
    var ciutat = document.getElementById('ciutat').value;
    var cp = document.getElementById('cp').value;
    var pais = document.getElementById('pais').value;
    var email = document.getElementById('email').value;
    var tlf = document.getElementById('tlf').value;
  
    // Evita el envío del formulario si no están todos los campos completos
    if ((ciutat || cp || pais) && (!ciutat || !cp || !pais)) {
      alert("Si deseas modificar Ciudad, Código Postal o País, debes rellenar los tres campos.");
      return false;
    }
  
  
    // Validar formato del correo electrónico
    var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (email !== "" && !emailRegex.test(email)) {
      alert("El correu electrònic no té un format vàlid.");
      console.log("Hola, Mundo!");
      return false;
    }
  
    // Validar formato del número de teléfono (por ejemplo, para España: 9 dígitos y que empiece con un número entre 6 y 9)
    var phoneRegex = /^[6-9]\d{8}$/;
    if (tlf !== "" && !phoneRegex.test(tlf)) {
      alert("El número de telèfon no té un format vàlid. Ha de ser un número espanyol de 9 dígits.");
      return false;
    }
  
  
    // Si el formulario es válido, lo enviamos
    return true;
  }