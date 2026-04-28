document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.getElementById('obrasCarousel');
  const prevBtn = document.querySelector('.termair-obras-arrow-left');
  const nextBtn = document.querySelector('.termair-obras-arrow-right');

  if (!carousel || !prevBtn || !nextBtn) return;

  let isMoving = false;

  const getMoveAmount = () => {
    const card = carousel.querySelector('.termair-obra-card');
    const gap = parseInt(window.getComputedStyle(carousel).gap, 10) || 14;

    if (!card) return 0;

    return card.offsetWidth + gap;
  };

  const moveNext = () => {
    if (isMoving) return;

    const firstCard = carousel.firstElementChild;
    const moveAmount = getMoveAmount();

    if (!firstCard || !moveAmount) return;

    isMoving = true;

    carousel.style.transition = 'transform 0.35s ease';
    carousel.style.transform = `translateX(-${moveAmount}px)`;

    carousel.addEventListener('transitionend', function handleEnd() {
      carousel.style.transition = 'none';
      carousel.style.transform = 'translateX(0)';

      carousel.appendChild(firstCard);

      carousel.removeEventListener('transitionend', handleEnd);
      isMoving = false;
    });
  };

  const movePrev = () => {
    if (isMoving) return;

    const lastCard = carousel.lastElementChild;
    const moveAmount = getMoveAmount();

    if (!lastCard || !moveAmount) return;

    isMoving = true;

    carousel.style.transition = 'none';
    carousel.insertBefore(lastCard, carousel.firstElementChild);
    carousel.style.transform = `translateX(-${moveAmount}px)`;

    requestAnimationFrame(() => {
      carousel.style.transition = 'transform 0.35s ease';
      carousel.style.transform = 'translateX(0)';
    });

    carousel.addEventListener('transitionend', function handleEnd() {
      carousel.style.transition = 'none';
      carousel.style.transform = 'translateX(0)';

      carousel.removeEventListener('transitionend', handleEnd);
      isMoving = false;
    });
  };

  nextBtn.addEventListener('click', moveNext);
  prevBtn.addEventListener('click', movePrev);
});