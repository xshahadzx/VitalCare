
// slider Function 
const initializeSlider = (sliderSelector) => {
    const slider = document.querySelector(sliderSelector);
    const slides = slider.querySelectorAll('.slide');

    if (slides.length > 0) {
        let currentIndex = 0;

        // Show the first slide initially
        slides[currentIndex].classList.add('active');

        // Function to switch to the next slide
        const showNextSlide = () => {
            // Remove the active class from the current slide
            slides[currentIndex].classList.remove('active');

            // Update the index to the next slide
            currentIndex = (currentIndex + 1) % slides.length;

            // Add the active class to the new slide
            slides[currentIndex].classList.add('active');
        };

        // Set an interval to switch slides every 5 seconds
        setInterval(showNextSlide, 5000);
    }
};

// Initialize sliders for different sections
initializeSlider('.comment-slider');
initializeSlider('.profile-slider');
initializeSlider('.insurance-slider');


