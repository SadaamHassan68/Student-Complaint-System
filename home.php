<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--dark-color) !important;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-register {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 6px 18px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-hero {
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            margin: 0 10px 10px 0;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-hero {
            background: white;
            color: var(--primary-color);
        }

        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: var(--primary-color);
        }

        .btn-outline-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-hero:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: var(--light-color);
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }

        /* About Section */
        .about {
            padding: 100px 0;
        }

        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .about-image {
            flex: 1;
        }

        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Contact Section */
        .contact {
            padding: 100px 0;
            background: var(--light-color);
        }

        .contact-form {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 50px 0 20px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .navbar-nav {
                text-align: center;
                margin-top: 20px;
            }
            
            .contact-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-graduation-cap me-2"></i>StudentCare
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                
                <div class="navbar-buttons">
                    <a href="index.php" class="btn btn-login">Login</a>
                    <a href="register.php" class="btn btn-register">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Student Complaint Management Made Simple</h1>
                        <p>Streamline your institution's complaint handling process with our modern, efficient, and transparent digital platform.</p>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn btn-primary-hero btn-hero">Get Started</a>
                            <a href="#about" class="btn btn-outline-hero btn-hero">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-comments fa-10x text-white opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="animate-on-scroll" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Why Choose Our Platform?</h2>
                    <p class="animate-on-scroll" style="font-size: 1.2rem; color: #6c757d;">Modern solution for educational institutions</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Easy to Use</h4>
                        <p>Intuitive interface designed for students, staff, and administrators. Submit and track complaints with just a few clicks.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure & Private</h4>
                        <p>Your data is protected with advanced security measures. Role-based access ensures confidentiality and privacy.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Real-time Tracking</h4>
                        <p>Monitor complaint status in real-time. Get instant updates and notifications throughout the resolution process.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Multi-Role Support</h4>
                        <p>Designed for students, staff, and administrators with role-specific dashboards and functionalities.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Responsive</h4>
                        <p>Access the platform from any device. Fully responsive design ensures optimal experience on mobile and desktop.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card animate-on-scroll">
                        <div class="feature-icon">
                            <i class="fas fa-file-download"></i>
                        </div>
                        <h4>Advanced Reports</h4>
                        <p>Generate comprehensive reports and analytics. Export data for further analysis and institutional insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-text animate-on-scroll">
                        <h2>About Our Platform</h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 20px;">
                            Our Student Complaint Management System revolutionizes how educational institutions handle student concerns. 
                            Built with modern technology and user-centric design, we provide a comprehensive solution that benefits everyone.
                        </p>
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px;">
                            From simple complaint submission to advanced analytics and reporting, our platform ensures transparency, 
                            efficiency, and accountability in every step of the complaint resolution process.
                        </p>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 style="color: var(--primary-color); font-weight: 700;">500+</h3>
                                <p>Institutions</p>
                            </div>
                            <div class="col-4">
                                <h3 style="color: var(--primary-color); font-weight: 700;">10K+</h3>
                                <p>Students</p>
                            </div>
                            <div class="col-4">
                                <h3 style="color: var(--primary-color); font-weight: 700;">95%</h3>
                                <p>Satisfaction</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="about-image animate-on-scroll">
                        <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 50px; border-radius: 20px; text-align: center;">
                            <i class="fas fa-university fa-5x text-white mb-3"></i>
                            <h4 class="text-white mb-3">Trusted by Educational Institutions</h4>
                            <p class="text-white opacity-75">Empowering better communication between students and administration</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="animate-on-scroll" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Get In Touch</h2>
                    <p class="animate-on-scroll" style="font-size: 1.2rem; color: #6c757d;">Have questions? We'd love to hear from you.</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form animate-on-scroll">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" placeholder="Your Name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="your@email.com">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" placeholder="How can we help?">
                            </div>
                            <div class="col-12 mb-4">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" rows="5" placeholder="Your message..."></textarea>
                            </div>
                            <div class="col-12 text-center">
                                <button class="btn btn-login btn-lg px-5">Send Message</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-graduation-cap me-2"></i>StudentCare
                    </h5>
                    <p style="color: rgba(255,255,255,0.7);">
                        Revolutionizing student complaint management with modern technology and user-centric design.
                    </p>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 style="color: white; margin-bottom: 20px;">Quick Links</h6>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#home" style="color: rgba(255,255,255,0.7); text-decoration: none;">Home</a></li>
                        <li><a href="#about" style="color: rgba(255,255,255,0.7); text-decoration: none;">About</a></li>
                        <li><a href="#features" style="color: rgba(255,255,255,0.7); text-decoration: none;">Features</a></li>
                        <li><a href="index.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Login</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 style="color: white; margin-bottom: 20px;">Contact Info</h6>
                    <p style="color: rgba(255,255,255,0.7);">
                        <i class="fas fa-envelope me-2"></i>info@studentcare.com<br>
                        <i class="fas fa-phone me-2"></i>+1 (555) 123-4567<br>
                        <i class="fas fa-map-marker-alt me-2"></i>123 Education St, Learning City
                    </p>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1);">
            
            <div class="text-center" style="color: rgba(255,255,255,0.7);">
                <p>&copy; 2024 StudentCare. All rights reserved. Built with ❤️ for educational institutions.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Add active class to navbar links based on scroll position
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>