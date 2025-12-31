<?php
// includes/footer.php (path: Lengkong/includes/footer.php)
// Hanya memanggil config.php dan functions.php jika belum dipanggil
if (!function_exists('get_settings')) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../functions.php';
}

$settings = get_settings();
?>
        </div> <!-- Penutup untuk content-wrapper dari header.php -->
    </div> <!-- Penutup untuk div utama -->

    <!-- Footer -->
    <footer class="footer-section bg-dark text-white pt-5 position-relative overflow-hidden">
        <!-- Animated Waves -->
        <div class="wave-container position-absolute top-0 start-0 w-100">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
                <defs>
                    <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
                </defs>
                <g class="parallax">
                    <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.1)" />
                    <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.2)" />
                    <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
                    <use xlink:href="#gentle-wave" x="48" y="7" fill="var(--primary)" />
                </g>
            </svg>
        </div>
        
        <div class="container pt-5 position-relative z-1">
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="footer-brand mb-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="logo-icon me-2">
                                <i class="fas fa-village fa-2x text-warning floating" style="animation-duration: 5s;"></i>
                            </div>
                            <div>
                                <h3 class="text-white fw-bold mb-0">Desa</h3>
                                <h3 class="text-warning fw-bold mb-0">Lengkong</h3>
                            </div>
                        </div>
                        <p class="text-white-50"><?php echo htmlspecialchars($settings['motto_desa']); ?></p>
                    </div>
                    <div class="social-icons d-flex gap-3">
                        <a href="<?php echo $settings['facebook'] ?? '#'; ?>" class="social-icon facebook animate-hover" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo $settings['instagram'] ?? '#'; ?>" class="social-icon instagram animate-hover" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo $settings['youtube'] ?? '#'; ?>" class="social-icon youtube animate-hover" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://wa.me/<?php echo $settings['whatsapp'] ?? '6281234567890'; ?>" class="social-icon whatsapp animate-hover" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <h5 class="text-warning mb-3">Menu Cepat</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50 hover-link">Beranda</a></li>
                        <li class="mb-2"><a href="modules/profil.php" class="text-white-50 hover-link">Profil Desa</a></li>
                        <li class="mb-2"><a href="modules/berita.php" class="text-white-50 hover-link">Berita</a></li>
                        <li class="mb-2"><a href="modules/potensi.php" class="text-white-50 hover-link">Potensi</a></li>
                        <li class="mb-2"><a href="modules/layanan.php" class="text-white-50 hover-link">Layanan</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <h5 class="text-warning mb-3">Kontak Kami</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt text-warning me-2"></i>
                            <span class="text-white-50"><?php echo htmlspecialchars($settings['alamat_kantor']); ?></span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone text-warning me-2"></i>
                            <span class="text-white-50"><?php echo htmlspecialchars($settings['telepon']); ?></span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope text-warning me-2"></i>
                            <span class="text-white-50"><?php echo htmlspecialchars($settings['email']); ?></span>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <span class="text-white-50">08:00 - 16:00 WIB</span>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <h5 class="text-warning mb-3">Lokasi</h5>
                    <div class="footer-map rounded overflow-hidden shadow">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.2383229124256!2d108.485214!3d-6.982822!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f169c8d3d3b3d%3A0x301e8f1fc28b8d0!2sKuningan%2C%20Jawa%20Barat!5e0!3m2!1sid!2sid!4v1629783456789!5m2!1sid!2sid" 
                                width="100%" 
                                height="150" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                        </iframe>
                    </div>
                    <p class="text-white-50 small mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Kantor Desa Lengkong, Kec. Garawangi
                    </p>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            
            <div class="row">
                <div class="col-md-6 mb-3" data-aos="fade-right">
                    <p class="mb-0 text-white-50">
                        &copy; <?php echo date('Y'); ?> 
                        <span class="text-warning"><?php echo htmlspecialchars($settings['nama_desa']); ?></span>. 
                        Hak Cipta Dilindungi.
                    </p>
                </div>
                <div class="col-md-6 text-md-end" data-aos="fade-left">
                    <p class="mb-0 text-white-50">
                        Dibuat dengan <i class="fas fa-heart text-danger heartbeat"></i> 
                        oleh Tim Digital Desa
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="btn btn-primary back-to-top animate__animated animate__fadeInUp">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animasi.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });
        
        // Back to Top Button
        const backToTopButton = document.querySelector('.back-to-top');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
                backToTopButton.style.opacity = '1';
                backToTopButton.style.transform = 'translateY(0)';
            } else {
                backToTopButton.classList.remove('show');
                backToTopButton.style.opacity = '0';
                backToTopButton.style.transform = 'translateY(20px)';
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Floating animation for particles
        document.addEventListener('DOMContentLoaded', () => {
            // Animate particles
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                particle.style.animation = `float ${Math.random() * 20 + 10}s ease-in-out infinite`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
            });
            
            // Heartbeat animation
            const heart = document.querySelector('.heartbeat');
            if (heart) {
                setInterval(() => {
                    heart.classList.add('animate__pulse');
                    setTimeout(() => {
                        heart.classList.remove('animate__pulse');
                    }, 1000);
                }, 2000);
            }
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    if (this.getAttribute('href') !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            window.scrollTo({
                                top: target.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });
            
            // Loading screen
            const loadingScreen = document.querySelector('.loading-screen');
            const progressBar = document.querySelector('.progress-bar');
            
            if (loadingScreen) {
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 20;
                    if (progressBar) {
                        progressBar.style.width = `${Math.min(progress, 100)}%`;
                    }
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                        loadingScreen.style.opacity = '0';
                        setTimeout(() => {
                            loadingScreen.style.display = 'none';
                        }, 500);
                    }
                }, 100);
            }
            
            // Counter animation for statistics
            const counters = document.querySelectorAll('.counter');
            const speed = 200;
            
            const startCounter = (counter) => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-count');
                    const count = +counter.innerText.replace(/,/g, '');
                    const increment = target / speed;
                    
                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment).toLocaleString('id-ID');
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };
                updateCount();
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        startCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            counters.forEach(counter => {
                observer.observe(counter);
            });
            
            // Typewriter effect
            const typewriterElements = document.querySelectorAll('.typewriter-text');
            typewriterElements.forEach(element => {
                const text = element.textContent;
                element.textContent = '';
                let i = 0;
                
                const typeWriter = () => {
                    if (i < text.length) {
                        element.textContent += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, 100);
                    }
                };
                
                const typewriterObserver = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        typeWriter();
                        typewriterObserver.unobserve(element);
                    }
                });
                
                typewriterObserver.observe(element);
            });
            
            // Theme toggle
            const themeToggle = document.querySelector('.theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const html = document.querySelector('html');
                    const icon = themeToggle.querySelector('i');
                    
                    if (html.getAttribute('data-bs-theme') === 'dark') {
                        html.setAttribute('data-bs-theme', 'light');
                        icon.classList.remove('fa-sun');
                        icon.classList.add('fa-moon');
                    } else {
                        html.setAttribute('data-bs-theme', 'dark');
                        icon.classList.remove('fa-moon');
                        icon.classList.add('fa-sun');
                    }
                });
            }
            
            // Hover effect for cards
            const cards = document.querySelectorAll('.animate-on-hover');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-10px)';
                    card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
                    card.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '';
                });
            });
            
            // Parallax effect for waves
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const waves = document.querySelector('.waves');
                if (waves) {
                    waves.style.transform = `translate3d(0, ${scrolled * 0.2}px, 0)`;
                }
            });
        });
    </script>
</body>
</html>