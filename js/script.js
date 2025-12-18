// Register GSAP ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// Preloader & Animation Init
// Preloader & Animation Init
window.addEventListener('load', () => {
  // Init animations immediately so 'gsap.from' hides elements before user sees them
  initAnimations();

  const preloader = document.getElementById('preloader');
  if (preloader) {
    // Short delay to ensure GSAP has set initial states
    setTimeout(() => {
      preloader.classList.add('fade-out');
      setTimeout(() => {
        preloader.remove();
      }, 500);
    }, 100);
  }
});

// Scroll to Top
document.addEventListener('DOMContentLoaded', () => {
  const scrollTopBtn = document.getElementById('scrollTopBtn');
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Contact Form Handling
  const contactForm = document.getElementById('contactForm');
  const formMessage = document.getElementById('form-message');

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(contactForm);

      fetch('contact_mail.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            formMessage.innerHTML = data.message;
            formMessage.className = 'mb-3 success';
            contactForm.reset();
          } else {
            formMessage.innerHTML = data.message;
            formMessage.className = 'mb-3 error';
          }
          formMessage.style.display = 'block';
        })
        .catch(error => {
          console.error('Error:', error);
          formMessage.innerHTML = 'An unexpected error occurred. Please try again later.';
          formMessage.className = 'mb-3 error';
          formMessage.style.display = 'block';
        });
    });
  }
});

function initAnimations() {
  // Navbar Animation
  gsap.from('#mainNavbar', {
    y: -100,
    opacity: 0,
    duration: 1,
    ease: 'power3.out',
    delay: 0.5
  });

  // Hero Section Animations
  // (Leaving Hero carousel as is, per request)

  // About Section
  gsap.from('.about-img-wrapper', {
    scrollTrigger: {
      trigger: '#about',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    x: -50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
  });

  gsap.from('.about-content', {
    scrollTrigger: {
      trigger: '#about',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    x: 50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out',
    delay: 0.2
  });

  // Services Section
  gsap.from('#services .section-title', {
    scrollTrigger: {
      trigger: '#services',
      start: 'top 85%',
      toggleActions: 'play none none none'
    },
    y: 30,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  });



  // Portfolio Section
  gsap.from('#portfolio .text-center', {
    scrollTrigger: {
      trigger: '#portfolio',
      start: 'top 85%',
      toggleActions: 'play none none none'
    },
    y: 30,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  });

  ScrollTrigger.batch('.portfolio-item', {
    start: 'top 85%',
    onEnter: batch => gsap.from(batch, {
      opacity: 0,
      y: 50,
      stagger: 0.15,
      duration: 0.8,
      ease: 'power3.out',
      overwrite: true
    })
  });

  // Testimonials Section
  gsap.from('#testimonials .text-center', {
    scrollTrigger: {
      trigger: '#testimonials',
      start: 'top 85%',
      toggleActions: 'play none none none'
    },
    y: 30,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  });

  gsap.from('#testimonialCarousel', {
    scrollTrigger: {
      trigger: '#testimonials',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    scale: 0.95,
    opacity: 0,
    duration: 1,
    ease: 'back.out(1.7)'
  });

  // Contact Section
  gsap.from('.contact-info-wrapper', {
    scrollTrigger: {
      trigger: '#contact',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    x: -30,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
  });

  gsap.from('.contact-form-wrapper', {
    scrollTrigger: {
      trigger: '#contact',
      start: 'top 80%',
      toggleActions: 'play none none none'
    },
    x: 30,
    opacity: 0,
    duration: 1,
    ease: 'power3.out',
    delay: 0.2
  });

  // Footer
  gsap.from('.footer', {
    scrollTrigger: {
      trigger: '.footer',
      start: 'top 95%',
      toggleActions: 'play none none none'
    },
    y: 30,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
  });
}
