const form = document.querySelector('form');
form.addEventListener('submit', (event) => {
  event.preventDefault(); // prevenir la acción por defecto del formulario

  const password = document.querySelector('#password').value;
  if (password === '12345678') {
    // Si la contraseña es correcta, redirigir al usuario a la página de inicio de sesión
    window.location.href = 'inicio.html';
  } else {
    // Si la contraseña es incorrecta, mostrar un mensaje de error
    const errorMessage = document.createElement('p');
    errorMessage.textContent = 'Contraseña incorrecta. Por favor, inténtalo de nuevo.';
    form.appendChild(errorMessage);
  }
});
