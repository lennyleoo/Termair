document.addEventListener('DOMContentLoaded', () => {
  const images = document.querySelectorAll( '.obra-img-ent img, .obra-img-hidro img, .obra-img img');

  const modal = document.createElement('div');
  modal.className = 'image-lightbox';

  const modalImg = document.createElement('img');
  modalImg.className = 'image-lightbox-img';

  modal.appendChild(modalImg);
  document.body.appendChild(modal);

  images.forEach((img) => {
    img.addEventListener('click', () => {
      modalImg.src = img.src;
      modal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    });
  });

  const closeModal = () => {
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  modal.addEventListener('click', closeModal);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});