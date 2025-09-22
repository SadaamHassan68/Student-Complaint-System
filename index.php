<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-decoration: none;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: #ffd700 !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ffd700;
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after {
            width: 100%;
        }

        .btn-auth {
            margin: 0 5px;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-login {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-login:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .btn-register {
            background: #ffd700;
            border: 2px solid #ffd700;
            color: #333;
        }

        .btn-register:hover {
            background: transparent;
            color: #ffd700;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: rgba(0,0,0,0.1);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-hero {
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary-hero {
            background: #ffd700;
            color: #333;
            border: 2px solid #ffd700;
        }

        .btn-primary-hero:hover {
            background: transparent;
            color: #ffd700;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255,215,0,0.3);
        }

        .btn-secondary-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary-hero:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255,255,255,0.3);
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.2rem;
            color: #666;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }

        .feature-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* About Section */
        .about {
            padding: 100px 0;
            background: white;
        }

        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .about-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .about-text p {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .stats {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        /* Contact Section */
        .contact {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .contact-form {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            margin-bottom: 20px;
            color: #ffd700;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ffd700;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 20px;
            text-align: center;
            color: #ccc;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .btn-hero {
                display: block;
                margin: 10px auto;
                width: 200px;
            }

            .about-content {
                flex-direction: column;
                text-align: center;
            }

            .stats {
                justify-content: center;
            }

            .footer-content {
                flex-direction: column;
                gap: 30px;
            }

            .navbar-nav {
                text-align: center;
            }

            .contact-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-graduation-cap me-2"></i>SCMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-4">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-auth btn-login">Login</a>
                    <a href="register.php" class="btn btn-auth btn-register">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <h1>Student Complaint Management System</h1>
                        <p>Streamline complaint handling, improve communication, and ensure every student voice is heard with our comprehensive management platform.</p>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn-hero btn-primary-hero">Get Started</a>
                            <a href="#features" class="btn-hero btn-secondary-hero">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Why Choose Our System?</h2>
                <p>Powerful features designed to make complaint management efficient and transparent</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Multi-User Access</h4>
                        <p>Dedicated portals for students, staff, and administrators with role-based permissions and customized dashboards.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Real-time Communication</h4>
                        <p>Instant updates, notifications, and seamless communication between all parties involved in the complaint process.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Analytics & Reporting</h4>
                        <p>Comprehensive analytics, detailed reports, and insights to help improve institutional processes and satisfaction.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure & Private</h4>
                        <p>Advanced security measures to protect sensitive information and maintain confidentiality of all complaints.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Quick Resolution</h4>
                        <p>Streamlined workflow processes that ensure complaints are addressed promptly and efficiently.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 animate-on-scroll">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Responsive</h4>
                        <p>Access the system from any device - desktop, tablet, or mobile - with a fully responsive design.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate-on-scroll">
                    <div class="about-text">
                        <h2>Transforming Educational Institutions</h2>
                        <p>Our Student Complaint Management System is designed to bridge the communication gap between students and educational institutions. We understand that every student's voice matters and every concern deserves proper attention.</p>
                        <p>With years of experience in educational technology, we've created a platform that not only manages complaints efficiently but also helps institutions build stronger relationships with their students through transparency and accountability.</p>
                        <div class="stats">
                            <div class="stat-item">
                                <span class="stat-number">500+</span>
                                <span class="stat-label">Institutions</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">50K+</span>
                                <span class="stat-label">Students</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">98%</span>
                                <span class="stat-label">Satisfaction</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 animate-on-scroll">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/667eea/ffffff?text=Student+Management" alt="About Us" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-title animate-on-scroll">
                <h2>Get In Touch</h2>
                <p>Have questions? We're here to help you get started</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="contact-form animate-on-scroll">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" rows="5" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>SCMS</h4>
                    <p>Making student voices heard through efficient complaint management and transparent communication.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="#home">Home</a>
                    <a href="#features">Features</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="#">Help Center</a>
                    <a href="#">Documentation</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <a href="#"><i class="fab fa-facebook me-2"></i>Facebook</a>
                    <a href="#"><i class="fab fa-twitter me-2"></i>Twitter</a>
                    <a href="#"><i class="fab fa-linkedin me-2"></i>LinkedIn</a>
                    <a href="#"><i class="fab fa-instagram me-2"></i>Instagram</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Student Complaint Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
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

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Animate elements on scroll
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

        // Contact form submission
        document.querySelector('.contact-form form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            
            // Simple validation
            if (name && email && subject && message) {
                alert('Thank you for your message! We will get back to you soon.');
                this.reset();
            } else {
                alert('Please fill in all fields.');
            }
        });

        // Add hover effects to feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>