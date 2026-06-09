const fTelefone = document.getElementById('telefone');
const fSenha    = document.getElementById('senha');
const btnToggle = document.getElementById('toggleSenha');
const form      = document.getElementById('loginForm');
const errTel    = document.getElementById('errTelefone');
const errSen    = document.getElementById('errSenha');

function mascTel(v) {
  const n = v.replace(/\D/g, '').slice(0, 11);
  if (n.length <= 2)  return n;
  if (n.length <= 7)  return `(${n.slice(0,2)}) ${n.slice(2)}`;
  return `(${n.slice(0,2)}) ${n.slice(2,7)}-${n.slice(7)}`;
}

if (fTelefone) {
  fTelefone.value = mascTel(fTelefone.value);
  fTelefone.addEventListener('input', e => {
    e.target.value = mascTel(e.target.value);
    fTelefone.classList.remove('input-erro');
    if (errTel) errTel.style.display = 'none';
  });
}

if (fSenha) {
  fSenha.addEventListener('input', () => {
    fSenha.classList.remove('input-erro');
    if (errSen) errSen.style.display = 'none';
  });
}

if (btnToggle && fSenha) {
  btnToggle.addEventListener('click', () => {
    const show = fSenha.type === 'text';
    fSenha.type = show ? 'password' : 'text';
    btnToggle.textContent = show ? 'Mostrar' : 'Ocultar';
  });
}

if (form) {
  form.addEventListener('submit', e => {
    const tel = fTelefone.value.replace(/\D/g, '');
    const sen = fSenha.value.trim();
    let ok = true;

    if (!tel || tel.length < 10) {
      fTelefone.classList.add('input-erro');
      if (errTel) errTel.style.display = 'block';
      ok = false;
    }
    if (!sen) {
      fSenha.classList.add('input-erro');
      if (errSen) errSen.style.display = 'block';
      ok = false;
    }
    if (!ok) e.preventDefault();
  });
}
