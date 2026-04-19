const telefoneInput = document.getElementById("telefone");
const senhaInput = document.getElementById("senha");
const toggleSenha = document.getElementById("toggleSenha");
const loginForm = document.getElementById("loginForm");
const msgTelefone = document.getElementById("error-telefone");
const msgSenha = document.getElementById("error-senha");

function aplicarMascaraTelefone(valor) {
  const numeros = valor.replace(/\D/g, "").slice(0, 11);

  if (numeros.length <= 2) return numeros;
  if (numeros.length <= 7) return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
  return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7, 11)}`;
}

if (telefoneInput) {
  telefoneInput.addEventListener("input", (event) => {
    event.target.value = aplicarMascaraTelefone(event.target.value);
  });

  telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
}

if (toggleSenha && senhaInput) {
  toggleSenha.addEventListener("click", () => {
    const mostrando = senhaInput.type === "text";
    senhaInput.type = mostrando ? "password" : "text";
    toggleSenha.textContent = mostrando ? "Mostrar" : "Ocultar";
  });
}

if (loginForm) {
  loginForm.addEventListener("submit", (event) => {
    const telefone = telefoneInput.value.replace(/\D/g, "");
    const senha = senhaInput.value.trim();
    if (!telefone || !senha) {
      event.preventDefault();
      if (!telefone) {
          telefoneInput.classList.add("input-error");
          msgTelefone.style.display = "block";
      }

      if (!senha) {
          senhaInput.classList.add("input-error");
          msgSenha.style.display = "block";
      }
      return;
      }

    if (telefone.length < 10 || telefone.length > 11) {
      event.preventDefault();
      telefoneInput.classList.add("input-error");
    }
  });
}