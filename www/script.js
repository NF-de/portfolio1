window.addEventListener('scroll', function() {
    const title = document.querySelector('.title');
    const titlePosition = title.getBoundingClientRect().top;
    const screenPosition = window.innerHeight / 1.3; 
    
    if (titlePosition < screenPosition) {
        title.classList.add('scrolled');
    } else {
        title.classList.remove('scrolled');
    }
});

