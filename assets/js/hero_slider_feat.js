document.addEventListener('DOMContentLoaded', () => {
    const slider = document.querySelector('[data-hero-ad-slider]');

    if (!slider) {
        return;
    }

    const slides = Array.from(
        slider.querySelectorAll('.hero-ad-slide')
    );

    if (slides.length <= 1) {
        return;
    }

    let currentIndex = 0;

    const showNextSlide = () => {
        const currentSlide = slides[currentIndex];

        currentIndex = (currentIndex + 1) % slides.length;

        const nextSlide = slides[currentIndex];

        currentSlide.classList.remove('is-active');
        currentSlide.classList.add('is-leaving');

        nextSlide.classList.remove('is-leaving');
        nextSlide.classList.add('is-active');

        setTimeout(() => {
            currentSlide.classList.remove('is-leaving');
        }, 700);
    };

    setInterval(showNextSlide, 5000);
});