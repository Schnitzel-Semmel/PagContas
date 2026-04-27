const telefoneInput = document.getElementById("telefone");
const senhaInput = document.getElementById("senha");
const toggleSenha = document.getElementById("toggleSenha");
const loginForm = document.getElementById("loginForm");
const msgTelefone = document.getElementById("error-telefone");
const msgSenha = document.getElementById("error-senha");
const msgLogin = document.getElementById("error-login");
const params = new URLSearchParams(window.location.search);
const erro = params.get("erro");

function mostrarMensagem(elemento) {
  if (elemento) {
    elemento.style.display = "block";
  }
}

function esconderMensagem(elemento) {
  if (elemento) {
    elemento.style.display = "none";
  }
}

function limparErroTelefone() {
  if (telefoneInput) {
    telefoneInput.classList.remove("input-error");
  }

  esconderMensagem(msgTelefone);
  esconderMensagem(msgLogin);
}

function limparErroSenha() {
  if (senhaInput) {
    senhaInput.classList.remove("input-error");
  }
  if (telefoneInput) {
    telefoneInput.classList.remove("input-error");
  }

  esconderMensagem(msgTelefone);
  esconderMensagem(msgLogin);
  esconderMensagem(msgSenha);
  esconderMensagem(msgLogin);
}

function aplicarMascaraTelefone(valor) {
  const numeros = valor.replace(/\D/g, "").slice(0, 11);

  if (numeros.length <= 2) return numeros;
  if (numeros.length <= 7) return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
  return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7, 11)}`;
}

if (telefoneInput) {
  telefoneInput.addEventListener("input", (event) => {
    event.target.value = aplicarMascaraTelefone(event.target.value);
    limparErroTelefone();
  });

  telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
}

if (senhaInput) {
  senhaInput.addEventListener("input", () => {
    limparErroSenha();
  });
}

if (toggleSenha && senhaInput) {
  toggleSenha.addEventListener("click", () => {
    const mostrando = senhaInput.type === "text";
    senhaInput.type = mostrando ? "password" : "text";
    toggleSenha.textContent = mostrando ? "Mostrar" : "Ocultar";
  });
}

if (erro === "usuario") {
  telefoneInput?.classList.add("input-error");
  senhaInput?.classList.add("input-error");
  mostrarMensagem(msgLogin);
}

if (loginForm) {
  loginForm.addEventListener("submit", (event) => {
    const telefone = telefoneInput.value.replace(/\D/g, "");
    const senha = senhaInput.value.trim();

    limparErroTelefone();
    limparErroSenha();

    if (!telefone || !senha) {
      event.preventDefault();

      if (!telefone) {
        telefoneInput.classList.add("input-error");
        mostrarMensagem(msgTelefone);
      }

      if (!senha) {
        senhaInput.classList.add("input-error");
        mostrarMensagem(msgSenha);
      }

      return;
    }

    if (telefone.length < 10 || telefone.length > 11) {
      event.preventDefault();
      telefoneInput.classList.add("input-error");
      mostrarMensagem(msgTelefone);
    }
  });
}
