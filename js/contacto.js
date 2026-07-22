document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('termairContactForm');
  const submit = document.getElementById('termairContactSubmit');
  const feedback = document.getElementById('termairFormFeedback');

  if (!form || !submit || !feedback) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    const originalText = submit.textContent;
    submit.disabled = true;
    submit.textContent = 'Enviando...';
    feedback.className = 'termair-form-feedback';
    feedback.textContent = '';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      const data = await response.json();
      feedback.textContent = data.message || 'No fue posible procesar la consulta.';
      feedback.classList.add(data.success ? 'is-success' : 'is-error');

      if (data.success) {
        form.reset();
        if (data.csrf_token) form.elements.csrf_token.value = data.csrf_token;
      }
    } catch (error) {
      feedback.textContent = 'No se pudo enviar la consulta. Inténtelo nuevamente en unos minutos.';
      feedback.classList.add('is-error');
    } finally {
      submit.disabled = false;
      submit.textContent = originalText;
    }
  });
});
